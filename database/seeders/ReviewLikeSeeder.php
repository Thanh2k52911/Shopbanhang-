<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $reviewIds = DB::table('product_reviews')
            ->where('status', true)
            ->orderBy('id')
            ->limit(5)
            ->pluck('id')
            ->values();

        $userIds = DB::table('users')
            ->orderBy('id')
            ->limit(5)
            ->pluck('id')
            ->values();

        if ($reviewIds->isEmpty() || $userIds->isEmpty()) {
            $this->command?->warn(
                'Không đủ review hoặc user để tạo review likes.'
            );

            return;
        }

        $likes = [
            [0, 1],
            [0, 2],
            [0, 3],
            [1, 0],
            [1, 2],
            [2, 0],
            [3, 1],
            [4, 2],
        ];

        foreach ($likes as [$reviewIndex, $userIndex]) {
            $reviewId = $reviewIds->get($reviewIndex);
            $userId = $userIds->get($userIndex);

            if (!$reviewId || !$userId) {
                continue;
            }

            $reviewOwnerId = DB::table('product_reviews')
                ->where('id', $reviewId)
                ->value('user_id');

            /*
             * Không cho user tự thích đánh giá của chính mình.
             */
            if ((int) $reviewOwnerId === (int) $userId) {
                continue;
            }

            DB::table('review_likes')->updateOrInsert(
                [
                    'review_id' => $reviewId,
                    'user_id' => $userId,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
