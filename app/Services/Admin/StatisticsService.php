<?php

namespace App\Services\Admin;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    /**
     * Lấy toàn bộ dữ liệu thống kê theo khoảng thời gian.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(
        Carbon $dateFrom,
        Carbon $dateTo
    ): array {
        $previousDays = $dateFrom
            ->copy()
            ->startOfDay()
            ->diffInDays(
                $dateTo->copy()->endOfDay()
            ) + 1;

        $previousTo = $dateFrom
            ->copy()
            ->subDay()
            ->endOfDay();

        $previousFrom = $previousTo
            ->copy()
            ->subDays($previousDays - 1)
            ->startOfDay();

        $overview = $this->overview(
            $dateFrom,
            $dateTo
        );

        $previousOverview = $this->overview(
            $previousFrom,
            $previousTo
        );

        return [
            'overview' => $overview,
            'previousOverview' => $previousOverview,
            'changes' => $this->changes(
                $overview,
                $previousOverview
            ),
            'dailyStatistics' => $this->dailyStatistics(
                $dateFrom,
                $dateTo
            ),
            'orderStatusStatistics' => $this->statusStatistics(
                'order_status',
                $dateFrom,
                $dateTo
            ),
            'paymentStatusStatistics' => $this->statusStatistics(
                'payment_status',
                $dateFrom,
                $dateTo
            ),
            'shippingStatusStatistics' => $this->statusStatistics(
                'shipping_status',
                $dateFrom,
                $dateTo
            ),
            'topProducts' => $this->topProducts(
                $dateFrom,
                $dateTo
            ),
            'topCustomers' => $this->topCustomers(
                $dateFrom,
                $dateTo
            ),
            'inventoryStatistics' => $this->inventoryStatistics(),
            'lowStockItems' => $this->lowStockItems(),
            'recentOrders' => $this->recentOrders(
                $dateFrom,
                $dateTo
            ),
            'catalogStatistics' => $this->catalogStatistics(),
        ];
    }

    /**
     * Tổng quan trong một kỳ.
     *
     * @return array<string, int|float>
     */
    private function overview(
        Carbon $dateFrom,
        Carbon $dateTo
    ): array {
        $query = DB::table('orders as o')
            ->whereNull('o.deleted_at')
            ->whereBetween('o.created_at', [
                $dateFrom,
                $dateTo,
            ]);

        return [
            'revenue' => (float) (clone $query)
                ->where('o.order_status', 'completed')
                ->sum('o.total_amount'),

            'orders' => (clone $query)->count(),

            'completed_orders' => (clone $query)
                ->where('o.order_status', 'completed')
                ->count(),

            'cancelled_orders' => (clone $query)
                ->where('o.order_status', 'cancelled')
                ->count(),

            'products_sold' => (int) (clone $query)
                ->where('o.order_status', 'completed')
                ->sum('o.total_quantity'),

            'average_order_value' => (float) (
                (clone $query)
                    ->where('o.order_status', 'completed')
                    ->avg('o.total_amount')
                ?? 0
            ),

            'new_customers' => DB::table('users as u')
                ->whereNull('u.deleted_at')
                ->whereBetween('u.created_at', [
                    $dateFrom,
                    $dateTo,
                ])
                ->whereExists(
                    function (Builder $builder): void {
                        $builder
                            ->selectRaw('1')
                            ->from('user_roles as ur')
                            ->join(
                                'roles as r',
                                'ur.role_id',
                                '=',
                                'r.id'
                            )
                            ->whereColumn(
                                'ur.user_id',
                                'u.id'
                            )
                            ->where('r.name', 'customer');
                    }
                )
                ->count(),

            'paid_orders' => (clone $query)
                ->where('o.payment_status', 'paid')
                ->count(),
        ];
    }

    /**
     * So sánh kỳ hiện tại với kỳ trước.
     *
     * @return array<string, float>
     */
    private function changes(
        array $current,
        array $previous
    ): array {
        return [
            'revenue' => $this->percentageChange(
                $previous['revenue'],
                $current['revenue']
            ),
            'orders' => $this->percentageChange(
                $previous['orders'],
                $current['orders']
            ),
            'completed_orders' => $this->percentageChange(
                $previous['completed_orders'],
                $current['completed_orders']
            ),
            'products_sold' => $this->percentageChange(
                $previous['products_sold'],
                $current['products_sold']
            ),
            'new_customers' => $this->percentageChange(
                $previous['new_customers'],
                $current['new_customers']
            ),
        ];
    }

    private function dailyStatistics(
        Carbon $dateFrom,
        Carbon $dateTo
    ) {
        $rows = DB::table('orders')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [
                $dateFrom,
                $dateTo,
            ])
            ->selectRaw('DATE(created_at) as report_date')
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw(
                "COALESCE(
                    SUM(
                        CASE
                            WHEN order_status = 'completed'
                            THEN total_amount
                            ELSE 0
                        END
                    ),
                    0
                ) as revenue"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(
                        CASE
                            WHEN order_status = 'completed'
                            THEN total_quantity
                            ELSE 0
                        END
                    ),
                    0
                ) as sold_quantity"
            )
            ->groupByRaw('DATE(created_at)')
            ->orderBy('report_date')
            ->get()
            ->keyBy('report_date');

        return collect(
            CarbonPeriod::create(
                $dateFrom->copy()->startOfDay(),
                $dateTo->copy()->startOfDay()
            )
        )
            ->map(function (Carbon $date) use ($rows): array {
                $key = $date->format('Y-m-d');
                $row = $rows->get($key);

                return [
                    'date' => $key,
                    'label' => $date->format('d/m'),
                    'orders' => (int) ($row->total_orders ?? 0),
                    'revenue' => (float) ($row->revenue ?? 0),
                    'sold_quantity' => (int) ($row->sold_quantity ?? 0),
                ];
            })
            ->values();
    }

    private function statusStatistics(
        string $column,
        Carbon $dateFrom,
        Carbon $dateTo
    ) {
        return DB::table('orders')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [
                $dateFrom,
                $dateTo,
            ])
            ->selectRaw(
                $column . ', COUNT(*) as total'
            )
            ->groupBy($column)
            ->orderByDesc('total')
            ->get();
    }

    private function topProducts(
        Carbon $dateFrom,
        Carbon $dateTo
    ) {
        return DB::table('order_items as oi')
            ->join(
                'orders as o',
                'oi.order_id',
                '=',
                'o.id'
            )
            ->leftJoin(
                'products as p',
                'oi.product_id',
                '=',
                'p.id'
            )
            ->leftJoin(
                'product_statistics as ps',
                'ps.product_id',
                '=',
                'oi.product_id'
            )
            ->whereNull('o.deleted_at')
            ->whereNull('p.deleted_at')
            ->where('o.order_status', 'completed')
            ->whereBetween('o.completed_at', [
                $dateFrom,
                $dateTo,
            ])
            ->whereNotNull('oi.product_id')
            ->groupBy(
                'oi.product_id',
                'p.name',
                'p.slug',
                'p.status',
                'p.is_featured',
                'ps.views',
                'ps.favorites'
            )
            ->select([
                'oi.product_id as id',
                'p.name',
                'p.slug',
                'p.status',
                'p.is_featured',
            ])
            ->selectRaw(
                'COALESCE(ps.views, 0) as views'
            )
            ->selectRaw(
                'COALESCE(ps.favorites, 0) as favorites'
            )
            ->selectRaw(
                'COUNT(DISTINCT o.id) as orders'
            )
            ->selectRaw(
                'COALESCE(
                    SUM(
                        oi.quantity
                        - COALESCE(
                            oi.returned_quantity,
                            0
                        )
                    ),
                    0
                ) as sold_quantity'
            )
            ->selectRaw(
                'COALESCE(
                    SUM(
                        CASE
                            WHEN oi.quantity > 0
                            THEN oi.total_price
                                * (
                                    (
                                        oi.quantity
                                        - COALESCE(
                                            oi.returned_quantity,
                                            0
                                        )
                                    )
                                    / oi.quantity
                                )
                            ELSE 0
                        END
                    ),
                    0
                ) as revenue'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('product_images')
                        ->select('image_path')
                        ->whereColumn(
                            'product_images.product_id',
                            'oi.product_id'
                        )
                        ->orderByDesc('is_thumbnail')
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->limit(1);
                },
                'image_path'
            )
            ->orderByDesc('revenue')
            ->orderByDesc('sold_quantity')
            ->limit(10)
            ->get();
    }

    private function topCustomers(
        Carbon $dateFrom,
        Carbon $dateTo
    ) {
        return DB::table('users as u')
            ->join('orders as o', 'o.user_id', '=', 'u.id')
            ->whereNull('u.deleted_at')
            ->whereNull('o.deleted_at')
            ->where('o.order_status', 'completed')
            ->whereBetween('o.created_at', [
                $dateFrom,
                $dateTo,
            ])
            ->whereExists(
                function (Builder $builder): void {
                    $builder
                        ->selectRaw('1')
                        ->from('user_roles as ur')
                        ->join(
                            'roles as r',
                            'ur.role_id',
                            '=',
                            'r.id'
                        )
                        ->whereColumn('ur.user_id', 'u.id')
                        ->where('r.name', 'customer');
                }
            )
            ->groupBy(
                'u.id',
                'u.name',
                'u.email',
                'u.avatar'
            )
            ->select([
                'u.id',
                'u.name',
                'u.email',
                'u.avatar',
            ])
            ->selectRaw('COUNT(o.id) as completed_orders')
            ->selectRaw('COALESCE(SUM(o.total_amount), 0) as spending')
            ->orderByDesc('spending')
            ->limit(10)
            ->get();
    }

    /**
     * @return array<string, int>
     */
    private function inventoryStatistics(): array
    {
        return [
            'quantity' => (int) DB::table('inventories')
                ->sum('quantity'),

            'reserved_quantity' => (int) DB::table('inventories')
                ->sum('reserved_quantity'),

            'available_quantity' => (int) (
                DB::table('inventories')
                    ->selectRaw(
                        'COALESCE(
                            SUM(quantity - reserved_quantity),
                            0
                        ) as total'
                    )
                    ->value('total')
                ?? 0
            ),

            'sold_quantity' => (int) DB::table('inventories')
                ->sum('sold_quantity'),

            'low_stock' => DB::table('inventories')
                ->whereRaw(
                    '(quantity - reserved_quantity) <= minimum_stock'
                )
                ->whereRaw(
                    '(quantity - reserved_quantity) > 0'
                )
                ->count(),

            'out_of_stock' => DB::table('inventories')
                ->whereRaw(
                    '(quantity - reserved_quantity) <= 0'
                )
                ->count(),
        ];
    }

    private function lowStockItems()
    {
        return DB::table('inventories as i')
            ->join('warehouses as w', 'i.warehouse_id', '=', 'w.id')
            ->join('product_skus as sku', 'i.sku_id', '=', 'sku.id')
            ->join('products as p', 'sku.product_id', '=', 'p.id')
            ->leftJoin(
                'product_variants as pv',
                'sku.variant_id',
                '=',
                'pv.id'
            )
            ->whereNull('p.deleted_at')
            ->whereRaw(
                '(i.quantity - i.reserved_quantity) <= i.minimum_stock'
            )
            ->select([
                'i.id',
                'i.quantity',
                'i.reserved_quantity',
                'i.minimum_stock',
                'w.id as warehouse_id',
                'w.name as warehouse_name',
                'sku.sku_code',
                'p.id as product_id',
                'p.name as product_name',
                'pv.name as variant_name',
            ])
            ->selectRaw(
                '(i.quantity - i.reserved_quantity) as available_quantity'
            )
            ->orderBy('available_quantity')
            ->limit(10)
            ->get();
    }

    private function recentOrders(
        Carbon $dateFrom,
        Carbon $dateTo
    ) {
        return DB::table('orders as o')
            ->leftJoin('users as u', 'o.user_id', '=', 'u.id')
            ->whereNull('o.deleted_at')
            ->whereBetween('o.created_at', [
                $dateFrom,
                $dateTo,
            ])
            ->orderByDesc('o.created_at')
            ->limit(10)
            ->get([
                'o.id',
                'o.order_code',
                'o.user_id',
                'o.customer_name',
                'o.customer_email',
                'o.order_status',
                'o.payment_status',
                'o.shipping_status',
                'o.total_amount',
                'o.total_quantity',
                'o.created_at',
                'u.name as user_name',
                'u.email as user_email',
            ]);
    }

    /**
     * @return array<string, int>
     */
    private function catalogStatistics(): array
    {
        return [
            'products' => DB::table('products')
                ->whereNull('deleted_at')
                ->count(),

            'active_products' => DB::table('products')
                ->whereNull('deleted_at')
                ->where('status', true)
                ->count(),

            'categories' => DB::table('categories')
                ->whereNull('deleted_at')
                ->count(),

            'brands' => DB::table('brands')
                ->whereNull('deleted_at')
                ->count(),

            'product_views' => (int) DB::table('product_statistics')
                ->sum('views'),

            'product_favorites' => (int) DB::table('product_statistics')
                ->sum('favorites'),
        ];
    }

    private function percentageChange(
        float|int $previous,
        float|int $current
    ): float {
        if ((float) $previous === 0.0) {
            return (float) $current === 0.0
                ? 0.0
                : 100.0;
        }

        return round(
            (
                (
                    (float) $current
                    - (float) $previous
                )
                / abs((float) $previous)
            ) * 100,
            2
        );
    }
}
