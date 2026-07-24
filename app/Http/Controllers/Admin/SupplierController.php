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

class SupplierController extends Controller
{
    /**
     * Hiển thị danh sách nhà cung cấp.
     */
    public function index(Request $request): View
    {
        $query = DB::table('suppliers as s')
            ->whereNull('s.deleted_at')
            ->select([
                's.id',
                's.name',
                's.contact_name',
                's.phone',
                's.email',
                's.address',
                's.tax_code',
                's.sort_order',
                's.status',
                's.created_at',
                's.updated_at',
            ])
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('products')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn(
                            'products.supplier_id',
                            's.id'
                        )
                        ->whereNull('products.deleted_at');
                },
                'products_count'
            );

        $keyword = trim(
            (string) $request->input('keyword')
        );

        if ($keyword !== '') {
            $query->where(
                function (Builder $builder) use ($keyword): void {
                    $builder
                        ->where(
                            's.name',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            's.contact_name',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            's.phone',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            's.email',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            's.address',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            's.tax_code',
                            'like',
                            '%' . $keyword . '%'
                        );
                }
            );
        }

        if ($request->filled('status')) {
            $query->where(
                's.status',
                (int) $request->input('status')
            );
        }

        match ($request->input('sort')) {
            'oldest' => $query->orderBy('s.id'),
            'name_asc' => $query->orderBy('s.name'),
            'name_desc' => $query->orderByDesc('s.name'),
            'sort_asc' => $query
                ->orderBy('s.sort_order')
                ->orderBy('s.name'),
            'sort_desc' => $query
                ->orderByDesc('s.sort_order')
                ->orderBy('s.name'),
            'products_desc' => $query
                ->orderByDesc('products_count')
                ->orderBy('s.name'),
            default => $query
                ->orderBy('s.sort_order')
                ->orderByDesc('s.id'),
        };

        $suppliers = $query
            ->paginate(20)
            ->withQueryString();

        $statistics = [
            'total' => DB::table('suppliers')
                ->whereNull('deleted_at')
                ->count(),

            'active' => DB::table('suppliers')
                ->whereNull('deleted_at')
                ->where('status', 1)
                ->count(),

            'inactive' => DB::table('suppliers')
                ->whereNull('deleted_at')
                ->where('status', 0)
                ->count(),

            'with_products' => DB::table('suppliers as s')
                ->whereNull('s.deleted_at')
                ->whereExists(
                    function (Builder $builder): void {
                        $builder
                            ->selectRaw('1')
                            ->from('products')
                            ->whereColumn(
                                'products.supplier_id',
                                's.id'
                            )
                            ->whereNull('products.deleted_at');
                    }
                )
                ->count(),
        ];

        return view('admin.suppliers.index', [
            'suppliers' => $suppliers,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Hiển thị form thêm nhà cung cấp.
     */
    public function create(): View
    {
        return view('admin.suppliers.create');
    }

    /**
     * Lưu nhà cung cấp mới.
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

            $supplierId = DB::table('suppliers')
                ->insertGetId([
                    'name' => trim($validated['name']),

                    'contact_name' => $this->nullableTrim(
                        $validated['contact_name'] ?? null
                    ),

                    'phone' => $this->nullableTrim(
                        $validated['phone'] ?? null
                    ),

                    'email' => $this->nullableTrim(
                        $validated['email'] ?? null
                    ),

                    'address' => $this->nullableTrim(
                        $validated['address'] ?? null
                    ),

                    'tax_code' => $this->nullableTrim(
                        $validated['tax_code'] ?? null
                    ),

                    'sort_order' => (int) (
                        $validated['sort_order'] ?? 0
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
                    'admin.suppliers.show',
                    $supplierId
                )
                ->with(
                    'success',
                    'Thêm nhà cung cấp thành công.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi: ' . $exception->getMessage()
                        : 'Không thể thêm nhà cung cấp.'
                );
        }
    }

    /**
     * Hiển thị chi tiết nhà cung cấp.
     */
    public function show(int $supplier): View
    {
        $supplierDetail = DB::table('suppliers')
            ->where('id', $supplier)
            ->whereNull('deleted_at')
            ->first([
                'id',
                'name',
                'contact_name',
                'phone',
                'email',
                'address',
                'tax_code',
                'sort_order',
                'status',
                'created_at',
                'updated_at',
            ]);

        abort_if(! $supplierDetail, 404);

        $products = DB::table('products')
            ->where('supplier_id', $supplierDetail->id)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get([
                'id',
                'name',
                'slug',
                'status',
                'is_featured',
                'view_count',
                'created_at',
            ]);

        $statistics = [
            'products' => DB::table('products')
                ->where(
                    'supplier_id',
                    $supplierDetail->id
                )
                ->whereNull('deleted_at')
                ->count(),

            'active_products' => DB::table('products')
                ->where(
                    'supplier_id',
                    $supplierDetail->id
                )
                ->whereNull('deleted_at')
                ->where('status', 1)
                ->count(),

            'featured_products' => DB::table('products')
                ->where(
                    'supplier_id',
                    $supplierDetail->id
                )
                ->whereNull('deleted_at')
                ->where('is_featured', 1)
                ->count(),

            'total_views' => (int) DB::table('products')
                ->where(
                    'supplier_id',
                    $supplierDetail->id
                )
                ->whereNull('deleted_at')
                ->sum('view_count'),
        ];

        return view('admin.suppliers.show', [
            'supplier' => $supplierDetail,
            'products' => $products,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Hiển thị form chỉnh sửa nhà cung cấp.
     */
    public function edit(int $supplier): View
    {
        $supplierDetail = DB::table('suppliers')
            ->where('id', $supplier)
            ->whereNull('deleted_at')
            ->first([
                'id',
                'name',
                'contact_name',
                'phone',
                'email',
                'address',
                'tax_code',
                'sort_order',
                'status',
                'created_at',
                'updated_at',
            ]);

        abort_if(! $supplierDetail, 404);

        return view('admin.suppliers.edit', [
            'supplier' => $supplierDetail,
        ]);
    }

    /**
     * Cập nhật nhà cung cấp.
     */
    public function update(
        Request $request,
        int $supplier
    ): RedirectResponse {
        $supplierDetail = DB::table('suppliers')
            ->where('id', $supplier)
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $supplierDetail, 404);

        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        DB::beginTransaction();

        try {
            DB::table('suppliers')
                ->where('id', $supplierDetail->id)
                ->update([
                    'name' => trim($validated['name']),

                    'contact_name' => $this->nullableTrim(
                        $validated['contact_name'] ?? null
                    ),

                    'phone' => $this->nullableTrim(
                        $validated['phone'] ?? null
                    ),

                    'email' => $this->nullableTrim(
                        $validated['email'] ?? null
                    ),

                    'address' => $this->nullableTrim(
                        $validated['address'] ?? null
                    ),

                    'tax_code' => $this->nullableTrim(
                        $validated['tax_code'] ?? null
                    ),

                    'sort_order' => (int) (
                        $validated['sort_order'] ?? 0
                    ),

                    'status' => (int) (
                        $validated['status'] ?? 1
                    ),

                    'updated_at' => now(),
                ]);

            DB::commit();

            return redirect()
                ->route(
                    'admin.suppliers.show',
                    $supplierDetail->id
                )
                ->with(
                    'success',
                    'Cập nhật nhà cung cấp thành công.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi: ' . $exception->getMessage()
                        : 'Không thể cập nhật nhà cung cấp.'
                );
        }
    }

    /**
     * Xóa mềm nhà cung cấp.
     */
    public function destroy(
        int $supplier
    ): RedirectResponse {
        $supplierDetail = DB::table('suppliers')
            ->where('id', $supplier)
            ->whereNull('deleted_at')
            ->first([
                'id',
                'name',
            ]);

        abort_if(! $supplierDetail, 404);

        $hasProducts = DB::table('products')
            ->where(
                'supplier_id',
                $supplierDetail->id
            )
            ->whereNull('deleted_at')
            ->exists();

        if ($hasProducts) {
            return back()->with(
                'error',
                'Không thể xóa nhà cung cấp này vì vẫn còn sản phẩm.'
            );
        }

        DB::beginTransaction();

        try {
            $now = now();

            DB::table('suppliers')
                ->where('id', $supplierDetail->id)
                ->whereNull('deleted_at')
                ->update([
                    'status' => 0,
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::commit();

            return redirect()
                ->route('admin.suppliers.index')
                ->with(
                    'success',
                    'Đã xóa nhà cung cấp "'
                    . $supplierDetail->name
                    . '".'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi xóa nhà cung cấp: '
                        . $exception->getMessage()
                    : 'Không thể xóa nhà cung cấp.'
            );
        }
    }

    /**
     * Quy tắc validation dùng chung.
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

            'contact_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:15',
                'regex:/^[0-9+\-\s().]+$/',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'tax_code' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
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
                'Vui lòng nhập tên nhà cung cấp.',

            'name.max' =>
                'Tên nhà cung cấp không được vượt quá 255 ký tự.',

            'contact_name.max' =>
                'Tên người liên hệ không được vượt quá 255 ký tự.',

            'phone.max' =>
                'Số điện thoại không được vượt quá 15 ký tự.',

            'phone.regex' =>
                'Số điện thoại không đúng định dạng.',

            'email.email' =>
                'Email không đúng định dạng.',

            'address.max' =>
                'Địa chỉ không được vượt quá 255 ký tự.',

            'tax_code.max' =>
                'Mã số thuế không được vượt quá 255 ký tự.',

            'sort_order.integer' =>
                'Thứ tự hiển thị phải là số nguyên.',

            'sort_order.min' =>
                'Thứ tự hiển thị không được nhỏ hơn 0.',
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
