<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductVideoSeeder extends Seeder
{
    public function run(): void
    {
        $videos = [
            [
                'product_slug' => 'sua-rua-mat-cerave-foaming-facial-cleanser',
                'title' => 'Giới thiệu CeraVe Foaming Facial Cleanser',
                'video_url' => 'https://www.youtube.com/watch?v=cerave-demo',
                'type' => 'intro',
                'sort_order' => 1,
            ],
            [
                'product_slug' => 'sua-rua-mat-senka-perfect-whip',
                'title' => 'Hướng dẫn sử dụng Senka Perfect Whip',
                'video_url' => 'https://www.youtube.com/watch?v=senka-demo',
                'type' => 'tutorial',
                'sort_order' => 1,
            ],
            [
                'product_slug' => 'nuoc-tay-trang-bioderma-sensibio-h2o',
                'title' => 'Review Bioderma Sensibio H2O',
                'video_url' => 'https://www.youtube.com/watch?v=bioderma-demo',
                'type' => 'review',
                'sort_order' => 1,
            ],
            [
                'product_slug' => 'sua-chong-nang-anessa-perfect-uv-sunscreen',
                'title' => 'Giới thiệu kem chống nắng Anessa',
                'video_url' => 'https://www.youtube.com/watch?v=anessa-demo',
                'type' => 'intro',
                'sort_order' => 1,
            ],
            [
                'product_slug' => 'kem-chong-nang-la-roche-posay-anthelios',
                'title' => 'Hướng dẫn dùng La Roche-Posay Anthelios',
                'video_url' => 'https://www.youtube.com/watch?v=larocheposay-demo',
                'type' => 'tutorial',
                'sort_order' => 1,
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

            DB::table('product_videos')->updateOrInsert(
                [
                    'product_id' => $productId,
                    'video_url' => $video['video_url'],
                ],
                [
                    'title' => $video['title'],
                    'type' => $video['type'],
                    'sort_order' => $video['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
