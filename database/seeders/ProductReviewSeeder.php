<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            [
                'product_slug' => 'nuoc-tay-trang-bioderma-sensibio-h2o',
                'user_index' => 0,
                'order_code' => 'ORD-DEMO-0004',
                'rating' => 5,
                'content' => 'Sản phẩm làm sạch tốt, dùng khá dịu da và không gây khô căng.',
                'verified_purchase' => true,
                'status' => true,
            ],
            [
                'product_slug' => 'sua-rua-mat-senka-perfect-whip',
                'user_index' => 1,
                'order_code' => 'ORD-DEMO-0004',
                'rating' => 4,
                'content' => 'Bọt nhiều, làm sạch tốt. Sau khi rửa nên dùng thêm toner và kem dưỡng.',
                'verified_purchase' => true,
                'status' => true,
            ],
            [
                'product_slug' => 'sua-rua-mat-cerave-foaming-facial-cleanser',
                'user_index' => 2,
                'order_code' => null,
                'rating' => 5,
                'content' => 'Kết cấu dễ dùng, phù hợp với da dầu và không có cảm giác quá khô.',
                'verified_purchase' => false,
                'status' => true,
            ],
            [
                'product_slug' => 'kem-chong-nang-la-roche-posay-anthelios',
                'user_index' => 3,
                'order_code' => null,
                'rating' => 4,
                'content' => 'Khả năng kiềm dầu khá ổn, lớp chống nắng ráo nhưng cần tán nhanh.',
                'verified_purchase' => false,
                'status' => true,
            ],
            [
                'product_slug' => 'sua-chong-nang-anessa-perfect-uv-sunscreen',
                'user_index' => 4,
                'order_code' => null,
                'rating' => 5,
                'content' => 'Chống nắng tốt, kết cấu nhẹ và phù hợp khi hoạt động ngoài trời.',
                'verified_purchase' => false,
                'status' => true,
            ],
        ];

        $users = DB::table('users')
            ->orderBy('id')
            ->limit(5)
            ->get()
            ->values();

        if ($users->isEmpty()) {
            $this->command?->warn(
                'Không có user để tạo product reviews.'
            );

            return;
        }

        foreach ($reviews as $review) {
            $productId = DB::table('products')
                ->where('slug', $review['product_slug'])
                ->value('id');

            $user = $users->get($review['user_index']);

            if (!$productId || !$user) {
                $this->command?->warn(
                    "Thiếu product hoặc user: {$review['product_slug']}"
                );

                continue;
            }

            $orderId = null;

            if ($review['order_code']) {
                $orderId = DB::table('orders')
                    ->where('order_code', $review['order_code'])
                    ->value('id');
            }

            DB::table('product_reviews')->updateOrInsert(
                [
                    'product_id' => $productId,
                    'user_id' => $user->id,
                ],
                [
                    'order_id' => $orderId,
                    'rating' => $review['rating'],
                    'content' => $review['content'],
                    'verified_purchase' =>
                        $review['verified_purchase'],
                    'status' => $review['status'],
                    'created_at' => now()
                        ->subDays(5 - $review['user_index']),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
