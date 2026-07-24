<?php

namespace App\Http\Middleware;

use App\Services\SearchHistoryService;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RecordProductActivity
{
    public function __construct(
        private readonly SearchHistoryService
            $searchHistoryService
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $response = $next($request);

        /*
         * Ghi lượt tìm kiếm sau khi controller
         * đã chạy xong để lấy đúng tổng kết quả.
         */
        if (
            $request->isMethod('GET')
            && $request->routeIs(
                'products.index'
            )
        ) {
            $this->recordSearch(
                $request,
                $response
            );
        }

        /*
         * Khi người dùng đi từ trang kết quả
         * sang chi tiết sản phẩm, cập nhật
         * clicked_product_id.
         */
        if (
            $request->isMethod('GET')
            && $request->routeIs(
                'products.show'
            )
        ) {
            $this->searchHistoryService
                ->recordProductClick(
                    $request,

                    (string) $request
                        ->route('slug')
                );
        }

        return $response;
    }

    private function recordSearch(
        Request $request,
        Response $response
    ): void {
        /*
         * Chuyển trang phân trang không tạo
         * thêm một lượt tìm kiếm mới.
         */
        if (
            (int) $request->input(
                'page',
                1
            ) > 1
        ) {
            return;
        }

        $keyword = trim(
            (string) $request->input(
                'keyword',
                ''
            )
        );

        $hasFilter = collect([
            $request->input('category'),
            $request->input('brand'),
            $request->input('min_price'),
            $request->input('max_price'),
        ])->contains(
            fn ($value): bool =>
                $value !== null
                && $value !== ''
        );

        /*
         * Chỉ mở trang tất cả sản phẩm
         * thì không ghi lịch sử.
         */
        if (
            $keyword === ''
            && ! $hasFilter
        ) {
            return;
        }

        if (
            ! method_exists(
                $response,
                'getOriginalContent'
            )
        ) {
            return;
        }

        $content =
            $response->getOriginalContent();

        if (! $content instanceof View) {
            return;
        }

        $products = $content
            ->getData()['products']
            ?? null;

        if (
            ! $products
                instanceof LengthAwarePaginator
        ) {
            return;
        }

        $this->searchHistoryService
            ->record(
                $request,

                $keyword,

                $request->only([
                    'category',
                    'brand',
                    'min_price',
                    'max_price',
                    'sort',
                    'per_page',
                ]),

                (int) $products->total()
            );
    }
}
