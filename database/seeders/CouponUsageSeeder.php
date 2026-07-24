<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouponUsageSeeder extends Seeder
{
    public function run(): void
    {
        $usages = [
            [
                'coupon_code' => 'WELCOME10',
                'order_code' => 'ORD-DEMO-0001',
                'discount_amount' => 45400,
                'used_at' => now()->subDay(),
            ],
            [
                'coupon_code' => 'GIAM50K',
                'order_code' => 'ORD-DEMO-0002',
                'discount_amount' => 50000,
                'used_at' => now()->subDays(2),
            ],
            [
                'coupon_code' => 'FREESHIP',
                'order_code' => 'ORD-DEMO-0003',
                'discount_amount' => 30000,
                'used_at' => now()->subDays(3),
            ],
        ];

        foreach ($usages as $usage) {
            $couponId = DB::table('coupons')
                ->where('code', $usage['coupon_code'])
                ->value('id');

            $order = DB::table('orders')
                ->where('order_code', $usage['order_code'])
                ->first();

            if (!$couponId || !$order) {
                $this->command?->warn(
                    "Không đủ dữ liệu coupon/order: "
                    . $usage['coupon_code']
                    . ' - '
                    . $usage['order_code']
                );

                continue;
            }

            DB::table('coupon_usages')->updateOrInsert(
                [
                    'coupon_id' => $couponId,
                    'order_id' => $order->id,
                ],
                [
                    'user_id' => $order->user_id,
                    'discount_amount' => $usage['discount_amount'],
                    'used_at' => $usage['used_at'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        /*
         * Đồng bộ used_count theo dữ liệu sử dụng thực tế.
         */
        $couponIds = DB::table('coupon_usages')
            ->distinct()
            ->pluck('coupon_id');

        foreach ($couponIds as $couponId) {
            $usedCount = DB::table('coupon_usages')
                ->where('coupon_id', $couponId)
                ->count();

            DB::table('coupons')
                ->where('id', $couponId)
                ->update([
                    'used_count' => $usedCount,
                    'updated_at' => now(),
                ]);
        }
    }
}
