<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductStatisticSeeder extends Seeder
{
    public function run(): void
    {
        $statistics = [
            [
                'slug' => 'sua-rua-mat-cerave-foaming-facial-cleanser',
                'views' => 1250,
                'favorites' => 85,
                'orders' => 120,
                'sold_quantity' => 145,
                'revenue' => 50025000,
            ],
            [
                'slug' => 'sua-rua-mat-senka-perfect-whip',
                'views' => 1680,
                'favorites' => 110,
                'orders' => 175,
                'sold_quantity' => 210,
                'revenue' => 20790000,
            ],
            [
                'slug' => 'nuoc-tay-trang-bioderma-sensibio-h2o',
                'views' => 2100,
                'favorites' => 145,
                'orders' => 140,
                'sold_quantity' => 165,
                'revenue' => 55275000,
            ],
            [
                'slug' => 'sua-chong-nang-anessa-perfect-uv-sunscreen',
                'views' => 1820,
                'favorites' => 132,
                'orders' => 105,
                'sold_quantity' => 128,
                'revenue' => 63360000,
            ],
            [
                'slug' => 'kem-chong-nang-la-roche-posay-anthelios',
                'views' => 2380,
                'favorites' => 170,
                'orders' => 165,
                'sold_quantity' => 192,
                'revenue' => 81600000,
            ],
            [
                'slug' => 'serum-the-ordinary-niacinamide-10-zinc-1',
                'views' => 1950,
                'favorites' => 160,
                'orders' => 150,
                'sold_quantity' => 178,
                'revenue' => 53222000,
            ],
            [
                'slug' => 'kem-duong-am-cerave-moisturizing-cream',
                'views' => 1480,
                'favorites' => 96,
                'orders' => 98,
                'sold_quantity' => 115,
                'revenue' => 55185000,
            ],
            [
                'slug' => 'mat-na-ngu-laneige-water-sleeping-mask',
                'views' => 1320,
                'favorites' => 88,
                'orders' => 75,
                'sold_quantity' => 90,
                'revenue' => 33750000,
            ],
        ];

        foreach ($statistics as $statistic) {
            $productId = DB::table('products')
                ->where('slug', $statistic['slug'])
                ->value('id');

            if (!$productId) {
                $this->command?->warn(
                    "Không tìm thấy sản phẩm: {$statistic['slug']}"
                );

                continue;
            }

            DB::table('product_statistics')->updateOrInsert(
                [
                    'product_id' => $productId,
                ],
                [
                    'views' => $statistic['views'],
                    'favorites' => $statistic['favorites'],
                    'orders' => $statistic['orders'],
                    'sold_quantity' => $statistic['sold_quantity'],
                    'revenue' => $statistic['revenue'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('products')
                ->where('id', $productId)
                ->update([
                    'view_count' => $statistic['views'],
                    'updated_at' => now(),
                ]);
        }
    }
}
