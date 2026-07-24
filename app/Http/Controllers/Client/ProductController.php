<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Collection;
use App\Models\ProductReview;
use App\Models\ProductFavorite;
use App\Services\RecentlyViewedService;
use App\Models\RecentlyViewedProduct;
use App\Models\ProductQuestion;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'keyword' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:180'],
            'brand' => ['nullable', 'string', 'max:255'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:min_price',
            ],
            'sort' => [
                'nullable',
                'in:newest,price_asc,price_desc,best_selling,featured',
            ],
            'per_page' => ['nullable', 'integer', 'in:12,24,36'],
        ]);

        $keyword = trim($validated['keyword'] ?? '');
        $categorySlug = $validated['category'] ?? null;
        $brandSlug = $validated['brand'] ?? null;
        $minPrice = isset($validated['min_price'])
            ? (float) $validated['min_price']
            : null;
        $maxPrice = isset($validated['max_price'])
            ? (float) $validated['max_price']
            : null;
        $sort = $validated['sort'] ?? 'newest';
        $perPage = (int) ($validated['per_page'] ?? 12);

        $query = DB::table('products as p')
            ->leftJoin(
                'brands as b',
                'p.brand_id',
                '=',
                'b.id'
            )
            ->leftJoin(
                'categories as c',
                'p.category_id',
                '=',
                'c.id'
            )
            ->where('p.status', true)
            ->whereNull('p.deleted_at')
            ->select([
                'p.id',
                'p.name',
                'p.slug',
                'p.short_description',
                'p.is_featured',
                'p.view_count',
                'p.created_at',
                'b.name as brand_name',
                'b.slug as brand_slug',
                'c.name as category_name',
                'c.slug as category_slug',
            ])
            ->selectSub(function (Builder $subQuery): void {
                $subQuery
                    ->from('product_skus')
                    ->selectRaw('MIN(price)')
                    ->whereColumn(
                        'product_skus.product_id',
                        'p.id'
                    )
                    ->where('status', true);
            }, 'price')
            ->selectSub(function (Builder $subQuery): void {
                $subQuery
                    ->from('product_images')
                    ->select('image_path')
                    ->whereColumn(
                        'product_images.product_id',
                        'p.id'
                    )
                    ->orderByDesc('is_thumbnail')
                    ->orderBy('sort_order')
                    ->limit(1);
            }, 'image_path')
            ->selectSub(function (Builder $subQuery): void {
    $subQuery
        ->from('product_favorites')
        ->selectRaw('COUNT(*)')
        ->whereColumn(
            'product_favorites.product_id',
            'p.id'
        )
        ->where(
            'product_favorites.user_id',
            auth()->id() ?? 0
        );
}, 'is_favorite')
            ->selectSub(function (Builder $subQuery): void {
                $subQuery
                    ->from('product_reviews')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn(
                        'product_reviews.product_id',
                        'p.id'
                    )
                    ->where('status', true);
            }, 'review_count')
            ->selectSub(function (Builder $subQuery): void {
                $subQuery
                    ->from('product_reviews')
                    ->selectRaw(
                        'COALESCE(AVG(rating), 0)'
                    )
                    ->whereColumn(
                        'product_reviews.product_id',
                        'p.id'
                    )
                    ->where('status', true);
            }, 'average_rating')
            ->selectSub(function (Builder $subQuery): void {
                $subQuery
                    ->from('product_statistics')
                    ->selectRaw(
                        'COALESCE(MAX(sold_quantity), 0)'
                    )
                    ->whereColumn(
                        'product_statistics.product_id',
                        'p.id'
                    );
            }, 'sold_quantity');

        /*
         * Chỉ hiển thị sản phẩm có ít nhất một SKU hoạt động.
         */
        $query->whereExists(function (Builder $subQuery): void {
            $subQuery
                ->selectRaw('1')
                ->from('product_skus')
                ->whereColumn(
                    'product_skus.product_id',
                    'p.id'
                )
                ->where('status', true);
        });

        if ($keyword !== '') {
            $query->where(function (Builder $subQuery) use ($keyword): void {
                $subQuery
                    ->where('p.name', 'like', "%{$keyword}%")
                    ->orWhere(
                        'p.short_description',
                        'like',
                        "%{$keyword}%"
                    )
                    ->orWhere(
                        'p.description',
                        'like',
                        "%{$keyword}%"
                    )
                    ->orWhere(
                        'p.ingredient',
                        'like',
                        "%{$keyword}%"
                    )
                    ->orWhere(
                        'b.name',
                        'like',
                        "%{$keyword}%"
                    );
            });
        }

        if ($categorySlug) {
            $query->where('c.slug', $categorySlug);
        }

        if ($brandSlug) {
            $query->where('b.slug', $brandSlug);
        }

        if ($minPrice !== null) {
            $query->whereRaw(
                '(
                    SELECT MIN(ps.price)
                    FROM product_skus AS ps
                    WHERE ps.product_id = p.id
                      AND ps.status = 1
                ) >= ?',
                [$minPrice]
            );
        }

        if ($maxPrice !== null) {
            $query->whereRaw(
                '(
                    SELECT MIN(ps.price)
                    FROM product_skus AS ps
                    WHERE ps.product_id = p.id
                      AND ps.status = 1
                ) <= ?',
                [$maxPrice]
            );
        }

        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'best_selling' => $query
                ->orderByDesc('sold_quantity')
                ->orderByDesc('p.created_at'),
            'featured' => $query
                ->orderByDesc('p.is_featured')
                ->orderByDesc('p.updated_at'),
            default => $query->orderByDesc('p.created_at'),
        };

        $products = $query
            ->paginate($perPage)
            ->withQueryString();

        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
            ]);

        $brands = Brand::query()
            ->where('status', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
            ]);

        return view('client.product.index', compact(
            'products',
            'categories',
            'brands',
            'keyword',
            'categorySlug',
            'brandSlug',
            'minPrice',
            'maxPrice',
            'sort',
            'perPage'

        ));

    }
    public function show(
    Request $request,
    string $slug,
    RecentlyViewedService $recentlyViewedService
): View
{
    $product = DB::table('products as p')
        ->leftJoin(
            'brands as b',
            'p.brand_id',
            '=',
            'b.id'
        )
        ->leftJoin(
            'categories as c',
            'p.category_id',
            '=',
            'c.id'
        )
        ->where('p.slug', $slug)
        ->where('p.status', true)
        ->whereNull('p.deleted_at')
        ->select([
            'p.id',
            'p.category_id',
            'p.brand_id',
            'p.name',
            'p.slug',
            'p.short_description',
            'p.description',
            'p.ingredient',
            'p.usage',
            'p.skin_type',
            'p.origin',
            'p.is_featured',
            'p.view_count',
            'b.name as brand_name',
            'b.slug as brand_slug',
            'b.country as brand_country',
            'c.name as category_name',
            'c.slug as category_slug',
        ])
        ->first();

    abort_if(!$product, 404);

/*
|--------------------------------------------------------------------------
| Ghi nhận sản phẩm đã xem
|--------------------------------------------------------------------------
*/

$recentlyViewedService->record(
    $request,
    (int) $product->id
);

/*
|--------------------------------------------------------------------------
| Tăng lượt xem
|--------------------------------------------------------------------------
*/

DB::table('products')
    ->where('id', $product->id)
    ->increment('view_count');

    $images = DB::table('product_images')
        ->where('product_id', $product->id)
        ->orderByDesc('is_thumbnail')
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get([
            'id',
            'image_path',
            'is_thumbnail',
            'sort_order',
        ]);

    $videos = DB::table('product_videos')
        ->where('product_id', $product->id)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get([
            'id',
            'title',
            'video_url',
            'type',
            'sort_order',
        ]);

    $skus = DB::table('product_skus as ps')
        ->leftJoin(
            'product_variants as pv',
            'ps.variant_id',
            '=',
            'pv.id'
        )
        ->where('ps.product_id', $product->id)
        ->where('ps.status', true)
        ->select([
            'ps.id',
            'ps.variant_id',
            'ps.sku_code',
            'ps.barcode',
            'ps.price',
            'ps.weight',
            'pv.name as variant_name',
            'pv.compare_price',
        ])
        ->selectSub(function ($query): void {
            $query
                ->from('inventories')
                ->selectRaw(
                    'COALESCE(
                        SUM(quantity - reserved_quantity),
                        0
                    )'
                )
                ->whereColumn(
                    'inventories.sku_id',
                    'ps.id'
                );
        }, 'available_quantity')
        ->orderBy('ps.price')
        ->get();

    abort_if($skus->isEmpty(), 404);

    $variantValues = $this->getVariantValues(
        $skus
            ->pluck('variant_id')
            ->filter()
            ->values()
    );

    $skus->transform(function ($sku) use ($variantValues) {
        $sku->attributes = $sku->variant_id
            ? $variantValues->get($sku->variant_id, collect())
            : collect();

        return $sku;
    });

    $defaultSku = $skus->firstWhere(
        fn ($sku) => (int) $sku->available_quantity > 0
    ) ?? $skus->first();

    $discount = DB::table('product_discounts as pd')
        ->join(
            'discount_campaigns as dc',
            'pd.campaign_id',
            '=',
            'dc.id'
        )
        ->where('pd.product_id', $product->id)
        ->where('dc.status', true)
        ->where('dc.start_date', '<=', now())
        ->where('dc.end_date', '>=', now())
        ->orderByDesc('dc.is_flash_sale')
        ->orderBy('dc.end_date')
        ->select([
            'dc.id as campaign_id',
            'dc.name as campaign_name',
            'dc.is_flash_sale',
            'dc.end_date',
            'pd.discount_percent',
            'pd.discount_amount',
            'pd.limit_quantity',
            'pd.sold_quantity',
        ])
        ->first();

    $reviewSummary = DB::table('product_reviews')
        ->where('product_id', $product->id)
        ->where('status', true)
        ->selectRaw('COUNT(*) as review_count')
        ->selectRaw('COALESCE(AVG(rating), 0) as average_rating')
        ->first();

    $relatedProducts = DB::table('products as p')
        ->leftJoin(
            'brands as b',
            'p.brand_id',
            '=',
            'b.id'
        )
        ->where('p.category_id', $product->category_id)
        ->where('p.id', '!=', $product->id)
        ->where('p.status', true)
        ->whereNull('p.deleted_at')
        ->whereExists(function ($query): void {
            $query
                ->selectRaw('1')
                ->from('product_skus')
                ->whereColumn(
                    'product_skus.product_id',
                    'p.id'
                )
                ->where('status', true);
        })
        ->select([
            'p.id',
            'p.name',
            'p.slug',
            'p.short_description',
            'p.is_featured',
            'b.name as brand_name',
        ])
        ->selectSub(function ($query): void {
            $query
                ->from('product_skus')
                ->selectRaw('MIN(price)')
                ->whereColumn(
                    'product_skus.product_id',
                    'p.id'
                )
                ->where('status', true);
        }, 'price')
        ->selectSub(function ($query): void {
            $query
                ->from('product_images')
                ->select('image_path')
                ->whereColumn(
                    'product_images.product_id',
                    'p.id'
                )
                ->orderByDesc('is_thumbnail')
                ->orderBy('sort_order')
                ->limit(1);
        }, 'image_path')
        ->selectSub(function ($query): void {
            $query
                ->from('product_reviews')
                ->selectRaw('COUNT(*)')
                ->whereColumn(
                    'product_reviews.product_id',
                    'p.id'
                )
                ->where('status', true);
        }, 'review_count')
        ->limit(4)
        ->get();
        $reviews = ProductReview::query()
    ->with([
        'user:id,name,avatar',
        'images:id,review_id,image_path',
        'videos:id,review_id,video_path',
        'replies' => function ($query) {
    $query
        ->with([
            'user:id,name,avatar',
            'user.roles:id,name',
        ])
        ->oldest();
},
    ])
    ->withCount('likes')
    ->withExists([
    'likes as is_liked_by_current_user' => function ($query) {
        $query->where(
            'user_id',
            auth()->id() ?? 0
        );
    },
])
    ->where('product_id', $product->id)
    ->where('status', true)
    ->latest()
    ->paginate(10, ['*'], 'review_page');


