<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class BrandController extends Controller
{
    /**
     * Hiển thị danh sách thương hiệu.
     */
    public function index(Request $request): View
    {
        $query = DB::table('brands as b')
            ->whereNull('b.deleted_at')
            ->select([
                'b.id',
                'b.name',
                'b.slug',
                'b.thumbnail',
                'b.country',
                'b.website',
                'b.description',
                'b.sort_order',
                'b.status',
                'b.created_at',
                'b.updated_at',
            ])
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('products')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn(
                            'products.brand_id',
                            'b.id'
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
                            'b.name',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'b.slug',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'b.country',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'b.website',
                            'like',
                            '%' . $keyword . '%'
                        );
                }
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'b.status',
                (int) $request->input('status')
            );
        }

        if ($request->filled('country')) {
            $query->where(
                'b.country',
                $request->input('country')
            );
        }

        match ($request->input('sort')) {
            'oldest' => $query->orderBy('b.id'),
            'name_asc' => $query->orderBy('b.name'),
            'name_desc' => $query->orderByDesc('b.name'),
            'sort_asc' => $query
                ->orderBy('b.sort_order')
                ->orderBy('b.name'),
            'sort_desc' => $query
                ->orderByDesc('b.sort_order')
                ->orderBy('b.name'),
            'products_desc' => $query
                ->orderByDesc('products_count')
                ->orderBy('b.name'),
            default => $query
                ->orderBy('b.sort_order')
                ->orderByDesc('b.id'),
        };

        $brands = $query
            ->paginate(20)
            ->withQueryString();

        $countries = DB::table('brands')
            ->whereNull('deleted_at')
            ->whereNotNull('country')
            ->where('country', '<>', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        $statistics = [
            'total' => DB::table('brands')
                ->whereNull('deleted_at')
                ->count(),

            'active' => DB::table('brands')
                ->whereNull('deleted_at')
                ->where('status', 1)
                ->count(),

            'inactive' => DB::table('brands')
                ->whereNull('deleted_at')
                ->where('status', 0)
                ->count(),

            'with_products' => DB::table('brands as b')
                ->whereNull('b.deleted_at')
                ->whereExists(
                    function (Builder $builder): void {
                        $builder
                            ->selectRaw('1')
                            ->from('products')
                            ->whereColumn(
                                'products.brand_id',
                                'b.id'
                            )
                            ->whereNull(
                                'products.deleted_at'
                            );
                    }
                )
                ->count(),
        ];

        return view('admin.brands.index', [
            'brands' => $brands,
            'countries' => $countries,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Hiển thị form thêm thương hiệu.
     */
    public function create(): View
    {
        return view('admin.brands.create');
    }

    /**
     * Lưu thương hiệu mới.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:120',
                ],

                'slug' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('brands', 'slug'),
                ],

                'thumbnail' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ],

                'country' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'website' => [
                    'nullable',
                    'url',
                    'max:255',
                ],

                'description' => [
                    'nullable',
                    'string',
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
            ],
            [
                'name.required' =>
                    'Vui lòng nhập tên thương hiệu.',

                'name.max' =>
                    'Tên thương hiệu không được vượt quá 120 ký tự.',

                'slug.unique' =>
                    'Slug thương hiệu đã tồn tại.',

                'thumbnail.image' =>
                    'File thumbnail phải là hình ảnh.',

                'thumbnail.mimes' =>
                    'Thumbnail phải có định dạng JPG, JPEG, PNG hoặc WEBP.',

                'thumbnail.max' =>
                    'Thumbnail không được lớn hơn 5 MB.',

                'website.url' =>
                    'Website phải là một URL hợp lệ.',
            ]
        );

        $storedThumbnailPath = null;

        DB::beginTransaction();

        try {
            $slug = $this->makeUniqueSlug(
                $validated['slug'] ?? null,
                $validated['name']
            );

            if ($request->hasFile('thumbnail')) {
                $storedThumbnailPath = $request
                    ->file('thumbnail')
                    ->store(
                        'brands',
                        'public'
                    );
            }

            $now = now();

            $brandId = DB::table('brands')
                ->insertGetId([
                    'name' => trim($validated['name']),

                    'slug' => $slug,

                    'thumbnail' =>
                        $storedThumbnailPath,

                    'country' => $this->nullableTrim(
                        $validated['country'] ?? null
                    ),

                    'website' => $this->nullableTrim(
                        $validated['website'] ?? null
                    ),

                    'description' =>
                        $validated['description'] ?? null,

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
                    'admin.brands.show',
                    $brandId
                )
                ->with(
                    'success',
                    'Thêm thương hiệu thành công.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            if ($storedThumbnailPath) {
                Storage::disk('public')
                    ->delete($storedThumbnailPath);
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi: ' . $exception->getMessage()
                        : 'Không thể thêm thương hiệu.'
                );
        }
    }

    /**
     * Hiển thị chi tiết thương hiệu.
     */
    public function show(int $brand): View
    {
        $brandDetail = DB::table('brands')
            ->where('id', $brand)
            ->whereNull('deleted_at')
            ->first([
                'id',
                'name',
                'slug',
                'thumbnail',
                'country',
                'website',
                'description',
                'sort_order',
                'status',
                'created_at',
                'updated_at',
            ]);

        abort_if(! $brandDetail, 404);

        $products = DB::table('products')
            ->where('brand_id', $brandDetail->id)
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
                    'brand_id',
                    $brandDetail->id
                )
                ->whereNull('deleted_at')
                ->count(),

            'active_products' => DB::table('products')
                ->where(
                    'brand_id',
                    $brandDetail->id
                )
                ->whereNull('deleted_at')
                ->where('status', 1)
                ->count(),

            'featured_products' => DB::table('products')
                ->where(
                    'brand_id',
                    $brandDetail->id
                )
                ->whereNull('deleted_at')
                ->where('is_featured', 1)
                ->count(),

            'total_views' => (int) DB::table('products')
                ->where(
                    'brand_id',
                    $brandDetail->id
                )
                ->whereNull('deleted_at')
                ->sum('view_count'),
        ];

        return view('admin.brands.show', [
            'brand' => $brandDetail,
            'products' => $products,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Hiển thị form chỉnh sửa thương hiệu.
     */
    public function edit(int $brand): View
    {
        $brandDetail = DB::table('brands')
            ->where('id', $brand)
            ->whereNull('deleted_at')
            ->first([
                'id',
                'name',
                'slug',
                'thumbnail',
                'country',
                'website',
                'description',
                'sort_order',
                'status',
                'created_at',
                'updated_at',
            ]);

        abort_if(! $brandDetail, 404);

        return view('admin.brands.edit', [
            'brand' => $brandDetail,
        ]);
    }

    /**
     * Cập nhật thương hiệu.
     */
    public function update(
        Request $request,
        int $brand
    ): RedirectResponse {
        $brandDetail = DB::table('brands')
            ->where('id', $brand)
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $brandDetail, 404);

        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:120',
                ],

                'slug' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('brands', 'slug')
                        ->ignore($brandDetail->id),
                ],

                'thumbnail' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ],

                'delete_thumbnail' => [
                    'nullable',
                    'boolean',
                ],

                'country' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'website' => [
                    'nullable',
                    'url',
                    'max:255',
                ],

                'description' => [
                    'nullable',
                    'string',
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
            ],
            [
                'name.required' =>
                    'Vui lòng nhập tên thương hiệu.',

                'name.max' =>
                    'Tên thương hiệu không được vượt quá 120 ký tự.',

                'slug.unique' =>
                    'Slug thương hiệu đã tồn tại.',

                'thumbnail.image' =>
                    'File thumbnail phải là hình ảnh.',

                'thumbnail.mimes' =>
                    'Thumbnail phải có định dạng JPG, JPEG, PNG hoặc WEBP.',

                'thumbnail.max' =>
                    'Thumbnail không được lớn hơn 5 MB.',

                'website.url' =>
                    'Website phải là một URL hợp lệ.',
            ]
        );

        $newThumbnailPath = null;
        $oldThumbnailPath = $brandDetail->thumbnail;
        $thumbnailToDelete = null;

        DB::beginTransaction();

        try {
            $slug = $this->makeUniqueSlug(
                $validated['slug'] ?? null,
                $validated['name'],
                $brandDetail->id
            );

            $thumbnail = $oldThumbnailPath;

            if ($request->boolean('delete_thumbnail')) {
                $thumbnail = null;
                $thumbnailToDelete =
                    $oldThumbnailPath;
            }

            if ($request->hasFile('thumbnail')) {
                $newThumbnailPath = $request
                    ->file('thumbnail')
                    ->store(
                        'brands',
                        'public'
                    );

                $thumbnail = $newThumbnailPath;
                $thumbnailToDelete =
                    $oldThumbnailPath;
            }

            DB::table('brands')
                ->where('id', $brandDetail->id)
                ->update([
                    'name' => trim($validated['name']),

                    'slug' => $slug,

                    'thumbnail' => $thumbnail,

                    'country' => $this->nullableTrim(
                        $validated['country'] ?? null
                    ),

                    'website' => $this->nullableTrim(
                        $validated['website'] ?? null
                    ),

                    'description' =>
                        $validated['description'] ?? null,

                    'sort_order' => (int) (
                        $validated['sort_order'] ?? 0
                    ),

                    'status' => (int) (
                        $validated['status'] ?? 1
                    ),

                    'updated_at' => now(),
                ]);

            DB::commit();

            if (
                $thumbnailToDelete
                && $thumbnailToDelete !==
                    $newThumbnailPath
            ) {
                Storage::disk('public')
                    ->delete($thumbnailToDelete);
            }

            return redirect()
                ->route(
                    'admin.brands.show',
                    $brandDetail->id
                )
                ->with(
                    'success',
                    'Cập nhật thương hiệu thành công.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            if ($newThumbnailPath) {
                Storage::disk('public')
                    ->delete($newThumbnailPath);
            }

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
                        : 'Không thể cập nhật thương hiệu.'
                );
        }
    }

    /**
     * Xóa mềm thương hiệu.
     */
    public function destroy(
        int $brand
    ): RedirectResponse {
        $brandDetail = DB::table('brands')
            ->where('id', $brand)
            ->whereNull('deleted_at')
            ->first([
                'id',
                'name',
            ]);

        abort_if(! $brandDetail, 404);

        $hasProducts = DB::table('products')
            ->where(
                'brand_id',
                $brandDetail->id
            )
            ->whereNull('deleted_at')
            ->exists();

        if ($hasProducts) {
            return back()->with(
                'error',
                'Không thể xóa thương hiệu này vì vẫn còn sản phẩm.'
            );
        }

        DB::beginTransaction();

        try {
            $now = now();

            DB::table('brands')
                ->where('id', $brandDetail->id)
                ->whereNull('deleted_at')
                ->update([
                    'status' => 0,
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::commit();

            return redirect()
                ->route('admin.brands.index')
                ->with(
                    'success',
                    'Đã xóa thương hiệu "'
                    . $brandDetail->name
                    . '".'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi xóa thương hiệu: '
                        . $exception->getMessage()
                    : 'Không thể xóa thương hiệu.'
            );
        }
    }

    /**
     * Tạo slug duy nhất.
     */
    private function makeUniqueSlug(
        ?string $requestedSlug,
        string $name,
        ?int $ignoreId = null
    ): string {
        $slug = trim((string) $requestedSlug);

        $baseSlug = Str::slug(
            $slug !== '' ? $slug : $name
        );

        if ($baseSlug === '') {
            $baseSlug = 'thuong-hieu';
        }

        $candidate = $baseSlug;
        $number = 1;

        while (
            DB::table('brands')
                ->where('slug', $candidate)
                ->when(
                    $ignoreId !== null,
                    fn (Builder $builder) =>
                        $builder->where(
                            'id',
                            '<>',
                            $ignoreId
                        )
                )
                ->exists()
        ) {
            $candidate =
                $baseSlug . '-' . $number;

            $number++;
        }

        return $candidate;
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
