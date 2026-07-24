<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Services\RecentlyViewedService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(
    Request $request,
    RecentlyViewedService $recentlyViewedService
): View
    {
        $banners = Banner::query()
            ->active()
            ->atPosition('home_slider')
            ->get();
        $middleBanners = Banner::query()
    ->active()
    ->atPosition('home_middle')
    ->get();

    $bottomBanners = Banner::query()
    ->active()
    ->atPosition('home_bottom')
    ->get();
        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(8)
            ->get();

        $brands = Brand::query()
            ->where('status', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(10)
            ->get();

        $flashCampaign = DB::table('discount_campaigns')
    ->where('is_flash_sale', true)
    ->where('status', true)
    ->where('start_date', '<=', now())
    ->where('end_date', '>=', now())
    ->orderBy('end_date')
    ->first();

$flashProducts = collect();

if ($flashCampaign) {
    $flashProducts = DB::table('product_discounts as pd')
        ->join(
            'products as p',
            'pd.product_id',
            '=',
            'p.id'
        )
        ->leftJoin(
            'brands as b',
            'p.brand_id',
            '=',
            'b.id'
        )
        ->where('pd.campaign_id', $flashCampaign->id)
        ->where('p.status', true)
        ->select([
            'p.id',
            'p.name',
            'p.slug',
            'p.short_description',
            'b.name as brand_name',
            'pd.discount_percent',
            'pd.discount_amount',
            'pd.limit_quantity',
            'pd.sold_quantity',
        ])
        ->selectSub(function ($query) {
            $query
                ->from('product_skus')
                ->selectRaw('MIN(price)')
                ->whereColumn(
                    'product_skus.product_id',
                    'p.id'
                )
                ->where('status', true);
        }, 'original_price')
        ->selectSub(function ($query) {
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
        ->limit(8)
        ->get()
        ->filter(
            fn ($product) => $product->original_price !== null
        )
        ->map(function ($product) {
            $originalPrice = (float) $product->original_price;
            $salePrice = $originalPrice;

            if ($product->discount_percent !== null) {
                $salePrice = $originalPrice
                    * (
                        1
                        - ((float) $product->discount_percent / 100)
                    );
            } elseif ($product->discount_amount !== null) {
                $salePrice = $originalPrice
                    - (float) $product->discount_amount;
            }

            $product->original_price = $originalPrice;
            $product->sale_price = max(0, round($salePrice));

            $product->discount_label =
                $product->discount_percent !== null
                    ? '-' . (float) $product->discount_percent . '%'
                    : '-' . number_format(
                        (float) $product->discount_amount,
                        0,
                        ',',
                        '.'
                    ) . 'đ';

            if ($product->limit_quantity) {
                $product->progress = min(
                    100,
                    round(
                        (
                            $product->sold_quantity
                            / $product->limit_quantity
                        ) * 100
                    )
                );
            } else {
                $product->progress = 0;
            }

            return $product;
        });
}
$newProducts = DB::table('products as p')
    ->leftJoin(
        'brands as b',
        'p.brand_id',
        '=',
        'b.id'
    )
    ->where('p.status', true)
    ->select([
        'p.id',
        'p.name',
        'p.slug',
        'p.short_description',
        'p.created_at',
        'b.name as brand_name',
    ])
    ->selectSub(function ($query) {
        $query
            ->from('product_skus')
            ->selectRaw('MIN(price)')
            ->whereColumn(
                'product_skus.product_id',
                'p.id'
            )
            ->where('status', true);
    }, 'price')
    ->selectSub(function ($query) {
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
    ->selectSub(function ($query) {
        $query
            ->from('product_reviews')
            ->selectRaw('COUNT(*)')
            ->whereColumn(
                'product_reviews.product_id',
                'p.id'
            )
            ->where('status', true);
    }, 'review_count')
    ->orderByDesc('p.created_at')
    ->limit(8)
    ->get()
    ->filter(
        fn ($product) => $product->price !== null
    )
    ->values();
    $bestSellingProducts = DB::table('products as p')
    ->leftJoin(
        'brands as b',
        'p.brand_id',
        '=',
        'b.id'
    )
    ->join(
        'order_items as oi',
        'p.id',
        '=',
        'oi.product_id'
    )
    ->join(
        'orders as o',
        'oi.order_id',
        '=',
        'o.id'
    )
    ->where('p.status', true)
    ->whereIn('o.order_status', [
        'completed',
        'delivered',
    ])
    ->groupBy(
        'p.id',
        'p.name',
        'p.slug',
        'p.short_description',
        'b.name'
    )
    ->select([
        'p.id',
        'p.name',
        'p.slug',
        'p.short_description',
        'b.name as brand_name',
    ])
    ->selectRaw(
        'SUM(oi.quantity) as sold_quantity'
    )
    ->selectSub(function ($query) {
        $query
            ->from('product_skus')
            ->selectRaw('MIN(price)')
            ->whereColumn(
                'product_skus.product_id',
                'p.id'
            )
            ->where('status', true);
    }, 'price')
    ->selectSub(function ($query) {
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
    ->selectSub(function ($query) {
        $query
            ->from('product_reviews')
            ->selectRaw('COUNT(*)')
            ->whereColumn(
                'product_reviews.product_id',
                'p.id'
            )
            ->where('status', true);
    }, 'review_count')
    ->orderByDesc('sold_quantity')
    ->limit(8)
    ->get()
    ->filter(
        fn ($product) => $product->price !== null
    )
    ->values();
    $featuredProducts = DB::table('products as p')
    ->leftJoin(
        'brands as b',
        'p.brand_id',
        '=',
        'b.id'
    )

    ->where('p.status', true)
    ->where('p.is_featured', true)
    ->select([
        'p.id',
        'p.name',
        'p.slug',
        'p.short_description',
        'b.name as brand_name',
    ])
    ->selectSub(function ($query) {
        $query
            ->from('product_skus')
            ->selectRaw('MIN(price)')
            ->whereColumn(
                'product_skus.product_id',
                'p.id'
            )
            ->where('status', true);
    }, 'price')
    ->selectSub(function ($query) {
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
    ->selectSub(function ($query) {
        $query
            ->from('product_reviews')
            ->selectRaw('COUNT(*)')
            ->whereColumn(
                'product_reviews.product_id',
                'p.id'
            )
            ->where('status', true);
    }, 'review_count')

    ->orderByDesc('p.updated_at')
    ->limit(8)
    ->get()
    ->filter(
        fn ($product) => $product->price !== null
    )
    ->values();
    $recentlyViewedProducts =
    $recentlyViewedService->getProductsForRequest(
        $request,
        8
    );
       return view('client.home.index', compact(
    'banners',
    'middleBanners',
    'bottomBanners',
    'categories',
    'brands',
    'flashCampaign',
    'flashProducts',
    'newProducts',
    'bestSellingProducts' ,
    'featuredProducts',
    'recentlyViewedProducts'
));
    }
}