$reviewStatistics = ProductReview::query()
    ->where('product_id', $product->id)
    ->where('status', true)
    ->selectRaw('COUNT(*) as total_reviews')
    ->selectRaw('COALESCE(AVG(rating), 0) as average_rating')
    ->first();

$ratingCounts = ProductReview::query()
    ->where('product_id', $product->id)
    ->where('status', true)
    ->selectRaw('rating, COUNT(*) as total')
    ->groupBy('rating')
    ->pluck('total', 'rating');

$ratingBreakdown = collect(range(5, 1))
    ->mapWithKeys(function (int $rating) use (
        $ratingCounts,
        $reviewStatistics
    ) {
        $count = (int) ($ratingCounts[$rating] ?? 0);
        $total = (int) $reviewStatistics->total_reviews;

        return [
            $rating => [
                'count' => $count,
                'percentage' => $total > 0
                    ? round(($count / $total) * 100, 1)
                    : 0,
            ],
        ];
    });

$reviewStatistics->average_rating = round(
    (float) $reviewStatistics->average_rating,
    1
);

$isFavorite = false;

if (auth()->check()) {
    $isFavorite = ProductFavorite::query()
        ->where('user_id', auth()->id())
        ->where('product_id', $product->id)
        ->exists();
}
/*
|--------------------------------------------------------------------------
| Các sản phẩm người dùng vừa xem
|--------------------------------------------------------------------------
*/

