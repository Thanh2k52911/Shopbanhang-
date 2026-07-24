<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildProductStatistics extends Command
{
    protected $signature = 'statistics:rebuild-products {--dry-run : Chỉ hiển thị, không cập nhật database}';

    protected $description = 'Tính lại orders, sold_quantity và revenue trong product_statistics từ đơn hoàn thành.';

    public function handle(): int
    {
        $rows = DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->whereNull('o.deleted_at')
            ->where('o.order_status', 'completed')
            ->whereNotNull('oi.product_id')
            ->groupBy('oi.product_id')
            ->selectRaw('oi.product_id')
            ->selectRaw('COUNT(DISTINCT o.id) as orders')
            ->selectRaw('COALESCE(SUM(oi.quantity - oi.returned_quantity), 0) as sold_quantity')
            ->selectRaw(
                'COALESCE(SUM(
                    CASE
                        WHEN oi.quantity > 0
                        THEN oi.total_price * ((oi.quantity - oi.returned_quantity) / oi.quantity)
                        ELSE 0
                    END
                ), 0) as revenue'
            )
            ->get();

        $this->info('Tìm thấy ' . $rows->count() . ' sản phẩm có dữ liệu bán hàng hoàn thành.');

        if ($this->option('dry-run')) {
            $this->table(
                ['product_id', 'orders', 'sold_quantity', 'revenue'],
                $rows->map(fn ($row): array => [
                    $row->product_id,
                    $row->orders,
                    $row->sold_quantity,
                    number_format((float) $row->revenue, 0, ',', '.') . 'đ',
                ])->all()
            );

            $this->warn('Dry-run: chưa cập nhật database.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($rows): void {
            DB::table('product_statistics')->update([
                'orders' => 0,
                'sold_quantity' => 0,
                'revenue' => 0,
                'updated_at' => now(),
            ]);

            foreach ($rows as $row) {
                DB::table('product_statistics')->updateOrInsert(
                    ['product_id' => $row->product_id],
                    [
                        'orders' => (int) $row->orders,
                        'sold_quantity' => (int) $row->sold_quantity,
                        'revenue' => (float) $row->revenue,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }, 3);

        $this->info('Đã tính lại product_statistics thành công.');

        return self::SUCCESS;
    }
}
