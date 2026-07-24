<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $createdBy = DB::table('users')
            ->orderBy('id')
            ->value('id');

        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => 'Giảm 10% cho khách hàng mới',
                'description' => 'Áp dụng cho đơn hàng đầu tiên.',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'maximum_discount' => 100000,
                'minimum_order_amount' => 300000,
                'usage_limit' => 500,
                'usage_limit_per_user' => 1,
                'used_count' => 0,
                'first_order_only' => true,
                'is_public' => true,
                'status' => true,
                'start_at' => now()->subDays(1),
                'end_at' => now()->addMonths(3),
            ],
            [
                'code' => 'GIAM50K',
                'name' => 'Giảm 50.000 đồng',
                'description' => 'Giảm trực tiếp 50.000 đồng cho đơn từ 500.000 đồng.',
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'maximum_discount' => null,
                'minimum_order_amount' => 500000,
                'usage_limit' => 300,
                'usage_limit_per_user' => 2,
                'used_count' => 0,
                'first_order_only' => false,
                'is_public' => true,
                'status' => true,
                'start_at' => now()->subDays(2),
                'end_at' => now()->addMonths(2),
            ],
            [
                'code' => 'FREESHIP',
                'name' => 'Miễn phí vận chuyển',
                'description' => 'Miễn phí vận chuyển cho đơn từ 250.000 đồng.',
                'discount_type' => 'free_shipping',
                'discount_value' => 0,
                'maximum_discount' => 30000,
                'minimum_order_amount' => 250000,
                'usage_limit' => 1000,
                'usage_limit_per_user' => 3,
                'used_count' => 0,
                'first_order_only' => false,
                'is_public' => true,
                'status' => true,
                'start_at' => now()->subDays(5),
                'end_at' => now()->addMonths(6),
            ],
            [
                'code' => 'SKINCARE15',
                'name' => 'Giảm 15% sản phẩm chăm sóc da',
                'description' => 'Áp dụng cho nhóm sản phẩm chăm sóc da.',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'maximum_discount' => 150000,
                'minimum_order_amount' => 400000,
                'usage_limit' => 200,
                'usage_limit_per_user' => 1,
                'used_count' => 0,
                'first_order_only' => false,
                'is_public' => true,
                'status' => true,
                'start_at' => now(),
                'end_at' => now()->addDays(45),
            ],
            [
                'code' => 'VIP100K',
                'name' => 'Ưu đãi riêng 100.000 đồng',
                'description' => 'Coupon dành riêng cho một số khách hàng.',
                'discount_type' => 'fixed',
                'discount_value' => 100000,
                'maximum_discount' => null,
                'minimum_order_amount' => 1000000,
                'usage_limit' => 50,
                'usage_limit_per_user' => 1,
                'used_count' => 0,
                'first_order_only' => false,
                'is_public' => false,
                'status' => true,
                'start_at' => now(),
                'end_at' => now()->addMonths(1),
            ],
        ];

        foreach ($coupons as $coupon) {
            DB::table('coupons')->updateOrInsert(
                [
                    'code' => $coupon['code'],
                ],
                array_merge($coupon, [
                    'created_by' => $createdBy,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ])
            );
        }
    }
}