$recentlyViewedQuery = RecentlyViewedProduct::query()
    ->where('product_id', '!=', $product->id);

if ($request->user()) {
    $recentlyViewedQuery->where(
        'user_id',
        $request->user()->id
    );
} else {
    $recentlyViewedQuery
        ->whereNull('user_id')
        ->where(
            'session_id',
            $request->session()->getId()
        );
}

$recentlyViewedProductIds = $recentlyViewedQuery
    ->orderByDesc('last_viewed_at')
    ->orderByDesc('id')
    ->limit(8)
    ->pluck('product_id');

$recentlyViewedProducts = collect();

if ($recentlyViewedProductIds->isNotEmpty()) {
    $recentlyViewedProducts = DB::table('products as p')
        ->leftJoin(
            'brands as b',
            'p.brand_id',
            '=',
            'b.id'
        )
        ->whereIn('p.id', $recentlyViewedProductIds)
        ->where('p.status', true)
        ->whereNull('p.deleted_at')
        ->whereExists(function ($query): void {
            $query
                ->selectRaw('1')
                ->from('product_skus')
                ->whereColumn(
                    'product_skus.product_id',
                    'p.id'
                )
                ->where('status', true);
        })
        ->select([
            'p.id',
            'p.name',
            'p.slug',
            'p.short_description',
            'p.is_featured',
            'b.name as brand_name',
        ])
        ->selectSub(function ($query): void {
            $query
                ->from('product_skus')
                ->selectRaw('MIN(price)')
                ->whereColumn(
                    'product_skus.product_id',
                    'p.id'
                )
                ->where('status', true);
        }, 'price')
        ->selectSub(function ($query): void {
            $query
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
        }, 'image_path')
        ->selectSub(function ($query): void {
            $query
                ->from('product_reviews')
                ->selectRaw('COUNT(*)')
                ->whereColumn(
                    'product_reviews.product_id',
                    'p.id'
                )
                ->where('status', true);
        }, 'review_count')
        ->selectSub(function ($query): void {
            $query
                ->from('product_reviews')
                ->selectRaw(
                    'COALESCE(AVG(rating), 0)'
                )
                ->whereColumn(
                    'product_reviews.product_id',
                    'p.id'
                )
                ->where('status', true);
        }, 'average_rating')
        ->selectSub(function ($query): void {
            $query
                ->from('product_statistics')
                ->selectRaw(
                    'COALESCE(MAX(sold_quantity), 0)'
                )
                ->whereColumn(
                    'product_statistics.product_id',
                    'p.id'
                );
        }, 'sold_quantity')
        ->selectSub(function ($query): void {
            $query
                ->from('product_favorites')
                ->selectRaw('COUNT(*)')
                ->whereColumn(
                    'product_favorites.product_id',
                    'p.id'
                )
                ->where(
                    'product_favorites.user_id',
                    auth()->id() ?? 0
                );
        }, 'is_favorite')
        ->get()
        ->sortBy(function ($recentProduct) use (
            $recentlyViewedProductIds
        ) {
            return $recentlyViewedProductIds
                ->search($recentProduct->id);
        })
        ->values();
}
/*
|--------------------------------------------------------------------------
| Cộng đồng hỏi đáp sản phẩm
|--------------------------------------------------------------------------
*/

