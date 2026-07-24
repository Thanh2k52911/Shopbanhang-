<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiscountCampaignSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = [
            [
                'name' => 'Ưu đãi chăm sóc da',
                'description' => 'Chương trình giảm giá dành cho các sản phẩm chăm sóc da nổi bật.',
                'start_date' => now()->subDays(3),
                'end_date' => now()->addDays(30),
                'is_flash_sale' => false,
                'status' => true,
            ],
            [
                'name' => 'Flash Sale cuối tuần',
                'description' => 'Chương trình giảm giá nhanh dành cho một số sản phẩm bán chạy.',
                'start_date' => now(),
                'end_date' => now()->addDays(7),
                'is_flash_sale' => true,
                'status' => true,
            ],
            [
                'name' => 'Khuyến mãi mỹ phẩm mùa hè',
                'description' => 'Ưu đãi cho các sản phẩm chống nắng và dưỡng da mùa hè.',
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(45),
                'is_flash_sale' => false,
                'status' => true,
            ],
        ];

        foreach ($campaigns as $campaign) {
            DB::table('discount_campaigns')->updateOrInsert(
                [
                    'name' => $campaign['name'],
                ],
                array_merge($campaign, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
