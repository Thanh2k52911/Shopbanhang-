<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewReplySeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')
            ->orderBy('id')
            ->value('id');

        if (!$adminId) {
            $this->command?->warn(
                'Không có user để tạo review replies.'
            );

            return;
        }

        $replies = [
            [
                'product_slug' => 'nuoc-tay-trang-bioderma-sensibio-h2o',
                'content' => 'Cảm ơn bạn đã tin tưởng và đánh giá sản phẩm. Shop rất vui khi sản phẩm phù hợp với làn da của bạn.',
            ],
            [
                'product_slug' => 'sua-rua-mat-senka-perfect-whip',
                'content' => 'Cảm ơn góp ý của bạn. Sau khi rửa mặt, bạn nên sử dụng toner và kem dưỡng để duy trì độ ẩm cho da.',
            ],
            [
                'product_slug' => 'sua-rua-mat-cerave-foaming-facial-cleanser',
                'content' => 'Cảm ơn bạn đã chia sẻ trải nghiệm. CeraVe Foaming Facial Cleanser phù hợp hơn với da thường đến da dầu.',
            ],
            [
                'product_slug' => 'kem-chong-nang-la-roche-posay-anthelios',
                'content' => 'Cảm ơn bạn đã đánh giá. Bạn nên chia kem chống nắng thành từng lượng nhỏ để tán đều và nhanh hơn.',
            ],
        ];

        foreach ($replies as $reply) {
            $productId = DB::table('products')
                ->where('slug', $reply['product_slug'])
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
                    "Không tìm thấy review: {$reply['product_slug']}"
                );

                continue;
            }

            DB::table('review_replies')->updateOrInsert(
                [
                    'review_id' => $reviewId,
                    'user_id' => $adminId,
                ],
                [
                    'content' => $reply['content'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