$productQuestions = ProductQuestion::query()
    ->with([
        'user:id,name,avatar',
        'user.roles:id,name',
    ])
    ->where('product_id', $product->id)
    ->where('is_public', true)
    ->whereIn(
        'status',
        [
            'published',
            'answered',
        ]
    )
    ->latest()
    ->paginate(
        10,
        ['*'],
        'question_page'
    );

$questionIds = $productQuestions
    ->getCollection()
    ->pluck('id');

$answersByQuestion = collect();

if ($questionIds->isNotEmpty()) {
    $answers = DB::table(
        'product_question_answers as pqa'
    )
        ->leftJoin(
            'users as u',
            'pqa.user_id',
            '=',
            'u.id'
        )
        ->whereIn('pqa.question_id', $questionIds)
        ->where('pqa.status', true)
        ->whereNull('pqa.deleted_at')
        ->orderBy('pqa.created_at')
        ->get([
            'pqa.id',
            'pqa.question_id',
            'pqa.user_id',
            'pqa.answer',
            'pqa.is_official',
            'pqa.created_at',
            'u.name as user_name',
            'u.avatar as user_avatar',
        ]);

    $shopUserIds = $answers
        ->pluck('user_id')
        ->filter()
        ->unique()
        ->values();

    $shopRoleUserIds = $shopUserIds->isEmpty()
        ? collect()
        : DB::table('user_roles as ur')
            ->join(
                'roles as r',
                'ur.role_id',
                '=',
                'r.id'
            )
            ->whereIn(
                'ur.user_id',
                $shopUserIds
            )
            ->whereIn(
                'r.name',
                [
                    'admin',
                    'super_admin',
                ]
            )
            ->pluck('ur.user_id')
            ->map(
                fn ($userId): int => (int) $userId
            )
            ->unique();

    $answersByQuestion = $answers
        ->map(function ($answer) use (
            $shopRoleUserIds
        ) {
            $isShop = (bool) $answer->is_official
                || (
                    $answer->user_id
                    && $shopRoleUserIds->contains(
                        (int) $answer->user_id
                    )
                );

            $answer->is_shop = $isShop;

            $answer->author_name = $isShop
                ? site_name()
                : (
                    $answer->user_name
                    ?: 'Thành viên'
                );

            $answer->created_at_display =
                \Carbon\Carbon::parse(
                    $answer->created_at
                )->format('d/m/Y H:i');

            return $answer;
        })
        ->groupBy('question_id');
}

