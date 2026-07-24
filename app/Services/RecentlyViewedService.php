<?php

namespace App\Services;

use App\Models\RecentlyViewedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class RecentlyViewedService
{
    private const MAX_ITEMS = 30;

    public function record(
        Request $request,
        int $productId
    ): void {
        DB::transaction(function () use (
            $request,
            $productId
        ): void {
            $userId = $request->user()?->id;
            $sessionId = $request->session()->getId();

            $query = RecentlyViewedProduct::query()
                ->where('product_id', $productId);

            if ($userId) {
                $query->forUser($userId);
            } else {
                $query
                    ->whereNull('user_id')
                    ->forSession($sessionId);
            }

            $recent = $query
                ->lockForUpdate()
                ->first();

            if ($recent) {
                $recent->update([
                    'view_count' => $recent->view_count + 1,
                    'last_viewed_at' => now(),
                    'session_id' => $userId
                        ? null
                        : $sessionId,
                ]);
            } else {
                RecentlyViewedProduct::query()->create([
                    'user_id' => $userId,
                    'session_id' => $userId
                        ? null
                        : $sessionId,
                    'product_id' => $productId,
                    'view_count' => 1,
                    'last_viewed_at' => now(),
                ]);
            }

            $this->trimOldItems(
                $userId,
                $sessionId
            );
        });
    }
    /**
 * Lấy danh sách sản phẩm đã xem gần đây
 * để hiển thị bằng product-card.
 */
public function getProductsForRequest(
    Request $request,
    int $limit = 8,
    ?int $excludeProductId = null
): Collection {
    $recentQuery = RecentlyViewedProduct::query();

    if ($request->user()) {
        $recentQuery->where(
            'user_id',
            $request->user()->id
        );
    } else {
        $recentQuery
            ->whereNull('user_id')
            ->where(
                'session_id',
                $request->session()->getId()
            );
    }

    if ($excludeProductId !== null) {
        $recentQuery->where(
            'product_id',
            '!=',
            $excludeProductId
        );
    }

    $productIds = $recentQuery
        ->orderByDesc('last_viewed_at')
        ->orderByDesc('id')
        ->limit($limit)
        ->pluck('product_id');

    if ($productIds->isEmpty()) {
        return collect();
    }

    return DB::table('products as p')
        ->leftJoin(
            'brands as b',
            'p.brand_id',
            '=',
            'b.id'
        )
        ->whereIn('p.id', $productIds)
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
        ->sortBy(function ($product) use ($productIds) {
            return $productIds->search($product->id);
        })
        ->values();
}
    private function trimOldItems(
        ?int $userId,
        string $sessionId
    ): void {
        $query = RecentlyViewedProduct::query();

        if ($userId) {
            $query->forUser($userId);
        } else {
            $query
                ->whereNull('user_id')
                ->forSession($sessionId);
        }

        $idsToDelete = $query
            ->latestViewed()
            ->orderByDesc('id')
            ->skip(self::MAX_ITEMS)
            ->take(1000)
            ->pluck('id');

        if ($idsToDelete->isEmpty()) {
            return;
        }

        RecentlyViewedProduct::query()
            ->whereIn('id', $idsToDelete)
            ->delete();
    }
}
