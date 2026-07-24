<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductFavoriteController extends Controller
{
    /**
     * Danh sách sản phẩm yêu thích của người dùng.
     */
    public function index(Request $request): View
    {
        $favorites = ProductFavorite::query()
            ->with([
                'product' => function ($query) {
                    $query->with([
                        'category:id,name,slug',
                        'brand:id,name,slug',
                        'images' => function ($imageQuery) {
                            $imageQuery
                                ->orderByDesc('is_thumbnail')
                                ->orderBy('sort_order');
                        },
                        'skus' => function ($skuQuery) {
                            $skuQuery
                                ->where('status', true)
                                ->orderBy('price');
                        },
                    ]);
                },
            ])
            ->where('user_id', $request->user()->id)
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->latest()
            ->paginate(12);

        return view(
            'client.account.favorites.index',
            compact('favorites')
        );
    }

    /**
     * Thêm hoặc bỏ sản phẩm khỏi danh sách yêu thích.
     */
    public function toggle(
        Request $request,
        Product $product
    ): JsonResponse {
        if (!$product->status) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm hiện không khả dụng.',
            ], 404);
        }

        $favorite = ProductFavorite::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->first();

        if ($favorite) {
            $favorite->delete();

            $isFavorite = false;
            $message = 'Đã xóa sản phẩm khỏi danh sách yêu thích.';
        } else {
            ProductFavorite::query()->create([
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
            ]);

            $isFavorite = true;
            $message = 'Đã thêm sản phẩm vào danh sách yêu thích.';
        }

        $favoritesCount = ProductFavorite::query()
            ->where('user_id', $request->user()->id)
            ->count();

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite,
            'favorites_count' => $favoritesCount,
            'message' => $message,
        ]);
    }

    /**
     * Xóa một sản phẩm khỏi danh sách yêu thích.
     */
    public function destroy(
        Request $request,
        ProductFavorite $favorite
    ): JsonResponse {
        if ((int) $favorite->user_id !== (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện thao tác này.',
            ], 403);
        }

        $favorite->delete();

        $favoritesCount = ProductFavorite::query()
            ->where('user_id', $request->user()->id)
            ->count();

        return response()->json([
            'success' => true,
            'favorites_count' => $favoritesCount,
            'message' => 'Đã xóa sản phẩm khỏi danh sách yêu thích.',
        ]);
    }

}
