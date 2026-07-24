<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')
            ->orderBy('id')
            ->limit(4)
            ->get()
            ->values();

        $questions = [
            [
                'product_slug' => 'sua-rua-mat-cerave-foaming-facial-cleanser',
                'user_index' => 0,
                'guest_name' => null,
                'guest_email' => null,
                'question' => 'Sản phẩm này có phù hợp với da dầu mụn không?',
                'status' => 'answered',
                'is_public' => true,
                'answered_at' => now()->subHours(4),
            ],
            [
                'product_slug' => 'sua-rua-mat-senka-perfect-whip',
                'user_index' => 1,
                'guest_name' => null,
                'guest_email' => null,
                'question' => 'Da khô có sử dụng sản phẩm này hằng ngày được không?',
                'status' => 'answered',
                'is_public' => true,
                'answered_at' => now()->subHours(3),
            ],
            [
                'product_slug' => 'kem-chong-nang-la-roche-posay-anthelios',
                'user_index' => 2,
                'guest_name' => null,
                'guest_email' => null,
                'question' => 'Kem chống nắng có nâng tông da không?',
                'status' => 'answered',
                'is_public' => true,
                'answered_at' => now()->subHours(2),
            ],
            [
                'product_slug' => 'serum-the-ordinary-niacinamide-10-zinc-1',
                'user_index' => 3,
                'guest_name' => null,
                'guest_email' => null,
                'question' => 'Có thể dùng chung sản phẩm này với vitamin C không?',
                'status' => 'published',
                'is_public' => true,
                'answered_at' => null,
            ],
            [
                'product_slug' => 'sua-chong-nang-anessa-perfect-uv-sunscreen',
                'user_index' => null,
                'guest_name' => 'Nguyễn Minh',
                'guest_email' => 'nguyenminh@example.com',
                'question' => 'Sản phẩm có chống nước khi đi biển không?',
                'status' => 'answered',
                'is_public' => true,
                'answered_at' => now()->subHour(),
            ],
        ];

        foreach ($questions as $index => $question) {
            $productId = DB::table('products')
                ->where('slug', $question['product_slug'])
                ->value('id');

            if (!$productId) {
                $this->command?->warn(
                    "Không tìm thấy sản phẩm: {$question['product_slug']}"
                );

                continue;
            }

            $userId = $question['user_index'] !== null
                ? $users->get($question['user_index'])?->id
                : null;

            DB::table('product_questions')->updateOrInsert(
                [
                    'product_id' => $productId,
                    'question' => $question['question'],
                ],
                [
                    'user_id' => $userId,
                    'guest_name' => $question['guest_name'],
                    'guest_email' => $question['guest_email'],
                    'status' => $question['status'],
                    'is_public' => $question['is_public'],
                    'answered_at' => $question['answered_at'],
                    'created_at' => now()->subDays(
                        5 - $index
                    ),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ]
            );
        }
    }
}
