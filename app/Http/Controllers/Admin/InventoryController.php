<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class InventoryController extends Controller
{
    /**
     * Hiển thị danh sách tồn kho.
     */
    public function index(Request $request): View
    {
        $query = DB::table('inventories as i')
            ->join(
                'warehouses as w',
                'i.warehouse_id',
                '=',
                'w.id'
            )
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
            ->whereNull('p.deleted_at')
            ->select([
                'i.id',
                'i.warehouse_id',
                'i.sku_id',
                'i.quantity',
                'i.reserved_quantity',
                'i.sold_quantity',
                'i.minimum_stock',
                'i.created_at',
                'i.updated_at',

                'w.name as warehouse_name',
                'w.status as warehouse_status',

                'ps.sku_code',
                'ps.barcode',
                'ps.status as sku_status',
                'ps.price',

                'p.id as product_id',
                'p.name as product_name',
                'p.slug as product_slug',
                'p.status as product_status',

                'pv.name as variant_name',
            ])
            ->selectRaw(
                '(i.quantity - i.reserved_quantity)
                as available_quantity'
            );

        $keyword = trim(
            (string) $request->input('keyword')
        );

        if ($keyword !== '') {
            $query->where(
                function (Builder $builder) use ($keyword): void {
                    $builder
                        ->where(
                            'p.name',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'ps.sku_code',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'ps.barcode',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'pv.name',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'w.name',
                            'like',
                            '%' . $keyword . '%'
                        );
                }
            );
        }

        if ($request->filled('warehouse_id')) {
            $query->where(
                'i.warehouse_id',
                (int) $request->input('warehouse_id')
            );
        }

        if ($request->filled('sku_status')) {
            $query->where(
                'ps.status',
                (int) $request->input('sku_status')
            );
        }

        match ($request->input('stock_status')) {
            'out_of_stock' => $query->whereRaw(
                '(i.quantity - i.reserved_quantity) <= 0'
            ),

            'low_stock' => $query
                ->whereRaw(
                    '(i.quantity - i.reserved_quantity)
                    <= i.minimum_stock'
                )
                ->whereRaw(
                    '(i.quantity - i.reserved_quantity) > 0'
                ),

            'in_stock' => $query->whereRaw(
                '(i.quantity - i.reserved_quantity)
                > i.minimum_stock'
            ),

            default => null,
        };

        match ($request->input('sort')) {
            'oldest' => $query->orderBy('i.id'),

            'product_asc' => $query
                ->orderBy('p.name')
                ->orderBy('ps.sku_code'),

            'product_desc' => $query
                ->orderByDesc('p.name')
                ->orderByDesc('ps.sku_code'),

            'quantity_desc' => $query
                ->orderByDesc('i.quantity')
                ->orderBy('p.name'),

            'available_asc' => $query
                ->orderBy('available_quantity')
                ->orderBy('p.name'),

            'available_desc' => $query
                ->orderByDesc('available_quantity')
                ->orderBy('p.name'),

            'sold_desc' => $query
                ->orderByDesc('i.sold_quantity')
                ->orderBy('p.name'),

            default => $query
                ->orderByRaw(
                    'CASE
                        WHEN (
                            i.quantity - i.reserved_quantity
                        ) <= 0 THEN 0
                        WHEN (
                            i.quantity - i.reserved_quantity
                        ) <= i.minimum_stock THEN 1
                        ELSE 2
                    END'
                )
                ->orderBy('p.name')
                ->orderBy('ps.sku_code'),
        };

        $inventories = $query
            ->paginate(20)
            ->withQueryString();

        $warehouses = DB::table('warehouses')
            ->orderByDesc('status')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'status',
            ]);

        $statistics = [
            'inventory_rows' => DB::table('inventories')
                ->count(),

            'total_quantity' => (int) DB::table(
                'inventories'
            )->sum('quantity'),

            'reserved_quantity' => (int) DB::table(
                'inventories'
            )->sum('reserved_quantity'),

            'available_quantity' => (int) DB::table(
                'inventories'
            )
                ->selectRaw(
                    'COALESCE(
                        SUM(quantity - reserved_quantity),
                        0
                    ) as total'
                )
                ->value('total'),

            'sold_quantity' => (int) DB::table(
                'inventories'
            )->sum('sold_quantity'),

            'low_stock_rows' => DB::table('inventories')
                ->whereRaw(
                    '(quantity - reserved_quantity)
                    <= minimum_stock'
                )
                ->whereRaw(
                    '(quantity - reserved_quantity) > 0'
                )
                ->count(),

            'out_of_stock_rows' => DB::table('inventories')
                ->whereRaw(
                    '(quantity - reserved_quantity) <= 0'
                )
                ->count(),
        ];

        return view('admin.inventories.index', [
            'inventories' => $inventories,
            'warehouses' => $warehouses,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Hiển thị chi tiết một dòng tồn kho.
     */
    public function show(int $inventory): View
    {
        $inventoryDetail = DB::table('inventories as i')
            ->join(
                'warehouses as w',
                'i.warehouse_id',
                '=',
                'w.id'
            )
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
            ->where('i.id', $inventory)
            ->whereNull('p.deleted_at')
            ->select([
                'i.id',
                'i.warehouse_id',
                'i.sku_id',
                'i.quantity',
                'i.reserved_quantity',
                'i.sold_quantity',
                'i.minimum_stock',
                'i.created_at',
                'i.updated_at',

                'w.name as warehouse_name',
                'w.address as warehouse_address',
                'w.status as warehouse_status',

                'ps.sku_code',
                'ps.barcode',
                'ps.price',
                'ps.cost_price',
                'ps.weight',
                'ps.status as sku_status',

                'p.id as product_id',
                'p.name as product_name',
                'p.slug as product_slug',
                'p.status as product_status',

                'pv.id as variant_id',
                'pv.name as variant_name',
            ])
            ->selectRaw(
                '(i.quantity - i.reserved_quantity)
                as available_quantity'
            )
            ->first();

        abort_if(! $inventoryDetail, 404);

        $transactions = DB::table(
            'inventory_transactions as it'
        )
            ->leftJoin(
                'users as u',
                'it.created_by',
                '=',
                'u.id'
            )
            ->where(
                'it.warehouse_id',
                $inventoryDetail->warehouse_id
            )
            ->where(
                'it.sku_id',
                $inventoryDetail->sku_id
            )
            ->orderByDesc('it.id')
            ->select([
                'it.id',
                'it.type',
                'it.quantity',
                'it.reference_type',
                'it.reference_id',
                'it.note',
                'it.created_by',
                'it.created_at',
                'it.updated_at',
                'u.name as creator_name',
                'u.email as creator_email',
            ])
            ->paginate(20)
            ->withQueryString();

        $transactionStatistics = DB::table(
            'inventory_transactions'
        )
            ->where(
                'warehouse_id',
                $inventoryDetail->warehouse_id
            )
            ->where(
                'sku_id',
                $inventoryDetail->sku_id
            )
            ->selectRaw(
                "
                COUNT(*) as total_transactions,
                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'import'
                            THEN quantity
                            ELSE 0
                        END
                    ),
                    0
                ) as imported_quantity,
                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'return'
                            THEN quantity
                            ELSE 0
                        END
                    ),
                    0
                ) as returned_quantity,
                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'export'
                            THEN quantity
                            ELSE 0
                        END
                    ),
                    0
                ) as exported_quantity
                "
            )
            ->first();

        return view('admin.inventories.show', [
            'inventory' => $inventoryDetail,
            'transactions' => $transactions,
            'transactionStatistics' =>
                $transactionStatistics,
        ]);
    }

    /**
     * Cập nhật mức tồn tối thiểu.
     */
    public function updateMinimumStock(
        Request $request,
        int $inventory
    ): RedirectResponse {
        $validated = $request->validate(
            [
                'minimum_stock' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:2147483647',
                ],
            ],
            [
                'minimum_stock.required' =>
                    'Vui lòng nhập mức tồn tối thiểu.',

                'minimum_stock.integer' =>
                    'Mức tồn tối thiểu phải là số nguyên.',

                'minimum_stock.min' =>
                    'Mức tồn tối thiểu không được nhỏ hơn 0.',
            ]
        );

        $exists = DB::table('inventories')
            ->where('id', $inventory)
            ->exists();

        abort_if(! $exists, 404);

        DB::table('inventories')
            ->where('id', $inventory)
            ->update([
                'minimum_stock' => (int) (
                    $validated['minimum_stock']
                ),
                'updated_at' => now(),
            ]);

        return back()->with(
            'success',
            'Cập nhật mức tồn tối thiểu thành công.'
        );
    }

    /**
     * Tạo giao dịch nhập, xuất, trả hoặc điều chỉnh kho.
     */
    public function storeTransaction(
        Request $request,
        int $inventory
    ): RedirectResponse {
        $validated = $request->validate(
            [
                'type' => [
                    'required',
                    Rule::in([
                        'import',
                        'export',
                        'return',
                        'adjust',
                    ]),
                ],

                'quantity' => [
                    'required',
                    'integer',
                    'not_in:0',
                    'min:-2147483648',
                    'max:2147483647',
                ],

                'reference_type' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'reference_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],

                'note' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],
            ],
            [
                'type.required' =>
                    'Vui lòng chọn loại giao dịch.',

                'type.in' =>
                    'Loại giao dịch kho không hợp lệ.',

                'quantity.required' =>
                    'Vui lòng nhập số lượng.',

                'quantity.integer' =>
                    'Số lượng phải là số nguyên.',

                'quantity.not_in' =>
                    'Số lượng giao dịch phải khác 0.',

                'reference_id.integer' =>
                    'ID tham chiếu phải là số nguyên.',
            ]
        );

        $type = $validated['type'];
        $enteredQuantity = (int) $validated['quantity'];

        if (
            in_array(
                $type,
                ['import', 'export', 'return'],
                true
            )
            && $enteredQuantity <= 0
        ) {
            throw ValidationException::withMessages([
                'quantity' =>
                    'Số lượng phải lớn hơn 0 với loại giao dịch đã chọn.',
            ]);
        }

        DB::beginTransaction();

        try {
            $inventoryRow = DB::table('inventories')
                ->where('id', $inventory)
                ->lockForUpdate()
                ->first();

            abort_if(! $inventoryRow, 404);

            $currentQuantity =
                (int) $inventoryRow->quantity;

            $reservedQuantity =
                (int) $inventoryRow->reserved_quantity;

            $availableQuantity =
                $currentQuantity - $reservedQuantity;

            $quantityDelta = match ($type) {
                'import', 'return' =>
                    $enteredQuantity,

                'export' =>
                    -$enteredQuantity,

                'adjust' =>
                    $enteredQuantity,
            };

            $newQuantity =
                $currentQuantity + $quantityDelta;

            if ($type === 'export') {
                if ($enteredQuantity > $availableQuantity) {
                    throw ValidationException::withMessages([
                        'quantity' =>
                            'Không thể xuất vượt số lượng khả dụng hiện tại ('
                            . number_format(
                                max(0, $availableQuantity)
                            )
                            . ').',
                    ]);
                }
            }

            if ($newQuantity < 0) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'Giao dịch làm tổng tồn kho nhỏ hơn 0.',
                ]);
            }

            if ($newQuantity < $reservedQuantity) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'Tổng tồn sau giao dịch không được nhỏ hơn số lượng đang giữ ('
                        . number_format($reservedQuantity)
                        . ').',
                ]);
            }

            DB::table('inventories')
                ->where('id', $inventoryRow->id)
                ->update([
                    'quantity' => $newQuantity,
                    'updated_at' => now(),
                ]);

            DB::table('inventory_transactions')
                ->insert([
                    'warehouse_id' =>
                        $inventoryRow->warehouse_id,

                    'sku_id' =>
                        $inventoryRow->sku_id,

                    'type' => $type,

                    /*
                     * Import, export và return lưu số dương.
                     * Adjust lưu số có dấu để thể hiện tăng/giảm.
                     */
                    'quantity' => $enteredQuantity,

                    'reference_type' =>
                        $this->nullableTrim(
                            $validated['reference_type']
                                ?? null
                        ),

                    'reference_id' =>
                        $validated['reference_id']
                            ?? null,

                    'note' => $this->buildTransactionNote(
                        $validated['note'] ?? null,
                        $currentQuantity,
                        $newQuantity
                    ),

                    'created_by' =>
                        auth()->id(),

                    'created_at' => now(),

                    'updated_at' => now(),
                ]);

            DB::commit();

            return back()->with(
                'success',
                'Tạo giao dịch kho thành công. Tồn kho mới: '
                . number_format($newQuantity)
                . '.'
            );
        } catch (Throwable $exception) {
            DB::rollBack();

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi giao dịch kho: '
                            . $exception->getMessage()
                        : 'Không thể tạo giao dịch kho.'
                );
        }
    }

    /**
     * Gắn số tồn trước/sau vào ghi chú giao dịch.
     */
    private function buildTransactionNote(
        mixed $note,
        int $beforeQuantity,
        int $afterQuantity
    ): string {
        $systemNote =
            'Tồn trước: '
            . number_format($beforeQuantity)
            . '; tồn sau: '
            . number_format($afterQuantity)
            . '.';

        $customNote = $this->nullableTrim($note);

        return $customNote
            ? $customNote . ' | ' . $systemNote
            : $systemNote;
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
