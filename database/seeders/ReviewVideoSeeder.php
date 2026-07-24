<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewVideoSeeder extends Seeder
{
    public function run(): void
    {
        $videos = [
            [
                'product_slug' => 'nuoc-tay-trang-bioderma-sensibio-h2o',
                'video_path' => 'reviews/videos/bioderma-review-1.mp4',
            ],
            [
                'product_slug' => 'sua-rua-mat-senka-perfect-whip',
                'video_path' => 'reviews/videos/senka-review-1.mp4',
            ],
        ];

        foreach ($videos as $video) {
            $productId = DB::table('products')
                ->where('slug', $video['product_slug'])
                ->value('id');

            if (!$productId) {
                $this->command?->warn(
                    "Không tìm thấy sản phẩm: {$video['product_slug']}"
                );

                continue;
            }

            $reviewId = DB::table('product_reviews')
                ->where('product_id', $productId)
                ->orderBy('id')
                ->value('id');

            if (!$reviewId) {
                $this->command?->warn(
                    "Không tìm thấy review: {$video['product_slug']}"
                );

                continue;
            }

            DB::table('review_videos')->updateOrInsert(
                [
                    'review_id' => $reviewId,
                    'video_path' => $video['video_path'],
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
