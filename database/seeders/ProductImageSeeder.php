<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $productImages = [
            'sua-rua-mat-cerave-foaming-facial-cleanser' => [
                'products/cerave-foaming-cleanser-1.jpg',
                'products/cerave-foaming-cleanser-2.jpg',
            ],

            'sua-rua-mat-senka-perfect-whip' => [
                'products/senka-perfect-whip-1.jpg',
                'products/senka-perfect-whip-2.jpg',
            ],

            'nuoc-tay-trang-bioderma-sensibio-h2o' => [
                'products/bioderma-sensibio-1.jpg',
                'products/bioderma-sensibio-2.jpg',
            ],

            'sua-chong-nang-anessa-perfect-uv-sunscreen' => [
                'products/anessa-perfect-uv-1.jpg',
                'products/anessa-perfect-uv-2.jpg',
            ],

            'kem-chong-nang-la-roche-posay-anthelios' => [
                'products/la-roche-posay-anthelios-1.jpg',
                'products/la-roche-posay-anthelios-2.jpg',
            ],

            'serum-the-ordinary-niacinamide-10-zinc-1' => [
                'products/the-ordinary-niacinamide-1.jpg',
                'products/the-ordinary-niacinamide-2.jpg',
            ],

            'kem-duong-am-cerave-moisturizing-cream' => [
                'products/cerave-moisturizing-cream-1.jpg',
                'products/cerave-moisturizing-cream-2.jpg',
            ],

            'mat-na-ngu-laneige-water-sleeping-mask' => [
                'products/laneige-water-sleeping-mask-1.jpg',
                'products/laneige-water-sleeping-mask-2.jpg',
            ],
        ];

        foreach ($productImages as $productSlug => $images) {
            $productId = DB::table('products')
                ->where('slug', $productSlug)
                ->value('id');

            if (!$productId) {
                $this->command?->warn(
                    "Không tìm thấy sản phẩm: {$productSlug}"
                );

                continue;
            }

            foreach ($images as $index => $imagePath) {
                DB::table('product_images')->updateOrInsert(
                    [
                        'product_id' => $productId,
                        'image_path' => $imagePath,
                    ],
                    [
                        'is_thumbnail' => $index === 0,
                        'sort_order' => $index + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
