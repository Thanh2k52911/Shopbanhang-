<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $variants = [
            [
                'product_slug' => 'sua-rua-mat-cerave-foaming-facial-cleanser',
                'name' => 'Dung tích 236ml',
                'sku' => 'CERAVE-FOAM-236ML',
                'price' => 345000,
                'compare_price' => 390000,
                'weight' => 280,
                'status' => 1,
            ],
            [
                'product_slug' => 'sua-rua-mat-cerave-foaming-facial-cleanser',
                'name' => 'Dung tích 473ml',
                'sku' => 'CERAVE-FOAM-473ML',
                'price' => 525000,
                'compare_price' => 590000,
                'weight' => 530,
                'status' => 1,
            ],
            [
                'product_slug' => 'sua-rua-mat-senka-perfect-whip',
                'name' => 'Dung tích 100g',
                'sku' => 'SENKA-WHIP-100G',
                'price' => 89000,
                'compare_price' => 115000,
                'weight' => 120,
                'status' => 1,
            ],
            [
                'product_slug' => 'sua-rua-mat-senka-perfect-whip',
                'name' => 'Dung tích 120g',
                'sku' => 'SENKA-WHIP-120G',
                'price' => 109000,
                'compare_price' => 135000,
                'weight' => 140,
                'status' => 1,
            ],
            [
                'product_slug' => 'nuoc-tay-trang-bioderma-sensibio-h2o',
                'name' => 'Dung tích 250ml',
                'sku' => 'BIODERMA-H2O-250ML',
                'price' => 315000,
                'compare_price' => 350000,
                'weight' => 290,
                'status' => 1,
            ],
            [
                'product_slug' => 'nuoc-tay-trang-bioderma-sensibio-h2o',
                'name' => 'Dung tích 500ml',
                'sku' => 'BIODERMA-H2O-500ML',
                'price' => 475000,
                'compare_price' => 530000,
                'weight' => 550,
                'status' => 1,
            ],
            [
                'product_slug' => 'sua-chong-nang-anessa-perfect-uv-sunscreen',
                'name' => 'Dung tích 60ml',
                'sku' => 'ANESSA-UV-60ML',
                'price' => 495000,
                'compare_price' => 550000,
                'weight' => 90,
                'status' => 1,
            ],
            [
                'product_slug' => 'kem-chong-nang-la-roche-posay-anthelios',
                'name' => 'Dung tích 50ml',
                'sku' => 'LRP-ANTHELIOS-50ML',
                'price' => 425000,
                'compare_price' => 480000,
                'weight' => 80,
                'status' => 1,
            ],
        ];

        foreach ($variants as $variant) {
            $productId = DB::table('products')
                ->where('slug', $variant['product_slug'])
                ->value('id');

            if (!$productId) {
                $this->command?->warn(
                    "Không tìm thấy sản phẩm: {$variant['product_slug']}"
                );

                continue;
            }

            DB::table('product_variants')->updateOrInsert(
                [
                    'sku' => $variant['sku'],
                ],
                [
                    'product_id' => $productId,
                    'name' => $variant['name'],
                    'price' => $variant['price'],
                    'compare_price' => $variant['compare_price'],
                    'weight' => $variant['weight'],
                    'status' => $variant['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
