<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductDiscountSeeder extends Seeder
{
    public function run(): void
    {
        $discounts = [
            [
                'campaign_name' => 'Ưu đãi chăm sóc da',
                'product_slug' => 'sua-rua-mat-cerave-foaming-facial-cleanser',
                'discount_percent' => 10,
                'discount_amount' => null,
                'limit_quantity' => 100,
                'sold_quantity' => 12,
            ],
            [
                'campaign_name' => 'Ưu đãi chăm sóc da',
                'product_slug' => 'sua-rua-mat-senka-perfect-whip',
                'discount_percent' => 15,
                'discount_amount' => null,
                'limit_quantity' => 150,
                'sold_quantity' => 35,
            ],
            [
                'campaign_name' => 'Ưu đãi chăm sóc da',
                'product_slug' => 'nuoc-tay-trang-bioderma-sensibio-h2o',
                'discount_percent' => 12,
                'discount_amount' => null,
                'limit_quantity' => 80,
                'sold_quantity' => 18,
            ],
            [
                'campaign_name' => 'Flash Sale cuối tuần',
                'product_slug' => 'serum-the-ordinary-niacinamide-10-zinc-1',
                'discount_percent' => 20,
                'discount_amount' => null,
                'limit_quantity' => 50,
                'sold_quantity' => 24,
            ],
            [
                'campaign_name' => 'Flash Sale cuối tuần',
                'product_slug' => 'kem-duong-am-cerave-moisturizing-cream',
                'discount_percent' => null,
                'discount_amount' => 50000,
                'limit_quantity' => 50,
                'sold_quantity' => 16,
            ],
            [
                'campaign_name' => 'Khuyến mãi mỹ phẩm mùa hè',
                'product_slug' => 'sua-chong-nang-anessa-perfect-uv-sunscreen',
                'discount_percent' => 10,
                'discount_amount' => null,
                'limit_quantity' => 100,
                'sold_quantity' => 28,
            ],
            [
                'campaign_name' => 'Khuyến mãi mỹ phẩm mùa hè',
                'product_slug' => 'kem-chong-nang-la-roche-posay-anthelios',
                'discount_percent' => 15,
                'discount_amount' => null,
                'limit_quantity' => 100,
                'sold_quantity' => 32,
            ],
            [
                'campaign_name' => 'Khuyến mãi mỹ phẩm mùa hè',
                'product_slug' => 'mat-na-ngu-laneige-water-sleeping-mask',
                'discount_percent' => null,
                'discount_amount' => 30000,
                'limit_quantity' => 70,
                'sold_quantity' => 14,
            ],
        ];

        foreach ($discounts as $discount) {
            $campaignId = DB::table('discount_campaigns')
                ->where('name', $discount['campaign_name'])
                ->value('id');

            $productId = DB::table('products')
                ->where('slug', $discount['product_slug'])
                ->value('id');

            if (!$campaignId || !$productId) {
                $this->command?->warn(
                    "Bỏ qua giảm giá: {$discount['product_slug']}"
                );

                continue;
            }

            DB::table('product_discounts')->updateOrInsert(
                [
                    'campaign_id' => $campaignId,
                    'product_id' => $productId,
                ],
                [
                    'discount_percent' => $discount['discount_percent'],
                    'discount_amount' => $discount['discount_amount'],
                    'limit_quantity' => $discount['limit_quantity'],
                    'sold_quantity' => $discount['sold_quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
