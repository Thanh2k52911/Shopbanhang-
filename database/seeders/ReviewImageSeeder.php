<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewImageSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'product_slug' => 'nuoc-tay-trang-bioderma-sensibio-h2o',
                'image_path' => 'reviews/bioderma-review-1.jpg',
            ],
            [
                'product_slug' => 'nuoc-tay-trang-bioderma-sensibio-h2o',
                'image_path' => 'reviews/bioderma-review-2.jpg',
            ],
            [
                'product_slug' => 'sua-rua-mat-senka-perfect-whip',
                'image_path' => 'reviews/senka-review-1.jpg',
            ],
            [
                'product_slug' => 'sua-rua-mat-cerave-foaming-facial-cleanser',
                'image_path' => 'reviews/cerave-review-1.jpg',
            ],
        ];

        foreach ($data as $item) {
            $productId = DB::table('products')
                ->where('slug', $item['product_slug'])
                ->value('id');

            if (!$productId) {
                continue;
            }

            $reviewId = DB::table('product_reviews')
                ->where('product_id', $productId)
                ->orderBy('id')
                ->value('id');

            if (!$reviewId) {
                $this->command?->warn(
                    "Không tìm thấy review: {$item['product_slug']}"
                );

                continue;
            }

            DB::table('review_images')->updateOrInsert(
                [
                    'review_id' => $reviewId,
                    'image_path' => $item['image_path'],
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
