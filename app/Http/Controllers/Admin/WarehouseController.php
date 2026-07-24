<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class WarehouseController extends Controller
{
    /**
     * Hiển thị danh sách kho hàng.
     */
    public function index(Request $request): View
    {
        $query = DB::table('warehouses as w')
            ->select([
                'w.id',
                'w.name',
                'w.address',
                'w.status',
                'w.created_at',
                'w.updated_at',
            ])
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('inventories')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn(
                            'inventories.warehouse_id',
                            'w.id'
                        );
                },
                'inventory_rows_count'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('inventories')
                        ->selectRaw(
                            'COALESCE(SUM(quantity), 0)'
                        )
                        ->whereColumn(
                            'inventories.warehouse_id',
                            'w.id'
                        );
                },
                'total_quantity'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('inventories')
                        ->selectRaw(
                            'COALESCE(SUM(reserved_quantity), 0)'
                        )
                        ->whereColumn(
                            'inventories.warehouse_id',
                            'w.id'
                        );
                },
                'reserved_quantity'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('inventories')
                        ->selectRaw(
                            'COALESCE(
                                SUM(
                                    quantity - reserved_quantity
                                ),
                                0
                            )'
                        )
                        ->whereColumn(
                            'inventories.warehouse_id',
                            'w.id'
                        );
                },
                'available_quantity'
            );

        $keyword = trim(
            (string) $request->input('keyword')
        );

        if ($keyword !== '') {
            $query->where(
                function (Builder $builder) use ($keyword): void {
                    $builder
                        ->where(
                            'w.name',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'w.address',
                            'like',
                            '%' . $keyword . '%'
                        );
                }
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'w.status',
                (int) $request->input('status')
            );
        }

        match ($request->input('sort')) {
            'oldest' =>
                $query->orderBy('w.id'),

            'name_asc' =>
                $query->orderBy('w.name'),

            'name_desc' =>
                $query->orderByDesc('w.name'),

            'quantity_desc' =>
                $query
                    ->orderByDesc('total_quantity')
                    ->orderBy('w.name'),

            'available_desc' =>
                $query
                    ->orderByDesc('available_quantity')
                    ->orderBy('w.name'),

            default =>
                $query->orderByDesc('w.id'),
        };

        $warehouses = $query
            ->paginate(20)
            ->withQueryString();

        $statistics = [
            'total' => DB::table('warehouses')
                ->count(),

            'active' => DB::table('warehouses')
                ->where('status', 1)
                ->count(),

            'inactive' => DB::table('warehouses')
                ->where('status', 0)
                ->count(),

            'total_quantity' => (int) DB::table(
                'inventories'
            )->sum('quantity'),

            'reserved_quantity' => (int) DB::table(
                'inventories'
            )->sum('reserved_quantity'),

            'available_quantity' => (int) DB::table(
                'inventories'
            )->selectRaw(
                'COALESCE(
                    SUM(quantity - reserved_quantity),
                    0
                ) as total'
            )->value('total'),
        ];

        return view('admin.warehouses.index', [
            'warehouses' => $warehouses,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Hiển thị form thêm kho.
     */
    public function create(): View
    {
        return view('admin.warehouses.create');
    }

    /**
     * Lưu kho mới.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        DB::beginTransaction();

        try {
            $now = now();

            $warehouseId = DB::table('warehouses')
                ->insertGetId([
                    'name' => trim($validated['name']),

                    'address' => $this->nullableTrim(
                        $validated['address'] ?? null
                    ),

                    'status' => (int) (
                        $validated['status'] ?? 1
                    ),

                    'created_at' => $now,

                    'updated_at' => $now,
                ]);

            DB::commit();

            return redirect()
                ->route(
                    'admin.warehouses.show',
                    $warehouseId
                )
                ->with(
                    'success',
                    'Thêm kho hàng thành công.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi: '
                            . $exception->getMessage()
                        : 'Không thể thêm kho hàng.'
                );
        }
    }

    /**
     * Hiển thị chi tiết kho.
     */
    public function show(int $warehouse): View
    {
        $warehouseDetail = DB::table('warehouses')
            ->where('id', $warehouse)
            ->first([
                'id',
                'name',
                'address',
                'status',
                'created_at',
                'updated_at',
            ]);

        abort_if(! $warehouseDetail, 404);

        $inventories = DB::table('inventories as i')
            ->join(
                'product_skus as ps',
                'i.sku_id',
                '=',
                'ps.id'
            )
            ->join(
                'products as p',
                'ps.product_id',
                '=',
                'p.id'
            )
            ->leftJoin(
                'product_variants as pv',
                'ps.variant_id',
                '=',
                'pv.id'
            )
            ->where(
                'i.warehouse_id',
                $warehouseDetail->id
            )
            ->whereNull('p.deleted_at')
            ->select([
                'i.id',
                'i.sku_id',
                'i.quantity',
                'i.reserved_quantity',
                'i.sold_quantity',
                'i.minimum_stock',
                'i.created_at',
                'i.updated_at',

                'ps.sku_code',
                'ps.status as sku_status',

                'p.id as product_id',
                'p.name as product_name',
                'p.slug as product_slug',

                'pv.name as variant_name',
            ])
            ->selectRaw(
                '(i.quantity - i.reserved_quantity)
                as available_quantity'
            )
            ->orderBy('p.name')
            ->orderBy('ps.sku_code')
            ->paginate(20)
            ->withQueryString();

        $statistics = [
            'inventory_rows' => DB::table('inventories')
                ->where(
                    'warehouse_id',
                    $warehouseDetail->id
                )
                ->count(),

            'total_quantity' => (int) DB::table(
                'inventories'
            )
                ->where(
                    'warehouse_id',
                    $warehouseDetail->id
                )
                ->sum('quantity'),

            'reserved_quantity' => (int) DB::table(
                'inventories'
            )
                ->where(
                    'warehouse_id',
                    $warehouseDetail->id
                )
                ->sum('reserved_quantity'),

            'sold_quantity' => (int) DB::table(
                'inventories'
            )
                ->where(
                    'warehouse_id',
                    $warehouseDetail->id
                )
                ->sum('sold_quantity'),

            'available_quantity' => (int) DB::table(
                'inventories'
            )
                ->where(
                    'warehouse_id',
                    $warehouseDetail->id
                )
                ->selectRaw(
                    'COALESCE(
                        SUM(quantity - reserved_quantity),
                        0
                    ) as total'
                )
                ->value('total'),

            'low_stock_rows' => DB::table('inventories')
                ->where(
                    'warehouse_id',
                    $warehouseDetail->id
                )
                ->whereRaw(
                    '(quantity - reserved_quantity)
                    <= minimum_stock'
                )
                ->count(),

            'transactions' => DB::table(
                'inventory_transactions'
            )
                ->where(
                    'warehouse_id',
                    $warehouseDetail->id
                )
                ->count(),

            'orders' => DB::table('orders')
                ->where(
                    'warehouse_id',
                    $warehouseDetail->id
                )
                ->count(),

            'shipments' => DB::table('shipments')
                ->where(
                    'warehouse_id',
                    $warehouseDetail->id
                )
                ->count(),
        ];

        return view('admin.warehouses.show', [
            'warehouse' => $warehouseDetail,
            'inventories' => $inventories,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Hiển thị form sửa kho.
     */
    public function edit(int $warehouse): View
    {
        $warehouseDetail = DB::table('warehouses')
            ->where('id', $warehouse)
            ->first([
                'id',
                'name',
                'address',
                'status',
                'created_at',
                'updated_at',
            ]);

        abort_if(! $warehouseDetail, 404);

        return view('admin.warehouses.edit', [
            'warehouse' => $warehouseDetail,
        ]);
    }

    /**
     * Cập nhật kho.
     */
    public function update(
        Request $request,
        int $warehouse
    ): RedirectResponse {
        $warehouseDetail = DB::table('warehouses')
            ->where('id', $warehouse)
            ->first();

        abort_if(! $warehouseDetail, 404);

        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        DB::beginTransaction();

        try {
            DB::table('warehouses')
                ->where('id', $warehouseDetail->id)
                ->update([
                    'name' => trim($validated['name']),

                    'address' => $this->nullableTrim(
                        $validated['address'] ?? null
                    ),

                    'status' => (int) (
                        $validated['status'] ?? 1
                    ),

                    'updated_at' => now(),
                ]);

            DB::commit();

            return redirect()
                ->route(
                    'admin.warehouses.show',
                    $warehouseDetail->id
                )
                ->with(
                    'success',
                    'Cập nhật kho hàng thành công.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi: '
                            . $exception->getMessage()
                        : 'Không thể cập nhật kho hàng.'
                );
        }
    }

    /**
     * Xóa kho.
     */
    public function destroy(
        int $warehouse
    ): RedirectResponse {
        $warehouseDetail = DB::table('warehouses')
            ->where('id', $warehouse)
            ->first([
                'id',
                'name',
            ]);

        abort_if(! $warehouseDetail, 404);

        $relations = [
            'inventories' => [
                'table' => 'inventories',
                'message' =>
                    'Kho vẫn còn dữ liệu tồn kho.',
            ],

            'inventory_transactions' => [
                'table' => 'inventory_transactions',
                'message' =>
                    'Kho đã phát sinh giao dịch kho.',
            ],

            'orders' => [
                'table' => 'orders',
                'message' =>
                    'Kho đã được sử dụng trong đơn hàng.',
            ],

            'shipments' => [
                'table' => 'shipments',
                'message' =>
                    'Kho đã được sử dụng trong vận chuyển.',
            ],
        ];

        foreach ($relations as $relation) {
            $exists = DB::table($relation['table'])
                ->where(
                    'warehouse_id',
                    $warehouseDetail->id
                )
                ->exists();

            if ($exists) {
                return back()->with(
                    'error',
                    'Không thể xóa kho này. '
                    . $relation['message']
                    . ' Bạn có thể chuyển kho sang trạng thái ngừng hoạt động.'
                );
            }
        }

        DB::beginTransaction();

        try {
            DB::table('warehouses')
                ->where('id', $warehouseDetail->id)
                ->delete();

            DB::commit();

            return redirect()
                ->route('admin.warehouses.index')
                ->with(
                    'success',
                    'Đã xóa kho "'
                    . $warehouseDetail->name
                    . '".'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi xóa kho: '
                        . $exception->getMessage()
                    : 'Không thể xóa kho hàng.'
            );
        }
    }

    /**
     * Quy tắc validation.
     *
     * @return array<string, mixed>
     */
    private function validationRules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                'integer',
                Rule::in([0, 1]),
            ],
        ];
    }

    /**
     * Thông báo validation.
     *
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'name.required' =>
                'Vui lòng nhập tên kho.',

            'name.max' =>
                'Tên kho không được vượt quá 255 ký tự.',

            'address.max' =>
                'Địa chỉ không được vượt quá 255 ký tự.',

            'status.in' =>
                'Trạng thái kho không hợp lệ.',
        ];
    }

    /**
     * Chuẩn hóa chuỗi nullable.
     */
    private function nullableTrim(
        mixed $value
    ): ?string {
        $value = trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }
}
