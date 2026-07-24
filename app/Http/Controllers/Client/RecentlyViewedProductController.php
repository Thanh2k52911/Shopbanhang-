<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\RecentlyViewedProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecentlyViewedProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm đã xem gần đây.
     *
     * Hỗ trợ:
     * - Người dùng đã đăng nhập.
     * - Khách chưa đăng nhập thông qua session.
     */
    public function index(Request $request): View
    {
        $query = RecentlyViewedProduct::query()
            ->with([
                'product' => function ($productQuery) {
                    $productQuery->with([
                        'brand:id,name,slug',

                        'images' => function ($imageQuery) {
                            $imageQuery
                                ->orderByDesc('is_thumbnail')
                                ->orderBy('sort_order')
                                ->orderBy('id');
                        },

                        'skus' => function ($skuQuery) {
                            $skuQuery
                                ->where('status', true)
                                ->orderBy('price')
                                ->orderBy('id');
                        },
                    ]);
                },
            ])
            ->whereHas('product', function ($productQuery) {
                $productQuery
                    ->where('status', true)
                    ->whereNull('deleted_at');
            });

        if ($request->user()) {
            $query->where(
                'user_id',
                $request->user()->id
            );
        } else {
            $query
                ->whereNull('user_id')
                ->where(
                    'session_id',
                    $request->session()->getId()
                );
        }

        $recentProducts = $query
            ->orderByDesc('last_viewed_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view(
            'client.recently-viewed.index',
            compact('recentProducts')
        );
    }

    /**
     * Xóa một sản phẩm khỏi lịch sử xem.
     */
    public function destroy(
        Request $request,
        RecentlyViewedProduct $recentlyViewedProduct
    ): JsonResponse {
        if (
            !$this->canManage(
                $request,
                $recentlyViewedProduct
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa lịch sử này.',
            ], 403);
        }

        $recentlyViewedProduct->delete();

        return response()->json([
            'success' => true,
            'remaining_count' => $this->countForRequest(
                $request
            ),
            'message' => 'Đã xóa sản phẩm khỏi lịch sử đã xem.',
        ]);
    }

    /**
     * Xóa toàn bộ lịch sử sản phẩm đã xem.
     */
    public function clear(
        Request $request
    ): RedirectResponse {
        $query = RecentlyViewedProduct::query();

        if ($request->user()) {
            $query->where(
                'user_id',
                $request->user()->id
            );
        } else {
            $query
                ->whereNull('user_id')
                ->where(
                    'session_id',
                    $request->session()->getId()
                );
        }

        $query->delete();

        return back()->with(
            'success',
            'Đã xóa toàn bộ lịch sử sản phẩm đã xem.'
        );
    }

    /**
     * Kiểm tra bản ghi lịch sử có thuộc người hiện tại không.
     */
    private function canManage(
        Request $request,
        RecentlyViewedProduct $recent
    ): bool {
        if ($request->user()) {
            return (int) $recent->user_id
                === (int) $request->user()->id;
        }

        return $recent->user_id === null
            && hash_equals(
                (string) $recent->session_id,
                (string) $request
                    ->session()
                    ->getId()
            );
    }

    /**
     * Đếm lịch sử còn lại của người dùng hoặc session.
     */
    private function countForRequest(
        Request $request
    ): int {
        $query = RecentlyViewedProduct::query();

        if ($request->user()) {
            $query->where(
                'user_id',
                $request->user()->id
            );
        } else {
            $query
                ->whereNull('user_id')
                ->where(
                    'session_id',
                    $request->session()->getId()
                );
        }

        return $query->count();
    }
}
