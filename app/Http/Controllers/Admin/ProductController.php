<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm.
     */
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Query danh sách sản phẩm
        |--------------------------------------------------------------------------
        */

        $query = DB::table('products as p')
            ->leftJoin(
                'categories as c',
                'p.category_id',
                '=',
                'c.id'
            )
            ->leftJoin(
                'brands as b',
                'p.brand_id',
                '=',
                'b.id'
            )
            ->leftJoin(
                'suppliers as s',
                'p.supplier_id',
                '=',
                's.id'
            )
            ->whereNull('p.deleted_at')
            ->select([
                'p.id',
                'p.name',
                'p.slug',
                'p.short_description',
                'p.skin_type',
                'p.origin',
                'p.status',
                'p.is_featured',
                'p.view_count',
                'p.created_at',

                'c.name as category_name',
                'b.name as brand_name',
                's.name as supplier_name',
            ])
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('product_images')
                        ->select('image_path')
                        ->whereColumn(
                            'product_images.product_id',
                            'p.id'
                        )
                        ->orderByDesc('is_thumbnail')
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->limit(1);
                },
                'thumbnail'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('product_skus')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn(
                            'product_skus.product_id',
                            'p.id'
                        );
                },
                'sku_count'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('product_skus')
                        ->selectRaw('MIN(price)')
                        ->whereColumn(
                            'product_skus.product_id',
                            'p.id'
                        )
                        ->where('status', true);
                },
                'minimum_price'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('product_skus')
                        ->selectRaw('MAX(price)')
                        ->whereColumn(
                            'product_skus.product_id',
                            'p.id'
                        )
                        ->where('status', true);
                },
                'maximum_price'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('inventories as i')
                        ->join(
                            'product_skus as ps',
                            'i.sku_id',
                            '=',
                            'ps.id'
                        )
                        ->selectRaw(
                            'COALESCE(
                                SUM(
                                    i.quantity - i.reserved_quantity
                                ),
                                0
                            )'
                        )
                        ->whereColumn(
                            'ps.product_id',
                            'p.id'
                        );
                },
                'available_quantity'
            );

        /*
        |--------------------------------------------------------------------------
        | Tìm kiếm
        |--------------------------------------------------------------------------
        */

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
                            'p.slug',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhereExists(
                            function (Builder $skuQuery) use (
                                $keyword
                            ): void {
                                $skuQuery
                                    ->selectRaw('1')
                                    ->from(
                                        'product_skus as search_sku'
                                    )
                                    ->whereColumn(
                                        'search_sku.product_id',
                                        'p.id'
                                    )
                                    ->where(
                                        'search_sku.sku_code',
                                        'like',
                                        '%' . $keyword . '%'
                                    );
                            }
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lọc theo danh mục
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category_id')) {
            $query->where(
                'p.category_id',
                (int) $request->input('category_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lọc theo thương hiệu
        |--------------------------------------------------------------------------
        */

        if ($request->filled('brand_id')) {
            $query->where(
                'p.brand_id',
                (int) $request->input('brand_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lọc theo nhà cung cấp
        |--------------------------------------------------------------------------
        */

        if ($request->filled('supplier_id')) {
            $query->where(
                'p.supplier_id',
                (int) $request->input('supplier_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lọc trạng thái
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'p.status',
                $request->boolean('status')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lọc sản phẩm nổi bật
        |--------------------------------------------------------------------------
        */

        if ($request->filled('is_featured')) {
            $query->where(
                'p.is_featured',
                $request->boolean('is_featured')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sắp xếp
        |--------------------------------------------------------------------------
        */

        match ($request->input('sort')) {
            'oldest' => $query->orderBy('p.id'),

            'name_asc' => $query->orderBy('p.name'),

            'name_desc' => $query->orderByDesc('p.name'),

            'view_desc' => $query->orderByDesc('p.view_count'),

            'price_asc' => $query->orderBy('minimum_price'),

            'price_desc' => $query->orderByDesc('maximum_price'),

            default => $query->orderByDesc('p.id'),
        };

        /*
        |--------------------------------------------------------------------------
        | Phân trang
        |--------------------------------------------------------------------------
        */

        $products = $query
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Danh sách danh mục dùng cho bộ lọc
        |--------------------------------------------------------------------------
        */

        $categories = DB::table('categories')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Danh sách thương hiệu dùng cho bộ lọc
        |--------------------------------------------------------------------------
        */

        $brands = DB::table('brands')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Danh sách nhà cung cấp dùng cho bộ lọc
        |--------------------------------------------------------------------------
        */

        $suppliers = DB::table('suppliers')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Thống kê danh sách sản phẩm
        |--------------------------------------------------------------------------
        */

        $statistics = [
            'total' => DB::table('products')
                ->whereNull('deleted_at')
                ->count(),

            'active' => DB::table('products')
                ->whereNull('deleted_at')
                ->where('status', true)
                ->count(),

            'inactive' => DB::table('products')
                ->whereNull('deleted_at')
                ->where('status', false)
                ->count(),

            'featured' => DB::table('products')
                ->whereNull('deleted_at')
                ->where('is_featured', true)
                ->count(),

            'out_of_stock' => DB::table('products as p')
                ->whereNull('p.deleted_at')
                ->whereNotExists(
                    function (Builder $builder): void {
                        $builder
                            ->selectRaw('1')
                            ->from('product_skus as ps')
                            ->join(
                                'inventories as i',
                                'i.sku_id',
                                '=',
                                'ps.id'
                            )
                            ->whereColumn(
                                'ps.product_id',
                                'p.id'
                            )
                            ->whereRaw(
                                '(i.quantity - i.reserved_quantity) > 0'
                            );
                    }
                )
                ->count(),
        ];

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'suppliers' => $suppliers,
            'statistics' => $statistics,
        ]);
    }
/**
 * Hiển thị form thêm sản phẩm.
 */
public function create(): View
{
    $categories = DB::table('categories')
        ->whereNull('deleted_at')
        ->where('status', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get([
            'id',
            'parent_id',
            'name',
        ]);

    $brands = DB::table('brands')
        ->whereNull('deleted_at')
        ->where('status', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get([
            'id',
            'name',
        ]);

    $suppliers = DB::table('suppliers')
        ->whereNull('deleted_at')
        ->where('status', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get([
            'id',
            'name',
        ]);

    $warehouses = DB::table('warehouses')
        ->where('status', true)
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'address',
        ]);

    $variantAttributes = DB::table('variant_attributes')
        ->orderBy('name')
        ->get([
            'id',
            'name',
        ]);

    $variantValues = DB::table('variant_values as vv')
        ->join(
            'variant_attributes as va',
            'vv.attribute_id',
            '=',
            'va.id'
        )
        ->orderBy('va.name')
        ->orderBy('vv.value')
        ->get([
            'vv.id',
            'vv.attribute_id',
            'vv.value',
            'va.name as attribute_name',
        ])
        ->groupBy('attribute_id');

    return view('admin.products.create', [
        'categories' => $categories,
        'brands' => $brands,
        'suppliers' => $suppliers,
        'warehouses' => $warehouses,
        'variantAttributes' => $variantAttributes,
        'variantValues' => $variantValues,
    ]);
}
/**
 * Lưu sản phẩm mới.
 */
public function store(Request $request): RedirectResponse
{
    $validated = $request->validate(
        [
            /*
            |--------------------------------------------------------------------------
            | Thông tin sản phẩm
            |--------------------------------------------------------------------------
            */

            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')
                    ->whereNull('deleted_at'),
            ],

            'brand_id' => [
                'nullable',
                'integer',
                Rule::exists('brands', 'id')
                    ->whereNull('deleted_at'),
            ],

            'supplier_id' => [
                'nullable',
                'integer',
                Rule::exists('suppliers', 'id')
                    ->whereNull('deleted_at'),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:products,slug',
            ],

            'short_description' => [
                'nullable',
                'string',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'ingredient' => [
                'nullable',
                'string',
            ],

            'usage' => [
                'nullable',
                'string',
            ],

            'skin_type' => [
                'nullable',
                'string',
                'max:255',
            ],

            'origin' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],

            'is_featured' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Hình ảnh
            |--------------------------------------------------------------------------
            */

            'images' => [
                'nullable',
                'array',
                'max:20',
            ],

            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'thumbnail_index' => [
                'nullable',
                'integer',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Video
            |--------------------------------------------------------------------------
            */

            'videos' => [
                'nullable',
                'array',
            ],

            'videos.*.title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'videos.*.video_url' => [
                'nullable',
                'url',
                'max:2048',
                'required_without:videos.*.video_file',
            ],

            'videos.*.video_file' => [
                'nullable',
                'file',
                'mimes:mp4,webm,mov',
                'max:51200',
                'required_without:videos.*.video_url',
            ],

            'videos.*.type' => [
                'nullable',
                Rule::in([
                    'intro',
                    'tutorial',
                    'review',
                ]),
            ],

            'videos.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Biến thể
            |--------------------------------------------------------------------------
            */

            'variants' => [
                'nullable',
                'array',
            ],

            'variants.*.name' => [
                'required_with:variants',
                'string',
                'max:255',
            ],

            'variants.*.sku' => [
                'nullable',
                'string',
                'max:255',
                'distinct',
                'unique:product_variants,sku',
            ],

            'variants.*.price' => [
                'required_with:variants',
                'numeric',
                'min:0',
            ],

            'variants.*.compare_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'variants.*.weight' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'variants.*.status' => [
                'nullable',
                'boolean',
            ],

            'variants.*.value_ids' => [
                'nullable',
                'array',
            ],

            'variants.*.value_ids.*' => [
                'integer',
                'distinct',
                'exists:variant_values,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | SKU
            |--------------------------------------------------------------------------
            */

            'skus' => [
                'required',
                'array',
                'min:1',
            ],

            'skus.*.variant_index' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'skus.*.sku_code' => [
                'required',
                'string',
                'max:255',
                'distinct',
                'unique:product_skus,sku_code',
            ],

            'skus.*.barcode' => [
                'nullable',
                'string',
                'max:255',
                'distinct',
            ],

            'skus.*.price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'skus.*.cost_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'skus.*.weight' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'skus.*.status' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Tồn kho
            |--------------------------------------------------------------------------
            */

            'skus.*.inventories' => [
                'nullable',
                'array',
            ],

            'skus.*.inventories.*.warehouse_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('warehouses', 'id')
                    ->where('status', true),
            ],

            'skus.*.inventories.*.quantity' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'skus.*.inventories.*.minimum_stock' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ],
        [
            'category_id.required' => 'Vui lòng chọn danh mục.',

            'name.required' => 'Vui lòng nhập tên sản phẩm.',

            'slug.unique' => 'Slug sản phẩm đã tồn tại.',

            'images.*.image' => 'File tải lên phải là hình ảnh.',

            'images.*.mimes' =>
                'Ảnh phải có định dạng JPG, JPEG, PNG hoặc WEBP.',

            'images.*.max' =>
                'Mỗi hình ảnh không được lớn hơn 5 MB.',

            'videos.*.video_url.url' =>
                'URL video không đúng định dạng.',

            'videos.*.video_url.required_without' =>
                'Mỗi video phải có URL hoặc file tải lên.',

            'videos.*.video_file.required_without' =>
                'Mỗi video phải có URL hoặc file tải lên.',

            'videos.*.video_file.mimes' =>
                'Video phải có định dạng MP4, WEBM hoặc MOV.',

            'videos.*.video_file.max' =>
                'Mỗi video không được lớn hơn 50 MB.',

            'skus.required' =>
                'Sản phẩm phải có ít nhất một SKU.',

            'skus.min' =>
                'Sản phẩm phải có ít nhất một SKU.',

            'skus.*.sku_code.required' =>
                'Vui lòng nhập mã SKU.',

            'skus.*.sku_code.unique' =>
                'Mã SKU đã tồn tại.',

            'skus.*.sku_code.distinct' =>
                'Các mã SKU không được trùng nhau.',

            'skus.*.price.required' =>
                'Vui lòng nhập giá bán của SKU.',

            'variants.*.sku.unique' =>
                'Mã biến thể đã tồn tại.',
        ]
    );

    $storedFilePaths = [];

    DB::beginTransaction();

    try {
        /*
        |--------------------------------------------------------------------------
        | Tạo slug
        |--------------------------------------------------------------------------
        */

        $slug = trim((string) ($validated['slug'] ?? ''));

        if ($slug === '') {
            $slug = Str::slug($validated['name']);
        } else {
            $slug = Str::slug($slug);
        }

        $baseSlug = $slug;
        $slugNumber = 1;

        while (
            DB::table('products')
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $slugNumber;
            $slugNumber++;
        }

        /*
        |--------------------------------------------------------------------------
        | Lưu sản phẩm
        |--------------------------------------------------------------------------
        */

        $now = now();

        $productId = DB::table('products')->insertGetId([
            'category_id' => (int) $validated['category_id'],

            'brand_id' => ! empty($validated['brand_id'])
                ? (int) $validated['brand_id']
                : null,

            'supplier_id' => ! empty($validated['supplier_id'])
                ? (int) $validated['supplier_id']
                : null,

            'name' => trim($validated['name']),

            'slug' => $slug,

            'short_description' =>
                $validated['short_description'] ?? null,

            'description' =>
                $validated['description'] ?? null,

            'ingredient' =>
                $validated['ingredient'] ?? null,

            'usage' =>
                $validated['usage'] ?? null,

            'skin_type' =>
                $validated['skin_type'] ?? null,

            'origin' =>
                $validated['origin'] ?? null,

            'status' => $request->boolean('status'),

            'is_featured' =>
                $request->boolean('is_featured'),

            'view_count' => 0,

            'created_by' => auth()->id(),

            'updated_by' => auth()->id(),

            'created_at' => $now,

            'updated_at' => $now,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Lưu hình ảnh
        |--------------------------------------------------------------------------
        */

        $uploadedImages = $request->file('images', []);

        if (! empty($uploadedImages)) {
            $thumbnailIndex = (int) (
                $validated['thumbnail_index'] ?? 0
            );

            if (! array_key_exists(
                $thumbnailIndex,
                $uploadedImages
            )) {
                $thumbnailIndex = 0;
            }

            foreach (
                $uploadedImages as $imageIndex => $image
            ) {
                $imagePath = $image->store(
                    'products/' . $productId,
                    'public'
                );

               $storedFilePaths[] = $imagePath;

                DB::table('product_images')->insert([
                    'product_id' => $productId,

                    'image_path' => $imagePath,

                    'is_thumbnail' =>
                        $imageIndex === $thumbnailIndex,

                    'sort_order' => $imageIndex,

                    'created_at' => $now,

                    'updated_at' => $now,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Lưu video
        |--------------------------------------------------------------------------
        */

        /*
|--------------------------------------------------------------------------
| Lưu video
|--------------------------------------------------------------------------
*/

foreach (
    $validated['videos'] ?? [] as $videoIndex => $video
) {
    $videoUrl = trim(
        (string) ($video['video_url'] ?? '')
    );

    $videoFile = $request->file(
        'videos.' . $videoIndex . '.video_file'
    );

    /*
    |--------------------------------------------------------------------------
    | Ưu tiên file upload nếu người dùng chọn file
    |--------------------------------------------------------------------------
    */

    if ($videoFile) {
        $videoUrl = $videoFile->store(
            'products/' . $productId . '/videos',
            'public'
        );

        $storedFilePaths[] = $videoUrl;
    }

    /*
    |--------------------------------------------------------------------------
    | Không có URL và cũng không upload file thì bỏ qua
    |--------------------------------------------------------------------------
    */

    if ($videoUrl === '') {
        continue;
    }

    DB::table('product_videos')->insert([
        'product_id' => $productId,

        'title' => ! empty($video['title'])
            ? trim($video['title'])
            : null,

        'video_url' => $videoUrl,

        'type' => $video['type'] ?? 'intro',

        'sort_order' => (int) (
            $video['sort_order'] ?? $videoIndex
        ),

        'created_at' => $now,

        'updated_at' => $now,
    ]);
}

        /*
        |--------------------------------------------------------------------------
        | Lưu biến thể
        |--------------------------------------------------------------------------
        */

        $variantIdMap = [];

        foreach (
            $validated['variants'] ?? []
            as $variantIndex => $variant
        ) {
            $variantId = DB::table(
                'product_variants'
            )->insertGetId([
                'product_id' => $productId,

                'name' => trim($variant['name']),

                'sku' => ! empty($variant['sku'])
                    ? trim($variant['sku'])
                    : null,

                'price' => $variant['price'],

                'compare_price' =>
                    $variant['compare_price'] ?? null,

                'weight' =>
                    $variant['weight'] ?? null,

                'status' => array_key_exists(
                    'status',
                    $variant
                )
                    ? (bool) $variant['status']
                    : true,

                'created_at' => $now,

                'updated_at' => $now,
            ]);

            $variantIdMap[$variantIndex] = $variantId;

            foreach (
                array_unique(
                    $variant['value_ids'] ?? []
                ) as $valueId
            ) {
                DB::table(
                    'product_variant_values'
                )->insert([
                    'variant_id' => $variantId,

                    'value_id' => (int) $valueId,

                    'created_at' => $now,

                    'updated_at' => $now,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Lưu SKU và tồn kho
        |--------------------------------------------------------------------------
        */

        foreach ($validated['skus'] as $sku) {
            $variantId = null;

            if (
                isset($sku['variant_index'])
                && array_key_exists(
                    $sku['variant_index'],
                    $variantIdMap
                )
            ) {
                $variantId = $variantIdMap[
                    $sku['variant_index']
                ];
            }

            $skuId = DB::table(
                'product_skus'
            )->insertGetId([
                'product_id' => $productId,

                'variant_id' => $variantId,

                'sku_code' => trim($sku['sku_code']),

                'barcode' => ! empty($sku['barcode'])
                    ? trim($sku['barcode'])
                    : null,

                'price' => $sku['price'],

                'cost_price' =>
                    $sku['cost_price'] ?? null,

                'weight' =>
                    $sku['weight'] ?? null,

                'status' => array_key_exists(
                    'status',
                    $sku
                )
                    ? (bool) $sku['status']
                    : true,

                'created_at' => $now,

                'updated_at' => $now,
            ]);

            foreach (
                $sku['inventories'] ?? [] as $inventory
            ) {
                DB::table('inventories')->insert([
                    'warehouse_id' =>
                        (int) $inventory['warehouse_id'],

                    'sku_id' => $skuId,

                    'quantity' => (int) (
                        $inventory['quantity'] ?? 0
                    ),

                    'reserved_quantity' => 0,

                    'sold_quantity' => 0,

                    'minimum_stock' => (int) (
                        $inventory['minimum_stock'] ?? 10
                    ),

                    'created_at' => $now,

                    'updated_at' => $now,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Khởi tạo thống kê sản phẩm
        |--------------------------------------------------------------------------
        */

        DB::table('product_statistics')->insert([
    'product_id' => $productId,
    'views' => 0,
    'favorites' => 0,
    'orders' => 0,
    'sold_quantity' => 0,
    'revenue' => 0,
    'created_at' => $now,
    'updated_at' => $now,
]);

        DB::commit();

        return redirect()
            ->route('admin.products.show', $productId)
            ->with(
                'success',
                'Thêm sản phẩm thành công.'
            );
    } catch (Throwable $exception) {
        DB::rollBack();

        foreach ($storedFilePaths as $filePath) {
            Storage::disk('public')->delete($filePath);
        }

        report($exception);

        return back()
            ->withInput()
            ->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi thêm sản phẩm: ' . $exception->getMessage()
                    : 'Không thể thêm sản phẩm. Vui lòng kiểm tra lại dữ liệu.'
            );
    }
}
/**
 * Hiển thị form chỉnh sửa sản phẩm.
 */
public function edit(int $product): View
{
    /*
    |--------------------------------------------------------------------------
    | Sản phẩm
    |--------------------------------------------------------------------------
    */

    $productDetail = DB::table('products')
        ->where('id', $product)
        ->whereNull('deleted_at')
        ->first([
            'id',
            'category_id',
            'brand_id',
            'supplier_id',
            'name',
            'slug',
            'short_description',
            'description',
            'ingredient',
            'usage',
            'skin_type',
            'origin',
            'status',
            'is_featured',
            'view_count',
            'created_at',
            'updated_at',
        ]);

    abort_if(! $productDetail, 404);

    /*
    |--------------------------------------------------------------------------
    | Danh mục
    |--------------------------------------------------------------------------
    */

    $categories = DB::table('categories')
        ->whereNull('deleted_at')
        ->where('status', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get([
            'id',
            'parent_id',
            'name',
        ]);

    /*
    |--------------------------------------------------------------------------
    | Thương hiệu
    |--------------------------------------------------------------------------
    */

    $brands = DB::table('brands')
        ->whereNull('deleted_at')
        ->where('status', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get([
            'id',
            'name',
        ]);

    /*
    |--------------------------------------------------------------------------
    | Nhà cung cấp
    |--------------------------------------------------------------------------
    */

    $suppliers = DB::table('suppliers')
        ->whereNull('deleted_at')
        ->where('status', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get([
            'id',
            'name',
        ]);

    /*
    |--------------------------------------------------------------------------
    | Kho hàng
    |--------------------------------------------------------------------------
    */

    $warehouses = DB::table('warehouses')
        ->where('status', true)
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'address',
        ]);

    /*
    |--------------------------------------------------------------------------
    | Thuộc tính biến thể
    |--------------------------------------------------------------------------
    */

    $variantAttributes = DB::table('variant_attributes')
        ->orderBy('name')
        ->get([
            'id',
            'name',
        ]);

    $variantValues = DB::table('variant_values as vv')
        ->join(
            'variant_attributes as va',
            'vv.attribute_id',
            '=',
            'va.id'
        )
        ->orderBy('va.name')
        ->orderBy('vv.value')
        ->get([
            'vv.id',
            'vv.attribute_id',
            'vv.value',
            'va.name as attribute_name',
        ])
        ->groupBy('attribute_id');

    /*
    |--------------------------------------------------------------------------
    | Hình ảnh
    |--------------------------------------------------------------------------
    */

    $images = DB::table('product_images')
        ->where('product_id', $productDetail->id)
        ->orderByDesc('is_thumbnail')
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get([
            'id',
            'product_id',
            'image_path',
            'is_thumbnail',
            'sort_order',
            'created_at',
            'updated_at',
        ]);

    /*
    |--------------------------------------------------------------------------
    | Video
    |--------------------------------------------------------------------------
    */

    $videos = DB::table('product_videos')
        ->where('product_id', $productDetail->id)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get([
            'id',
            'product_id',
            'title',
            'video_url',
            'type',
            'sort_order',
            'created_at',
            'updated_at',
        ]);

    /*
    |--------------------------------------------------------------------------
    | Biến thể
    |--------------------------------------------------------------------------
    */

    $variants = DB::table('product_variants')
        ->where('product_id', $productDetail->id)
        ->orderBy('id')
        ->get([
            'id',
            'product_id',
            'name',
            'sku',
            'price',
            'compare_price',
            'weight',
            'status',
            'created_at',
            'updated_at',
        ])
        ->map(function ($variant) {
            $variant->value_ids = DB::table(
                'product_variant_values'
            )
                ->where('variant_id', $variant->id)
                ->pluck('value_id')
                ->map(fn ($valueId) => (int) $valueId)
                ->values()
                ->all();

            return $variant;
        });

    /*
    |--------------------------------------------------------------------------
    | SKU
    |--------------------------------------------------------------------------
    */

    $skus = DB::table('product_skus')
        ->where('product_id', $productDetail->id)
        ->orderBy('id')
        ->get([
            'id',
            'product_id',
            'variant_id',
            'sku_code',
            'barcode',
            'price',
            'cost_price',
            'weight',
            'status',
            'created_at',
            'updated_at',
        ])
        ->map(function ($sku) use ($warehouses) {
            $inventoryByWarehouse = DB::table('inventories')
                ->where('sku_id', $sku->id)
                ->get([
                    'id',
                    'warehouse_id',
                    'sku_id',
                    'quantity',
                    'reserved_quantity',
                    'sold_quantity',
                    'minimum_stock',
                    'created_at',
                    'updated_at',
                ])
                ->keyBy('warehouse_id');

            $sku->inventories = $warehouses
                ->map(function ($warehouse) use (
                    $inventoryByWarehouse
                ) {
                    $inventory = $inventoryByWarehouse->get(
                        $warehouse->id
                    );

                    return (object) [
                        'id' => $inventory->id ?? null,

                        'warehouse_id' => $warehouse->id,

                        'warehouse_name' => $warehouse->name,

                        'warehouse_address' =>
                            $warehouse->address,

                        'quantity' => (int) (
                            $inventory->quantity ?? 0
                        ),

                        'reserved_quantity' => (int) (
                            $inventory->reserved_quantity ?? 0
                        ),

                        'sold_quantity' => (int) (
                            $inventory->sold_quantity ?? 0
                        ),

                        'minimum_stock' => (int) (
                            $inventory->minimum_stock ?? 10
                        ),
                    ];
                })
                ->values();

            return $sku;
        });

    return view('admin.products.edit', [
        'product' => $productDetail,
        'categories' => $categories,
        'brands' => $brands,
        'suppliers' => $suppliers,
        'warehouses' => $warehouses,
        'variantAttributes' => $variantAttributes,
        'variantValues' => $variantValues,
        'images' => $images,
        'videos' => $videos,
        'variants' => $variants,
        'skus' => $skus,
    ]);
}

/**
 * Cập nhật sản phẩm.
 */
public function update(
    Request $request,
    int $product
): RedirectResponse {
    $productDetail = DB::table('products')
        ->where('id', $product)
        ->whereNull('deleted_at')
        ->first();

    abort_if(! $productDetail, 404);

    $validated = $request->validate(
        [
            /*
            |--------------------------------------------------------------------------
            | Sản phẩm
            |--------------------------------------------------------------------------
            */

            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')
                    ->whereNull('deleted_at'),
            ],

            'brand_id' => [
                'nullable',
                'integer',
                Rule::exists('brands', 'id')
                    ->whereNull('deleted_at'),
            ],

            'supplier_id' => [
                'nullable',
                'integer',
                Rule::exists('suppliers', 'id')
                    ->whereNull('deleted_at'),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')
                    ->ignore($productDetail->id),
            ],

            'short_description' => [
                'nullable',
                'string',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'ingredient' => [
                'nullable',
                'string',
            ],

            'usage' => [
                'nullable',
                'string',
            ],

            'skin_type' => [
                'nullable',
                'string',
                'max:255',
            ],

            'origin' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],

            'is_featured' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Ảnh
            |--------------------------------------------------------------------------
            */

            'images' => [
                'nullable',
                'array',
                'max:20',
            ],

            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'thumbnail_index' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'existing_thumbnail_id' => [
                'nullable',
                'integer',
            ],

            'delete_image_ids' => [
                'nullable',
                'array',
            ],

            'delete_image_ids.*' => [
                'integer',
                'distinct',
            ],

            /*
            |--------------------------------------------------------------------------
            | Video
            |--------------------------------------------------------------------------
            */

            'videos' => [
                'nullable',
                'array',
            ],

            'videos.*.id' => [
                'nullable',
                'integer',
            ],

            'videos.*.title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'videos.*.video_url' => [
                'nullable',
                'url',
                'max:2048',
                'required_without:videos.*.video_file',
            ],

            'videos.*.video_file' => [
                'nullable',
                'file',
                'mimes:mp4,webm,mov',
                'max:51200',
                'required_without:videos.*.video_url',
            ],

            'videos.*.type' => [
                'nullable',
                Rule::in([
                    'intro',
                    'tutorial',
                    'review',
                ]),
            ],

            'videos.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Variant
            |--------------------------------------------------------------------------
            */

            'variants' => [
                'nullable',
                'array',
            ],

            'variants.*.id' => [
                'nullable',
                'integer',
            ],

            'variants.*.name' => [
                'required_with:variants',
                'string',
                'max:255',
            ],

            'variants.*.sku' => [
                'nullable',
                'string',
                'max:255',
                'distinct',
            ],

            'variants.*.price' => [
                'required_with:variants',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],

            'variants.*.compare_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],

            'variants.*.weight' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'variants.*.status' => [
                'nullable',
                'boolean',
            ],

            'variants.*.value_ids' => [
                'nullable',
                'array',
            ],

            'variants.*.value_ids.*' => [
                'integer',
                'distinct',
                'exists:variant_values,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | SKU
            |--------------------------------------------------------------------------
            */

            'skus' => [
                'required',
                'array',
                'min:1',
            ],

            'skus.*.id' => [
                'nullable',
                'integer',
            ],

            'skus.*.variant_index' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'skus.*.sku_code' => [
                'required',
                'string',
                'max:255',
                'distinct',
            ],

            'skus.*.barcode' => [
                'nullable',
                'string',
                'max:255',
                'distinct',
            ],

            'skus.*.price' => [
                'required',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],

            'skus.*.cost_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],

            'skus.*.weight' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'skus.*.status' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Inventory
            |--------------------------------------------------------------------------
            */

            'skus.*.inventories' => [
                'nullable',
                'array',
            ],

            'skus.*.inventories.*.id' => [
                'nullable',
                'integer',
            ],

            'skus.*.inventories.*.warehouse_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('warehouses', 'id'),
            ],

            'skus.*.inventories.*.quantity' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'skus.*.inventories.*.minimum_stock' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ],
        [
            'name.required' =>
                'Vui lòng nhập tên sản phẩm.',

            'category_id.required' =>
                'Vui lòng chọn danh mục.',

            'slug.unique' =>
                'Slug sản phẩm đã tồn tại.',

            'videos.*.video_url.url' =>
                'URL video không đúng định dạng.',

            'videos.*.video_url.required_without' =>
                'Mỗi video phải có URL hoặc file tải lên.',

            'videos.*.video_file.required_without' =>
                'Mỗi video phải có URL hoặc file tải lên.',

            'videos.*.video_file.mimes' =>
                'Video phải có định dạng MP4, WEBM hoặc MOV.',

            'videos.*.video_file.max' =>
                'Mỗi video không được lớn hơn 50 MB.',

            'variants.*.price.max' =>
                'Giá biến thể không được vượt quá 99.999.999 đồng.',

            'skus.required' =>
                'Sản phẩm phải có ít nhất một SKU.',

            'skus.min' =>
                'Sản phẩm phải có ít nhất một SKU.',

            'skus.*.sku_code.required' =>
                'Vui lòng nhập mã SKU.',

            'skus.*.sku_code.distinct' =>
                'Các mã SKU trong form không được trùng nhau.',

            'skus.*.price.required' =>
                'Vui lòng nhập giá bán của SKU.',

            'skus.*.price.max' =>
                'Giá SKU không được vượt quá 99.999.999 đồng.',
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | Kiểm tra các ID gửi lên đúng sản phẩm hiện tại
    |--------------------------------------------------------------------------
    */

    $existingImageIds = DB::table('product_images')
        ->where('product_id', $productDetail->id)
        ->pluck('id')
        ->map(fn ($id) => (int) $id);

    $existingVideoIds = DB::table('product_videos')
        ->where('product_id', $productDetail->id)
        ->pluck('id')
        ->map(fn ($id) => (int) $id);

    $existingVariantIds = DB::table('product_variants')
        ->where('product_id', $productDetail->id)
        ->pluck('id')
        ->map(fn ($id) => (int) $id);

    $existingSkuIds = DB::table('product_skus')
        ->where('product_id', $productDetail->id)
        ->pluck('id')
        ->map(fn ($id) => (int) $id);

    $deleteImageIds = collect(
        $validated['delete_image_ids'] ?? []
    )
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    if ($deleteImageIds->diff($existingImageIds)->isNotEmpty()) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'delete_image_ids' =>
                'Có hình ảnh không thuộc sản phẩm hiện tại.',
        ]);
    }

    $existingThumbnailId = ! empty(
        $validated['existing_thumbnail_id']
    )
        ? (int) $validated['existing_thumbnail_id']
        : null;

    if (
        $existingThumbnailId
        && ! $existingImageIds->contains($existingThumbnailId)
    ) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'existing_thumbnail_id' =>
                'Ảnh đại diện không thuộc sản phẩm hiện tại.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Kiểm tra mã variant và SKU không trùng database
    |--------------------------------------------------------------------------
    */

    foreach ($validated['variants'] ?? [] as $variant) {
        $variantId = ! empty($variant['id'])
            ? (int) $variant['id']
            : null;

        if (
            $variantId
            && ! $existingVariantIds->contains($variantId)
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'variants' =>
                    'Có biến thể không thuộc sản phẩm hiện tại.',
            ]);
        }

        $variantSku = trim(
            (string) ($variant['sku'] ?? '')
        );

        if ($variantSku === '') {
            continue;
        }

        $duplicateVariantSku = DB::table('product_variants')
            ->where('sku', $variantSku)
            ->when(
                $variantId,
                fn ($query) => $query->where(
                    'id',
                    '<>',
                    $variantId
                )
            )
            ->exists();

        if ($duplicateVariantSku) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'variants' =>
                    'Mã biến thể "' . $variantSku . '" đã tồn tại.',
            ]);
        }
    }

    foreach ($validated['skus'] as $sku) {
        $skuId = ! empty($sku['id'])
            ? (int) $sku['id']
            : null;

        if ($skuId && ! $existingSkuIds->contains($skuId)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'skus' =>
                    'Có SKU không thuộc sản phẩm hiện tại.',
            ]);
        }

        $skuCode = trim($sku['sku_code']);

        $duplicateSku = DB::table('product_skus')
            ->where('sku_code', $skuCode)
            ->when(
                $skuId,
                fn ($query) => $query->where(
                    'id',
                    '<>',
                    $skuId
                )
            )
            ->exists();

        if ($duplicateSku) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'skus' =>
                    'Mã SKU "' . $skuCode . '" đã tồn tại.',
            ]);
        }
    }

    $storedFilePaths = [];
    $deletedImagePaths = [];
    $oldVideoFilesToDelete = [];

    DB::beginTransaction();

    try {
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Cập nhật sản phẩm
        |--------------------------------------------------------------------------
        */

        $slug = trim(
            (string) ($validated['slug'] ?? '')
        );

        if ($slug === '') {
            $slug = Str::slug($validated['name']);
        } else {
            $slug = Str::slug($slug);
        }

        DB::table('products')
            ->where('id', $productDetail->id)
            ->update([
                'category_id' =>
                    (int) $validated['category_id'],

                'brand_id' => ! empty($validated['brand_id'])
                    ? (int) $validated['brand_id']
                    : null,

                'supplier_id' => ! empty(
                    $validated['supplier_id']
                )
                    ? (int) $validated['supplier_id']
                    : null,

                'name' => trim($validated['name']),

                'slug' => $slug,

                'short_description' =>
                    $validated['short_description'] ?? null,

                'description' =>
                    $validated['description'] ?? null,

                'ingredient' =>
                    $validated['ingredient'] ?? null,

                'usage' =>
                    $validated['usage'] ?? null,

                'skin_type' =>
                    $validated['skin_type'] ?? null,

                'origin' =>
                    $validated['origin'] ?? null,

                'status' => $request->boolean('status'),

                'is_featured' =>
                    $request->boolean('is_featured'),

                'updated_by' => auth()->id(),

                'updated_at' => $now,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Xóa ảnh được chọn
        |--------------------------------------------------------------------------
        */

        if ($deleteImageIds->isNotEmpty()) {
            $deletedImages = DB::table('product_images')
                ->where('product_id', $productDetail->id)
                ->whereIn('id', $deleteImageIds)
                ->get([
                    'id',
                    'image_path',
                ]);

            $deletedImagePaths = $deletedImages
                ->pluck('image_path')
                ->filter()
                ->values()
                ->all();

            DB::table('product_images')
                ->where('product_id', $productDetail->id)
                ->whereIn('id', $deleteImageIds)
                ->delete();
        }

        /*
        |--------------------------------------------------------------------------
        | Upload ảnh mới
        |--------------------------------------------------------------------------
        */

        $newImageIds = [];

        foreach (
            $request->file('images', [])
            as $imageIndex => $image
        ) {
            $imagePath = $image->store(
                'products/' . $productDetail->id,
                'public'
            );

             $storedFilePaths[] = $imagePath;

            $imageId = DB::table('product_images')
                ->insertGetId([
                    'product_id' => $productDetail->id,
                    'image_path' => $imagePath,
                    'is_thumbnail' => false,
                    'sort_order' => DB::table('product_images')
                        ->where(
                            'product_id',
                            $productDetail->id
                        )
                        ->max('sort_order') + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $newImageIds[$imageIndex] = $imageId;
        }

        /*
        |--------------------------------------------------------------------------
        | Đặt ảnh đại diện
        |--------------------------------------------------------------------------
        */

        $newThumbnailIndex = $validated[
            'thumbnail_index'
        ] ?? null;

        $selectedThumbnailId = null;

        if (
            $newThumbnailIndex !== null
            && array_key_exists(
                (int) $newThumbnailIndex,
                $newImageIds
            )
        ) {
            $selectedThumbnailId = $newImageIds[
                (int) $newThumbnailIndex
            ];
        } elseif (
            $existingThumbnailId
            && ! $deleteImageIds->contains(
                $existingThumbnailId
            )
        ) {
            $selectedThumbnailId = $existingThumbnailId;
        }

        if (! $selectedThumbnailId) {
            $selectedThumbnailId = DB::table('product_images')
                ->where('product_id', $productDetail->id)
                ->orderByDesc('is_thumbnail')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->value('id');
        }

        DB::table('product_images')
            ->where('product_id', $productDetail->id)
            ->update([
                'is_thumbnail' => false,
                'updated_at' => $now,
            ]);

        if ($selectedThumbnailId) {
            DB::table('product_images')
                ->where('product_id', $productDetail->id)
                ->where('id', $selectedThumbnailId)
                ->update([
                    'is_thumbnail' => true,
                    'updated_at' => $now,
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Đồng bộ video
        |--------------------------------------------------------------------------
        */

        $submittedVideoIds = [];

        foreach (
            $validated['videos'] ?? []
            as $videoIndex => $video
        ) {
            $videoId = ! empty($video['id'])
                ? (int) $video['id']
                : null;

            $videoUrl = trim(
                (string) ($video['video_url'] ?? '')
            );

            $videoFile = $request->file(
                'videos.' . $videoIndex . '.video_file'
            );

            $oldVideo = null;

            if ($videoId) {
                if (! $existingVideoIds->contains($videoId)) {
                    throw new \RuntimeException(
                        'Video không thuộc sản phẩm hiện tại.'
                    );
                }

                $oldVideo = DB::table('product_videos')
                    ->where('id', $videoId)
                    ->where('product_id', $productDetail->id)
                    ->first();
            }

            if ($videoFile) {
                $newVideoPath = $videoFile->store(
                    'products/' . $productDetail->id . '/videos',
                    'public'
                );

                $storedFilePaths[] = $newVideoPath;
                $videoUrl = $newVideoPath;

                if (
                    $oldVideo
                    && $oldVideo->video_url
                    && ! Str::startsWith(
                        $oldVideo->video_url,
                        ['http://', 'https://']
                    )
                ) {
                    $oldVideoFilesToDelete[] = $oldVideo->video_url;
                }
            }

            if ($videoUrl === '') {
                continue;
            }

            $videoData = [
                'product_id' => $productDetail->id,

                'title' => ! empty($video['title'])
                    ? trim($video['title'])
                    : null,

                'video_url' => $videoUrl,

                'type' => $video['type'] ?? 'intro',

                'sort_order' => (int) (
                    $video['sort_order'] ?? $videoIndex
                ),

                'updated_at' => $now,
            ];

            if ($videoId) {
                DB::table('product_videos')
                    ->where('id', $videoId)
                    ->where('product_id', $productDetail->id)
                    ->update($videoData);

                $submittedVideoIds[] = $videoId;
            } else {
                $videoData['created_at'] = $now;

                $submittedVideoIds[] = DB::table(
                    'product_videos'
                )->insertGetId($videoData);
            }
        }

        $removedVideosQuery = DB::table('product_videos')
            ->where('product_id', $productDetail->id);

        if (! empty($submittedVideoIds)) {
            $removedVideosQuery->whereNotIn(
                'id',
                $submittedVideoIds
            );
        }

        $removedVideos = $removedVideosQuery->get([
            'id',
            'video_url',
        ]);

        foreach ($removedVideos as $removedVideo) {
            if (
                $removedVideo->video_url
                && ! Str::startsWith(
                    $removedVideo->video_url,
                    ['http://', 'https://']
                )
            ) {
                $oldVideoFilesToDelete[] =
                    $removedVideo->video_url;
            }
        }

        $removedVideoIds = $removedVideos->pluck('id');

        if ($removedVideoIds->isNotEmpty()) {
            DB::table('product_videos')
                ->whereIn('id', $removedVideoIds)
                ->delete();
        }

        /*
        |--------------------------------------------------------------------------
        | Đồng bộ variant
        |--------------------------------------------------------------------------
        */

        $variantIdMap = [];
        $submittedVariantIds = [];

        foreach (
            $validated['variants'] ?? []
            as $variantIndex => $variant
        ) {
            $variantId = ! empty($variant['id'])
                ? (int) $variant['id']
                : null;

            $variantData = [
                'product_id' => $productDetail->id,

                'name' => trim($variant['name']),

                'sku' => ! empty($variant['sku'])
                    ? trim($variant['sku'])
                    : null,

                'price' => $variant['price'],

                'compare_price' =>
                    $variant['compare_price'] ?? null,

                'weight' =>
                    $variant['weight'] ?? null,

                'status' => array_key_exists(
                    'status',
                    $variant
                )
                    ? (bool) $variant['status']
                    : true,

                'updated_at' => $now,
            ];

            if ($variantId) {
                DB::table('product_variants')
                    ->where('id', $variantId)
                    ->where(
                        'product_id',
                        $productDetail->id
                    )
                    ->update($variantData);
            } else {
                $variantData['created_at'] = $now;

                $variantId = DB::table(
                    'product_variants'
                )->insertGetId($variantData);
            }

            $variantIdMap[$variantIndex] = $variantId;
            $submittedVariantIds[] = $variantId;

            DB::table('product_variant_values')
                ->where('variant_id', $variantId)
                ->delete();

            foreach (
                array_unique(
                    $variant['value_ids'] ?? []
                ) as $valueId
            ) {
                DB::table('product_variant_values')
                    ->insert([
                        'variant_id' => $variantId,
                        'value_id' => (int) $valueId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Đồng bộ SKU và inventory
        |--------------------------------------------------------------------------
        */

        $submittedSkuIds = [];

        foreach ($validated['skus'] as $sku) {
            $skuId = ! empty($sku['id'])
                ? (int) $sku['id']
                : null;

            $variantId = null;

            if (
                isset($sku['variant_index'])
                && array_key_exists(
                    (int) $sku['variant_index'],
                    $variantIdMap
                )
            ) {
                $variantId = $variantIdMap[
                    (int) $sku['variant_index']
                ];
            }

            $skuData = [
                'product_id' => $productDetail->id,
                'variant_id' => $variantId,
                'sku_code' => trim($sku['sku_code']),

                'barcode' => ! empty($sku['barcode'])
                    ? trim($sku['barcode'])
                    : null,

                'price' => $sku['price'],

                'cost_price' =>
                    $sku['cost_price'] ?? null,

                'weight' => $sku['weight'] ?? null,

                'status' => array_key_exists(
                    'status',
                    $sku
                )
                    ? (bool) $sku['status']
                    : true,

                'updated_at' => $now,
            ];

            if ($skuId) {
                DB::table('product_skus')
                    ->where('id', $skuId)
                    ->where(
                        'product_id',
                        $productDetail->id
                    )
                    ->update($skuData);
            } else {
                $skuData['created_at'] = $now;

                $skuId = DB::table('product_skus')
                    ->insertGetId($skuData);
            }

            $submittedSkuIds[] = $skuId;

            foreach (
                $sku['inventories'] ?? []
                as $inventory
            ) {
                $warehouseId = (int) (
                    $inventory['warehouse_id']
                );

                $newQuantity = (int) (
                    $inventory['quantity'] ?? 0
                );

                $minimumStock = (int) (
                    $inventory['minimum_stock'] ?? 10
                );

                $existingInventory = DB::table(
                    'inventories'
                )
                    ->where('sku_id', $skuId)
                    ->where(
                        'warehouse_id',
                        $warehouseId
                    )
                    ->first();

                if ($existingInventory) {
                    $oldQuantity = (int) (
                        $existingInventory->quantity
                    );

                    DB::table('inventories')
                        ->where('id', $existingInventory->id)
                        ->update([
                            'quantity' => $newQuantity,

                            'minimum_stock' =>
                                $minimumStock,

                            'updated_at' => $now,
                        ]);

                    $quantityDifference =
                        $newQuantity - $oldQuantity;

                    if ($quantityDifference !== 0) {
                        DB::table(
                            'inventory_transactions'
                        )->insert([
                            'warehouse_id' =>
                                $warehouseId,

                            'sku_id' => $skuId,

                            'type' => 'adjust',

                            'quantity' =>
                                $quantityDifference,

                            'reference_type' =>
                                'product_update',

                            'reference_id' =>
                                $productDetail->id,

                            'note' =>
                                'Điều chỉnh tồn kho khi cập nhật sản phẩm.',

                            'created_by' =>
                                auth()->id(),

                            'created_at' => $now,

                            'updated_at' => $now,
                        ]);
                    }
                } else {
                    DB::table('inventories')->insert([
                        'warehouse_id' => $warehouseId,
                        'sku_id' => $skuId,
                        'quantity' => $newQuantity,
                        'reserved_quantity' => 0,
                        'sold_quantity' => 0,
                        'minimum_stock' => $minimumStock,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    if ($newQuantity !== 0) {
                        DB::table(
                            'inventory_transactions'
                        )->insert([
                            'warehouse_id' =>
                                $warehouseId,

                            'sku_id' => $skuId,

                            'type' => 'adjust',

                            'quantity' => $newQuantity,

                            'reference_type' =>
                                'product_update',

                            'reference_id' =>
                                $productDetail->id,

                            'note' =>
                                'Khởi tạo tồn kho khi cập nhật sản phẩm.',

                            'created_by' =>
                                auth()->id(),

                            'created_at' => $now,

                            'updated_at' => $now,
                        ]);
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SKU bị bỏ khỏi form: ngừng bán thay vì xóa cứng
        |--------------------------------------------------------------------------
        |
        | SKU có thể đã tồn tại trong inventory_transactions và đơn hàng.
        | Vô hiệu hóa giúp giữ nguyên lịch sử dữ liệu.
        |
        */

        DB::table('product_skus')
            ->where('product_id', $productDetail->id)
            ->when(
                ! empty($submittedSkuIds),
                fn ($query) => $query->whereNotIn(
                    'id',
                    $submittedSkuIds
                )
            )
            ->update([
                'status' => false,
                'variant_id' => null,
                'updated_at' => $now,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Xóa variant đã bỏ khỏi form
        |--------------------------------------------------------------------------
        */

        $variantsToDelete = DB::table(
            'product_variants'
        )
            ->where('product_id', $productDetail->id)
            ->when(
                ! empty($submittedVariantIds),
                fn ($query) => $query->whereNotIn(
                    'id',
                    $submittedVariantIds
                )
            )
            ->pluck('id');

        if ($variantsToDelete->isNotEmpty()) {
            DB::table('product_variant_values')
                ->whereIn(
                    'variant_id',
                    $variantsToDelete
                )
                ->delete();

            DB::table('product_variants')
                ->whereIn('id', $variantsToDelete)
                ->delete();
        }

        DB::commit();

        /*
        |--------------------------------------------------------------------------
        | Chỉ xóa file ảnh cũ sau khi transaction thành công
        |--------------------------------------------------------------------------
        */

        foreach ($deletedImagePaths as $imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        foreach (
            array_unique($oldVideoFilesToDelete)
            as $videoPath
        ) {
            Storage::disk('public')->delete($videoPath);
        }

        return redirect()
            ->route(
                'admin.products.show',
                $productDetail->id
            )
            ->with(
                'success',
                'Cập nhật sản phẩm thành công.'
            );
    } catch (Throwable $exception) {
        DB::rollBack();

        /*
        |--------------------------------------------------------------------------
        | Xóa file ảnh mới nếu database rollback
        |--------------------------------------------------------------------------
        */

        foreach ($storedFilePaths as $filePath) {
            Storage::disk('public')->delete($filePath);
        }

        report($exception);

        return back()
            ->withInput()
            ->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi: ' . $exception->getMessage()
                    : 'Không thể cập nhật sản phẩm.'
            );
    }
}

/**
 * Xóa mềm sản phẩm.
 */
public function destroy(int $product): RedirectResponse
{
    $productDetail = DB::table('products')
        ->where('id', $product)
        ->whereNull('deleted_at')
        ->first([
            'id',
            'name',
            'slug',
            'status',
            'is_featured',
            'category_id',
            'brand_id',
            'supplier_id',
            'created_at',
            'updated_at',
        ]);

    abort_if(! $productDetail, 404);

    /*
    |--------------------------------------------------------------------------
    | Không cho xóa sản phẩm đã phát sinh đơn hàng
    |--------------------------------------------------------------------------
    */

    $hasOrder = DB::table('order_items')
        ->where('product_id', $productDetail->id)
        ->exists();

    if ($hasOrder) {
        return back()->with(
            'error',
            'Không thể xóa sản phẩm này vì sản phẩm đã phát sinh đơn hàng. '
            . 'Bạn có thể chuyển sản phẩm sang trạng thái ngừng bán.'
        );
    }

    DB::beginTransaction();

    try {
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Xóa mềm sản phẩm
        |--------------------------------------------------------------------------
        */

        DB::table('products')
            ->where('id', $productDetail->id)
            ->whereNull('deleted_at')
            ->update([
                'status' => false,
                'is_featured' => false,
                'updated_by' => auth()->id(),
                'updated_at' => $now,
                'deleted_at' => $now,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Tắt toàn bộ SKU
        |--------------------------------------------------------------------------
        */

        DB::table('product_skus')
            ->where('product_id', $productDetail->id)
            ->update([
                'status' => false,
                'updated_at' => $now,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Tắt toàn bộ biến thể
        |--------------------------------------------------------------------------
        */

        DB::table('product_variants')
            ->where('product_id', $productDetail->id)
            ->update([
                'status' => false,
                'updated_at' => $now,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Ghi nhật ký quản trị
        |--------------------------------------------------------------------------
        |
        | Migration audit_logs thật dùng:
        | auditable_type, auditable_id, route_name, url, request_method.
        |
        */

        DB::table('audit_logs')->insert([
            'user_id' => auth()->id(),

            'action' => 'deleted',

            'auditable_type' => \App\Models\Product::class,

            'auditable_id' => $productDetail->id,

            'description' =>
                'Xóa mềm sản phẩm: ' . $productDetail->name,

            'old_values' => json_encode([
                'id' => $productDetail->id,
                'name' => $productDetail->name,
                'slug' => $productDetail->slug,
                'status' => (bool) $productDetail->status,
                'is_featured' =>
                    (bool) $productDetail->is_featured,
                'deleted_at' => null,
            ], JSON_UNESCAPED_UNICODE),

            'new_values' => json_encode([
                'status' => false,
                'is_featured' => false,
                'deleted_at' => $now->toDateTimeString(),
            ], JSON_UNESCAPED_UNICODE),

            'route_name' => request()->route()?->getName(),

            'url' => request()->fullUrl(),

            'request_method' => request()->method(),

            'ip_address' => request()->ip(),

            'user_agent' => request()->userAgent(),

            'created_at' => $now,

            'updated_at' => $now,
        ]);

        DB::commit();

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                'Đã xóa sản phẩm "' . $productDetail->name . '".'
            );
    } catch (Throwable $exception) {
        DB::rollBack();

        report($exception);

        return back()->with(
            'error',
            app()->isLocal()
                ? 'Lỗi xóa sản phẩm: ' . $exception->getMessage()
                : 'Không thể xóa sản phẩm.'
        );
    }
}
    /**
     * Hiển thị chi tiết sản phẩm.
     */
    public function show(int $product): View
    {
        /*
        |--------------------------------------------------------------------------
        | Thông tin chính của sản phẩm
        |--------------------------------------------------------------------------
        */

        $productDetail = DB::table('products as p')
            ->leftJoin(
                'categories as c',
                'p.category_id',
                '=',
                'c.id'
            )
            ->leftJoin(
                'brands as b',
                'p.brand_id',
                '=',
                'b.id'
            )
            ->leftJoin(
                'suppliers as s',
                'p.supplier_id',
                '=',
                's.id'
            )
            ->where('p.id', $product)
            ->whereNull('p.deleted_at')
            ->select([
                'p.id',
                'p.category_id',
                'p.brand_id',
                'p.supplier_id',
                'p.created_by',
                'p.updated_by',

                'p.name',
                'p.slug',
                'p.short_description',
                'p.description',
                'p.ingredient',
                'p.usage',
                'p.skin_type',
                'p.origin',

                'p.status',
                'p.is_featured',
                'p.view_count',

                'p.created_at',
                'p.updated_at',

                'c.name as category_name',
                'c.slug as category_slug',

                'b.name as brand_name',
                'b.slug as brand_slug',
                'b.country as brand_country',

                's.name as supplier_name',
                's.phone as supplier_phone',
                's.email as supplier_email',
            ])
            ->first();

        abort_if(! $productDetail, 404);

        /*
        |--------------------------------------------------------------------------
        | Hình ảnh sản phẩm
        |--------------------------------------------------------------------------
        */

        $images = DB::table('product_images')
            ->where('product_id', $productDetail->id)
            ->orderByDesc('is_thumbnail')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'product_id',
                'image_path',
                'is_thumbnail',
                'sort_order',
                'created_at',
                'updated_at',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Video sản phẩm
        |--------------------------------------------------------------------------
        |
        | Bảng product_videos thật có:
        | id, product_id, title, video_url, type, sort_order,
        | created_at, updated_at.
        |
        */

        $videos = DB::table('product_videos')
            ->where('product_id', $productDetail->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'product_id',
                'title',
                'video_url',
                'type',
                'sort_order',
                'created_at',
                'updated_at',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Biến thể sản phẩm
        |--------------------------------------------------------------------------
        |
        | Bảng product_variants không có cột sort_order.
        |
        */

        $variants = DB::table('product_variants')
            ->where('product_id', $productDetail->id)
            ->orderByDesc('status')
            ->orderBy('id')
            ->get([
                'id',
                'product_id',
                'name',
                'sku',
                'price',
                'compare_price',
                'weight',
                'status',
                'created_at',
                'updated_at',
            ]);

        /*
        |--------------------------------------------------------------------------
        | SKU, giá bán và tổng tồn kho
        |--------------------------------------------------------------------------
        */

        $skus = DB::table('product_skus as ps')
            ->leftJoin(
                'product_variants as pv',
                'ps.variant_id',
                '=',
                'pv.id'
            )
            ->where('ps.product_id', $productDetail->id)
            ->select([
                'ps.id',
                'ps.product_id',
                'ps.variant_id',
                'ps.sku_code',
                'ps.barcode',
                'ps.price',
                'ps.cost_price',
                'ps.weight',
                'ps.status',
                'ps.created_at',
                'ps.updated_at',

                'pv.name as variant_name',
                'pv.sku as variant_sku',
                'pv.price as variant_price',
                'pv.compare_price',
                'pv.weight as variant_weight',
                'pv.status as variant_status',
            ])
            ->selectSub(
                function (Builder $query): void {
                    $query
                        ->from('inventories')
                        ->selectRaw(
                            'COALESCE(SUM(quantity), 0)'
                        )
                        ->whereColumn(
                            'inventories.sku_id',
                            'ps.id'
                        );
                },
                'total_quantity'
            )
            ->selectSub(
                function (Builder $query): void {
                    $query
                        ->from('inventories')
                        ->selectRaw(
                            'COALESCE(SUM(reserved_quantity), 0)'
                        )
                        ->whereColumn(
                            'inventories.sku_id',
                            'ps.id'
                        );
                },
                'reserved_quantity'
            )
            ->selectSub(
                function (Builder $query): void {
                    $query
                        ->from('inventories')
                        ->selectRaw(
                            'COALESCE(SUM(sold_quantity), 0)'
                        )
                        ->whereColumn(
                            'inventories.sku_id',
                            'ps.id'
                        );
                },
                'sold_quantity'
            )
            ->selectSub(
                function (Builder $query): void {
                    $query
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
                            'inventories.sku_id',
                            'ps.id'
                        );
                },
                'available_quantity'
            )
            ->orderByDesc('ps.status')
            ->orderBy('pv.name')
            ->orderBy('ps.id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Tồn kho theo từng kho hàng
        |--------------------------------------------------------------------------
        |
        | Bảng inventories thật có:
        | quantity, reserved_quantity, sold_quantity, minimum_stock.
        |
        */

        $inventories = DB::table('inventories as i')
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
    ->leftJoin(
        'product_variants as pv',
        'ps.variant_id',
        '=',
        'pv.id'
    )
    ->where('ps.product_id', $productDetail->id)
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
        'ps.price as sku_price',
        'ps.status as sku_status',

        'pv.name as variant_name',
    ])
    ->orderBy('w.name')
    ->orderBy('ps.sku_code')
    ->get()
    ->map(function ($inventory) {
        $inventory->available_quantity = max(
            0,
            (int) $inventory->quantity
                - (int) $inventory->reserved_quantity
        );

        $inventory->is_low_stock =
            (int) $inventory->available_quantity
            <= (int) $inventory->minimum_stock;

        return $inventory;
    });

        /*
        |--------------------------------------------------------------------------
        | Thống kê đánh giá
        |--------------------------------------------------------------------------
        */

        $reviewStatistics = DB::table('product_reviews')
            ->where('product_id', $productDetail->id)
            ->where('status', true)
            ->selectRaw('COUNT(*) as total_reviews')
            ->selectRaw(
                'COALESCE(AVG(rating), 0) as average_rating'
            )
            ->selectRaw(
                'SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5'
            )
            ->selectRaw(
                'SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4'
            )
            ->selectRaw(
                'SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3'
            )
            ->selectRaw(
                'SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2'
            )
            ->selectRaw(
                'SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1'
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Thống kê hỏi đáp
        |--------------------------------------------------------------------------
        |
        | Khóa ngoại thật của product_question_answers là question_id.
        |
        */

        $questionStatistics = [
            'total' => DB::table('product_questions')
                ->where('product_id', $productDetail->id)
                ->whereNull('deleted_at')
                ->count(),

            'published' => DB::table('product_questions')
                ->where('product_id', $productDetail->id)
                ->whereNull('deleted_at')
                ->whereIn(
                    'status',
                    [
                        'published',
                        'answered',
                    ]
                )
                ->count(),

            'answered' => DB::table('product_questions as pq')
                ->where('pq.product_id', $productDetail->id)
                ->whereNull('pq.deleted_at')
                ->whereExists(
                    function (Builder $query): void {
                        $query
                            ->selectRaw('1')
                            ->from(
                                'product_question_answers as pqa'
                            )
                            ->whereColumn(
                                'pqa.question_id',
                                'pq.id'
                            )
                            ->whereNull('pqa.deleted_at')
                            ->where('pqa.status', true)
                            ->where('pqa.is_official', true);
                    }
                )
                ->count(),

            'unanswered' => DB::table('product_questions as pq')
                ->where('pq.product_id', $productDetail->id)
                ->whereNull('pq.deleted_at')
                ->whereNotExists(
                    function (Builder $query): void {
                        $query
                            ->selectRaw('1')
                            ->from(
                                'product_question_answers as pqa'
                            )
                            ->whereColumn(
                                'pqa.question_id',
                                'pq.id'
                            )
                            ->whereNull('pqa.deleted_at')
                            ->where('pqa.status', true)
                            ->where('pqa.is_official', true);
                    }
                )
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Tổng hợp số liệu sản phẩm
        |--------------------------------------------------------------------------
        */

        $statistics = [
            'images' => $images->count(),

            'videos' => $videos->count(),

            'variants' => $variants->count(),

            'active_variants' => $variants
                ->where('status', true)
                ->count(),

            'skus' => $skus->count(),

            'active_skus' => $skus
                ->where('status', true)
                ->count(),

            'total_quantity' => (int) $skus
                ->sum('total_quantity'),

            'reserved_quantity' => (int) $skus
                ->sum('reserved_quantity'),

            'sold_quantity' => (int) $skus
                ->sum('sold_quantity'),

            'available_quantity' => (int) $skus
                ->sum('available_quantity'),

            'minimum_price' => $skus
                ->where('status', true)
                ->min('price'),

            'maximum_price' => $skus
                ->where('status', true)
                ->max('price'),

            'low_stock_count' => $inventories
                ->where('is_low_stock', true)
                ->count(),
        ];

        return view('admin.products.show', [
            'product' => $productDetail,
            'images' => $images,
            'videos' => $videos,
            'variants' => $variants,
            'skus' => $skus,
            'inventories' => $inventories,
            'statistics' => $statistics,
            'reviewStatistics' => $reviewStatistics,
            'questionStatistics' => $questionStatistics,
        ]);
    }
}