$productQuestions
    ->getCollection()
    ->transform(function ($question) use (
        $answersByQuestion
    ) {
        $question->community_answers =
            $answersByQuestion->get(
                $question->id,
                collect()
            );

        return $question;
    });

$questionCount = ProductQuestion::query()
    ->where('product_id', $product->id)
    ->where('is_public', true)
    ->whereIn(
        'status',
        [
            'published',
            'answered',
        ]
    )
    ->count();
        return view('client.product.show', compact(
        'product',
        'images',
        'videos',
        'skus',
        'defaultSku',
        'discount',
        'reviewSummary',
        'relatedProducts',
         'reviews',
        'reviewStatistics',
        'ratingBreakdown',
        'isFavorite',
        'productQuestions',
        'questionCount',
        'recentlyViewedProducts'
    ));
}

private function getVariantValues(Collection $variantIds): Collection
{
    if ($variantIds->isEmpty()) {
        return collect();
    }

    return DB::table('product_variant_values as pvv')
        ->join(
            'variant_values as vv',
            'pvv.value_id',
            '=',
            'vv.id'
        )
        ->join(
            'variant_attributes as va',
            'vv.attribute_id',
            '=',
            'va.id'
        )
        ->whereIn('pvv.variant_id', $variantIds)
        ->orderBy('va.id')
        ->get([
            'pvv.variant_id',
            'va.name as attribute_name',
            'vv.value as attribute_value',
        ])
        ->groupBy('variant_id');
}
}
