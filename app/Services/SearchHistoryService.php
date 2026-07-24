<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SearchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SearchHistoryService
{
    public const SESSION_KEY =
        'last_product_search_history_id';

    public const SESSION_TIME_KEY =
        'last_product_search_recorded_at';

    /**
     * @param array<string, mixed> $filters
     */
    public function record(
        Request $request,
        string $keyword,
        array $filters,
        int $resultCount
    ): SearchHistory {
        $normalizedKeyword = trim(
            $keyword
        );

        $normalizedFilters =
            $this->normalizeFilters(
                $filters
            );

        $signature = hash(
            'sha256',
            json_encode(
                [
                    'user_id' =>
                        $request->user()?->id,

                    'session_id' =>
                        $request
                            ->session()
                            ->getId(),

                    'keyword' => Str::lower(
                        $normalizedKeyword
                    ),

                    'filters' =>
                        $normalizedFilters,
                ],
                JSON_UNESCAPED_UNICODE
            )
        );

        /*
         * Chống ghi trùng khi trình duyệt gửi lại
         * cùng một request trong vài giây.
         */
        $recent = SearchHistory::query()
            ->where(
                function ($query) use (
                    $request
                ): void {
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
                                $request
                                    ->session()
                                    ->getId()
                            );
                    }
                }
            )
            ->where(
                'created_at',
                '>=',
                now()->subSeconds(3)
            )
            ->latestSearches()
            ->first();

        if (
            $recent
            && data_get(
                $recent->filters,
                '_signature'
            ) === $signature
        ) {
            $recent->forceFill([
                'result_count' =>
                    max(0, $resultCount),

                'ip_address' =>
                    $request->ip(),
            ])->save();

            $history = $recent;
        } else {
            $history =
                SearchHistory::query()
                    ->create([
                        'user_id' =>
                            $request
                                ->user()
                                ?->id,

                        'session_id' =>
                            $request
                                ->session()
                                ->getId(),

                        'keyword' =>
                            $normalizedKeyword !== ''
                                ? $normalizedKeyword
                                : 'Bộ lọc sản phẩm',

                        'filters' => [
                            ...$normalizedFilters,

                            '_signature' =>
                                $signature,
                        ],

                        'result_count' =>
                            max(
                                0,
                                $resultCount
                            ),

                        'clicked_product_id' =>
                            null,

                        'ip_address' =>
                            $request->ip(),
                    ]);
        }

        $request->session()->put([
            self::SESSION_KEY =>
                $history->id,

            self::SESSION_TIME_KEY =>
                now()->timestamp,
        ]);

        return $history;
    }

    public function recordProductClick(
        Request $request,
        string $productSlug
    ): void {
        $historyId = (int) $request
            ->session()
            ->get(
                self::SESSION_KEY,
                0
            );

        $recordedAt = (int) $request
            ->session()
            ->get(
                self::SESSION_TIME_KEY,
                0
            );

        /*
         * Chỉ gắn click với một lượt tìm kiếm
         * còn mới trong vòng hai giờ.
         */
        if (
            $historyId < 1
            || $recordedAt
                < now()
                    ->subHours(2)
                    ->timestamp
            || ! $this
                ->cameFromProductListing(
                    $request
                )
        ) {
            return;
        }

        $history =
            SearchHistory::query()
                ->whereKey($historyId)
                ->where(
                    function ($query) use (
                        $request
                    ): void {
                        if ($request->user()) {
                            $query->where(
                                'user_id',
                                $request
                                    ->user()
                                    ->id
                            );
                        } else {
                            $query
                                ->whereNull(
                                    'user_id'
                                )
                                ->where(
                                    'session_id',
                                    $request
                                        ->session()
                                        ->getId()
                                );
                        }
                    }
                )
                ->first();

        if (! $history) {
            return;
        }

        $productId = Product::query()
            ->where(
                'slug',
                $productSlug
            )
            ->where(
                'status',
                true
            )
            ->whereNull('deleted_at')
            ->value('id');

        if (! $productId) {
            return;
        }

        $history->forceFill([
            'clicked_product_id' =>
                (int) $productId,
        ])->save();
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    private function normalizeFilters(
        array $filters
    ): array {
        return collect(
            Arr::only(
                $filters,
                [
                    'category',
                    'brand',
                    'min_price',
                    'max_price',
                    'sort',
                    'per_page',
                ]
            )
        )
            ->reject(
                fn ($value): bool =>
                    $value === null
                    || $value === ''
            )
            ->all();
    }

    private function cameFromProductListing(
        Request $request
    ): bool {
        $referer = (string) $request
            ->headers
            ->get(
                'referer',
                ''
            );

        if ($referer === '') {
            return false;
        }

        $refererPath = rtrim(
            (string) parse_url(
                $referer,
                PHP_URL_PATH
            ),
            '/'
        );

        $productsPath = rtrim(
            (string) parse_url(
                route('products.index'),
                PHP_URL_PATH
            ),
            '/'
        );

        return $refererPath
            === $productsPath;
    }
}
