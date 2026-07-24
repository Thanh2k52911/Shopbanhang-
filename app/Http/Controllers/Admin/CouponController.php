<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class CouponController extends Controller
{
    /**
     * Hiển thị danh sách mã giảm giá.
     */
    public function index(Request $request): View
    {
        $query = DB::table('coupons as c')
            ->leftJoin(
                'users as creator',
                'c.created_by',
                '=',
                'creator.id'
            )
            ->whereNull('c.deleted_at')
            ->select([
                'c.id',
                'c.code',
                'c.name',
                'c.description',
                'c.discount_type',
                'c.discount_value',
                'c.maximum_discount',
                'c.minimum_order_amount',
                'c.usage_limit',
                'c.usage_limit_per_user',
                'c.used_count',
                'c.first_order_only',
                'c.is_public',
                'c.status',
                'c.start_at',
                'c.end_at',
                'c.created_by',
                'c.created_at',
                'c.updated_at',
                'creator.name as creator_name',
            ])
            ->selectSub(
                fn (Builder $builder) => $builder
                    ->from('coupon_products')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('coupon_products.coupon_id', 'c.id'),
                'products_count'
            )
            ->selectSub(
                fn (Builder $builder) => $builder
                    ->from('coupon_categories')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('coupon_categories.coupon_id', 'c.id'),
                'categories_count'
            )
            ->selectSub(
                fn (Builder $builder) => $builder
                    ->from('coupon_users')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('coupon_users.coupon_id', 'c.id'),
                'users_count'
            )
            ->selectSub(
                fn (Builder $builder) => $builder
                    ->from('saved_coupons')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('saved_coupons.coupon_id', 'c.id'),
                'saved_count'
            );

        $keyword = trim((string) $request->input('keyword'));

        if ($keyword !== '') {
            $query->where(function (Builder $builder) use ($keyword): void {
                $builder
                    ->where('c.code', 'like', '%' . $keyword . '%')
                    ->orWhere('c.name', 'like', '%' . $keyword . '%')
                    ->orWhere('c.description', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('discount_type')) {
            $query->where(
                'c.discount_type',
                $request->input('discount_type')
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'c.status',
                (int) $request->input('status')
            );
        }

        if ($request->filled('visibility')) {
            $query->where(
                'c.is_public',
                $request->input('visibility') === 'public' ? 1 : 0
            );
        }

        match ($request->input('validity')) {
            'active' => $query
                ->where('c.status', 1)
                ->where(function (Builder $builder): void {
                    $builder
                        ->whereNull('c.start_at')
                        ->orWhere('c.start_at', '<=', now());
                })
                ->where(function (Builder $builder): void {
                    $builder
                        ->whereNull('c.end_at')
                        ->orWhere('c.end_at', '>=', now());
                })
                ->where(function (Builder $builder): void {
                    $builder
                        ->whereNull('c.usage_limit')
                        ->orWhereColumn('c.used_count', '<', 'c.usage_limit');
                }),

            'scheduled' => $query->where('c.start_at', '>', now()),

            'expired' => $query->where(function (Builder $builder): void {
                $builder
                    ->where('c.end_at', '<', now())
                    ->orWhere(function (Builder $inner): void {
                        $inner
                            ->whereNotNull('c.usage_limit')
                            ->whereColumn(
                                'c.used_count',
                                '>=',
                                'c.usage_limit'
                            );
                    });
            }),

            default => null,
        };

        match ($request->input('sort')) {
            'oldest' => $query->orderBy('c.id'),
            'code_asc' => $query->orderBy('c.code'),
            'code_desc' => $query->orderByDesc('c.code'),
            'used_desc' => $query->orderByDesc('c.used_count'),
            'start_desc' => $query->orderByDesc('c.start_at'),
            'end_asc' => $query->orderBy('c.end_at'),
            default => $query->orderByDesc('c.id'),
        };

        $coupons = $query
            ->paginate(20)
            ->withQueryString();

        $statistics = [
            'total' => DB::table('coupons')
                ->whereNull('deleted_at')
                ->count(),

            'active' => DB::table('coupons')
                ->whereNull('deleted_at')
                ->where('status', 1)
                ->where(function (Builder $builder): void {
                    $builder
                        ->whereNull('start_at')
                        ->orWhere('start_at', '<=', now());
                })
                ->where(function (Builder $builder): void {
                    $builder
                        ->whereNull('end_at')
                        ->orWhere('end_at', '>=', now());
                })
                ->where(function (Builder $builder): void {
                    $builder
                        ->whereNull('usage_limit')
                        ->orWhereColumn('used_count', '<', 'usage_limit');
                })
                ->count(),

            'scheduled' => DB::table('coupons')
                ->whereNull('deleted_at')
                ->where('start_at', '>', now())
                ->count(),

            'expired' => DB::table('coupons')
                ->whereNull('deleted_at')
                ->where(function (Builder $builder): void {
                    $builder
                        ->where('end_at', '<', now())
                        ->orWhere(function (Builder $inner): void {
                            $inner
                                ->whereNotNull('usage_limit')
                                ->whereColumn(
                                    'used_count',
                                    '>=',
                                    'usage_limit'
                                );
                        });
                })
                ->count(),

            'public' => DB::table('coupons')
                ->whereNull('deleted_at')
                ->where('is_public', 1)
                ->count(),

            'total_discount' => (float) DB::table('coupon_usages')
                ->sum('discount_amount'),
        ];

        return view('admin.coupons.index', [
            'coupons' => $coupons,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Hiển thị form thêm coupon.
     */
    public function create(): View
    {
        return view('admin.coupons.create', [
            'products' => $this->productOptions(),
            'categories' => $this->categoryOptions(),
            'users' => $this->userOptions(),
        ]);
    }

    /**
     * Lưu coupon mới.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        $this->validateCouponBusinessRules($validated);

        DB::beginTransaction();

        try {
            $couponId = DB::table('coupons')->insertGetId([
                ...$this->couponPayload($validated),
                'used_count' => 0,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->syncRelations(
                $couponId,
                $validated
            );

            DB::commit();

            return redirect()
                ->route('admin.coupons.show', $couponId)
                ->with('success', 'Thêm mã giảm giá thành công.');
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
                        ? 'Lỗi: ' . $exception->getMessage()
                        : 'Không thể thêm mã giảm giá.'
                );
        }
    }

    /**
     * Hiển thị chi tiết coupon.
     */
    public function show(int $coupon): View
    {
        $couponDetail = DB::table('coupons as c')
            ->leftJoin(
                'users as creator',
                'c.created_by',
                '=',
                'creator.id'
            )
            ->where('c.id', $coupon)
            ->whereNull('c.deleted_at')
            ->select([
                'c.*',
                'creator.name as creator_name',
                'creator.email as creator_email',
            ])
            ->first();

        abort_if(! $couponDetail, 404);

        $products = DB::table('coupon_products as cp')
            ->join('products as p', 'cp.product_id', '=', 'p.id')
            ->where('cp.coupon_id', $couponDetail->id)
            ->whereNull('p.deleted_at')
            ->orderBy('p.name')
            ->get([
                'p.id',
                'p.name',
                'p.slug',
                'p.status',
            ]);

        $categories = DB::table('coupon_categories as cc')
            ->join('categories as c', 'cc.category_id', '=', 'c.id')
            ->where('cc.coupon_id', $couponDetail->id)
            ->whereNull('c.deleted_at')
            ->orderBy('c.name')
            ->get([
                'c.id',
                'c.name',
                'c.slug',
                'c.status',
            ]);

        $users = DB::table('coupon_users as cu')
            ->join('users as u', 'cu.user_id', '=', 'u.id')
            ->where('cu.coupon_id', $couponDetail->id)
            ->orderBy('u.name')
            ->get([
                'u.id',
                'u.name',
                'u.email',
            ]);

        $usages = DB::table('coupon_usages as usage')
            ->leftJoin('users as u', 'usage.user_id', '=', 'u.id')
            ->join('orders as o', 'usage.order_id', '=', 'o.id')
            ->where('usage.coupon_id', $couponDetail->id)
            ->orderByDesc('usage.used_at')
            ->select([
                'usage.id',
                'usage.discount_amount',
                'usage.used_at',
                'usage.order_id',
                'u.name as user_name',
                'u.email as user_email',
                'o.order_code',
                'o.order_status',
            ])
            ->paginate(20)
            ->withQueryString();

        $statistics = [
            'usage_count' => DB::table('coupon_usages')
                ->where('coupon_id', $couponDetail->id)
                ->count(),

            'total_discount' => (float) DB::table('coupon_usages')
                ->where('coupon_id', $couponDetail->id)
                ->sum('discount_amount'),

            'saved_count' => DB::table('saved_coupons')
                ->where('coupon_id', $couponDetail->id)
                ->count(),

            'products_count' => $products->count(),
            'categories_count' => $categories->count(),
            'users_count' => $users->count(),
        ];

        return view('admin.coupons.show', [
            'coupon' => $couponDetail,
            'products' => $products,
            'categories' => $categories,
            'users' => $users,
            'usages' => $usages,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Hiển thị form sửa coupon.
     */
    public function edit(int $coupon): View
    {
        $couponDetail = DB::table('coupons')
            ->where('id', $coupon)
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $couponDetail, 404);

        return view('admin.coupons.edit', [
            'coupon' => $couponDetail,
            'products' => $this->productOptions(),
            'categories' => $this->categoryOptions(),
            'users' => $this->userOptions(),

            'selectedProductIds' => DB::table('coupon_products')
                ->where('coupon_id', $couponDetail->id)
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->all(),

            'selectedCategoryIds' => DB::table('coupon_categories')
                ->where('coupon_id', $couponDetail->id)
                ->pluck('category_id')
                ->map(fn ($id) => (int) $id)
                ->all(),

            'selectedUserIds' => DB::table('coupon_users')
                ->where('coupon_id', $couponDetail->id)
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->all(),
        ]);
    }

    /**
     * Cập nhật coupon.
     */
    public function update(
        Request $request,
        int $coupon
    ): RedirectResponse {
        $couponDetail = DB::table('coupons')
            ->where('id', $coupon)
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $couponDetail, 404);

        $validated = $request->validate(
            $this->validationRules($couponDetail->id),
            $this->validationMessages()
        );

        $this->validateCouponBusinessRules($validated);

        DB::beginTransaction();

        try {
            DB::table('coupons')
                ->where('id', $couponDetail->id)
                ->update([
                    ...$this->couponPayload($validated),
                    'updated_at' => now(),
                ]);

            $this->syncRelations(
                $couponDetail->id,
                $validated
            );

            DB::commit();

            return redirect()
                ->route('admin.coupons.show', $couponDetail->id)
                ->with('success', 'Cập nhật mã giảm giá thành công.');
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
                        ? 'Lỗi: ' . $exception->getMessage()
                        : 'Không thể cập nhật mã giảm giá.'
                );
        }
    }

    /**
     * Xóa mềm coupon.
     */
    public function destroy(int $coupon): RedirectResponse
    {
        $couponDetail = DB::table('coupons')
            ->where('id', $coupon)
            ->whereNull('deleted_at')
            ->first([
                'id',
                'code',
            ]);

        abort_if(! $couponDetail, 404);

        $used = DB::table('coupon_usages')
            ->where('coupon_id', $couponDetail->id)
            ->exists();

        if ($used) {
            return back()->with(
                'error',
                'Mã giảm giá đã được sử dụng nên không thể xóa. Hãy chuyển trạng thái sang ngừng hoạt động.'
            );
        }

        DB::beginTransaction();

        try {
            $now = now();

            DB::table('coupons')
                ->where('id', $couponDetail->id)
                ->update([
                    'status' => 0,
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::commit();

            return redirect()
                ->route('admin.coupons.index')
                ->with(
                    'success',
                    'Đã xóa mã giảm giá "' . $couponDetail->code . '".'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi xóa coupon: ' . $exception->getMessage()
                    : 'Không thể xóa mã giảm giá.'
            );
        }
    }

    /**
     * Quy tắc validation.
     *
     * @return array<string, mixed>
     */
    private function validationRules(
        ?int $ignoreId = null
    ): array {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')
                    ->ignore($ignoreId),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'discount_type' => [
                'required',
                Rule::in([
                    'fixed',
                    'percentage',
                    'free_shipping',
                ]),
            ],

            'discount_value' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'maximum_discount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'minimum_order_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'usage_limit' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'usage_limit_per_user' => [
                'required',
                'integer',
                'min:1',
            ],

            'first_order_only' => [
                'nullable',
                'boolean',
            ],

            'is_public' => [
                'nullable',
                'boolean',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],

            'start_at' => [
                'nullable',
                'date',
            ],

            'end_at' => [
                'nullable',
                'date',
            ],

            'product_ids' => [
                'nullable',
                'array',
            ],

            'product_ids.*' => [
                'integer',
                Rule::exists('products', 'id')
                    ->whereNull('deleted_at'),
            ],

            'category_ids' => [
                'nullable',
                'array',
            ],

            'category_ids.*' => [
                'integer',
                Rule::exists('categories', 'id')
                    ->whereNull('deleted_at'),
            ],

            'user_ids' => [
                'nullable',
                'array',
            ],

            'user_ids.*' => [
                'integer',
                Rule::exists('users', 'id'),
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
            'code.required' => 'Vui lòng nhập mã coupon.',
            'code.unique' => 'Mã coupon đã tồn tại.',
            'code.max' => 'Mã coupon không được vượt quá 50 ký tự.',

            'name.required' => 'Vui lòng nhập tên coupon.',

            'discount_type.required' =>
                'Vui lòng chọn loại giảm giá.',

            'discount_type.in' =>
                'Loại giảm giá không hợp lệ.',

            'discount_value.required' =>
                'Vui lòng nhập giá trị giảm.',

            'discount_value.numeric' =>
                'Giá trị giảm phải là số.',

            'usage_limit.integer' =>
                'Tổng lượt dùng phải là số nguyên.',

            'usage_limit_per_user.required' =>
                'Vui lòng nhập giới hạn mỗi người.',

            'usage_limit_per_user.min' =>
                'Giới hạn mỗi người phải ít nhất là 1.',

            'end_at.date' =>
                'Thời gian kết thúc không hợp lệ.',
        ];
    }

    /**
     * Kiểm tra các quy tắc nghiệp vụ.
     */
    private function validateCouponBusinessRules(
        array $validated
    ): void {
        $type = $validated['discount_type'];
        $discountValue = (float) $validated['discount_value'];

        if ($type === 'percentage' && $discountValue > 100) {
            throw ValidationException::withMessages([
                'discount_value' =>
                    'Giảm theo phần trăm không được vượt quá 100%.',
            ]);
        }

        if (
            $type === 'free_shipping'
            && $discountValue !== 0.0
        ) {
            throw ValidationException::withMessages([
                'discount_value' =>
                    'Coupon miễn phí vận chuyển phải có giá trị giảm bằng 0.',
            ]);
        }

        if (
            ! empty($validated['start_at'])
            && ! empty($validated['end_at'])
            && strtotime($validated['end_at'])
                <= strtotime($validated['start_at'])
        ) {
            throw ValidationException::withMessages([
                'end_at' =>
                    'Thời gian kết thúc phải sau thời gian bắt đầu.',
            ]);
        }

        if (
            ! empty($validated['usage_limit'])
            && (int) $validated['usage_limit_per_user']
                > (int) $validated['usage_limit']
        ) {
            throw ValidationException::withMessages([
                'usage_limit_per_user' =>
                    'Giới hạn mỗi người không được lớn hơn tổng lượt sử dụng.',
            ]);
        }
    }

    /**
     * Chuẩn hóa dữ liệu coupon.
     *
     * @return array<string, mixed>
     */
    private function couponPayload(array $validated): array
    {
        $type = $validated['discount_type'];

        return [
            'code' => Str::upper(
                trim($validated['code'])
            ),

            'name' => trim($validated['name']),

            'description' => $this->nullableTrim(
                $validated['description'] ?? null
            ),

            'discount_type' => $type,

            'discount_value' =>
                $type === 'free_shipping'
                    ? 0
                    : (float) $validated['discount_value'],

            'maximum_discount' =>
                $type === 'percentage'
                    ? ($validated['maximum_discount'] ?? null)
                    : null,

            'minimum_order_amount' =>
                (float) (
                    $validated['minimum_order_amount']
                    ?? 0
                ),

            'usage_limit' =>
                $validated['usage_limit'] ?? null,

            'usage_limit_per_user' =>
                (int) $validated['usage_limit_per_user'],

            'first_order_only' => (int) (
                $validated['first_order_only'] ?? 0
            ),

            'is_public' => (int) (
                $validated['is_public'] ?? 1
            ),

            'status' => (int) (
                $validated['status'] ?? 1
            ),

            'start_at' =>
                $validated['start_at'] ?? null,

            'end_at' =>
                $validated['end_at'] ?? null,
        ];
    }

    /**
     * Đồng bộ sản phẩm, danh mục và người dùng áp dụng.
     */
    private function syncRelations(
        int $couponId,
        array $validated
    ): void {
        DB::table('coupon_products')
            ->where('coupon_id', $couponId)
            ->delete();

        DB::table('coupon_categories')
            ->where('coupon_id', $couponId)
            ->delete();

        DB::table('coupon_users')
            ->where('coupon_id', $couponId)
            ->delete();

        $productRows = collect(
            $validated['product_ids'] ?? []
        )
            ->unique()
            ->map(fn ($id) => [
                'coupon_id' => $couponId,
                'product_id' => (int) $id,
            ])
            ->values()
            ->all();

        $categoryRows = collect(
            $validated['category_ids'] ?? []
        )
            ->unique()
            ->map(fn ($id) => [
                'coupon_id' => $couponId,
                'category_id' => (int) $id,
            ])
            ->values()
            ->all();

        $userRows = collect(
            $validated['user_ids'] ?? []
        )
            ->unique()
            ->map(fn ($id) => [
                'coupon_id' => $couponId,
                'user_id' => (int) $id,
            ])
            ->values()
            ->all();

        if ($productRows !== []) {
            DB::table('coupon_products')->insert($productRows);
        }

        if ($categoryRows !== []) {
            DB::table('coupon_categories')->insert($categoryRows);
        }

        if ($userRows !== []) {
            DB::table('coupon_users')->insert($userRows);
        }
    }

    /**
     * Danh sách sản phẩm cho form.
     */
    private function productOptions()
    {
        return DB::table('products')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'status',
            ]);
    }

    /**
     * Danh sách danh mục cho form.
     */
    private function categoryOptions()
    {
        return DB::table('categories')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'parent_id',
                'name',
                'status',
            ]);
    }

    /**
     * Danh sách người dùng cho form.
     */
    private function userOptions()
    {
        return DB::table('users')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
            ]);
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
