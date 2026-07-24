<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoyaltyTierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Thành viên',
                'code' => 'MEMBER',
                'description' => 'Hạng thành viên cơ bản dành cho khách hàng mới.',
                'minimum_spending' => 0,
                'minimum_points' => 0,
                'point_multiplier' => 1,
                'discount_percent' => 0,
                'color' => '#9CA3AF',
                'icon' => 'loyalty/member.png',
                'sort_order' => 1,
                'status' => true,
            ],
            [
                'name' => 'Bạc',
                'code' => 'SILVER',
                'description' => 'Hạng Bạc dành cho khách hàng thường xuyên.',
                'minimum_spending' => 3000000,
                'minimum_points' => 300,
                'point_multiplier' => 1.1,
                'discount_percent' => 2,
                'color' => '#C0C0C0',
                'icon' => 'loyalty/silver.png',
                'sort_order' => 2,
                'status' => true,
            ],
            [
                'name' => 'Vàng',
                'code' => 'GOLD',
                'description' => 'Hạng Vàng dành cho khách hàng thân thiết.',
                'minimum_spending' => 8000000,
                'minimum_points' => 800,
                'point_multiplier' => 1.5,
                'discount_percent' => 5,
                'color' => '#F59E0B',
                'icon' => 'loyalty/gold.png',
                'sort_order' => 3,
                'status' => true,
            ],
            [
                'name' => 'Kim cương',
                'code' => 'DIAMOND',
                'description' => 'Hạng cao nhất dành cho khách hàng VIP.',
                'minimum_spending' => 20000000,
                'minimum_points' => 2000,
                'point_multiplier' => 2,
                'discount_percent' => 10,
                'color' => '#38BDF8',
                'icon' => 'loyalty/diamond.png',
                'sort_order' => 4,
                'status' => true,
            ],
        ];

        foreach ($tiers as $tier) {
            DB::table('loyalty_tiers')->updateOrInsert(
                [
                    'code' => $tier['code'],
                ],
                array_merge($tier, [
                    'created_at' => now(),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ])
            );
        }
    }
}
