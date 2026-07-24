<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecentlyViewedProductSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')
            ->orderBy('id')
            ->limit(4)
            ->pluck('id')
            ->values();

        $productIds = DB::table('products')
            ->where('status', 1)
            ->orderBy('id')
            ->limit(8)
            ->pluck('id')
            ->values();

        if ($productIds->isEmpty()) {
            $this->command?->warn(
                'Không có product để tạo recently viewed products.'
            );

            return;
        }

        foreach ($userIds as $userIndex => $userId) {
            for ($i = 0; $i < 2; $i++) {
                $productIndex = (
                    $userIndex + $i
                ) % $productIds->count();

                $productId = $productIds[$productIndex];

                DB::table('recently_viewed_products')
                    ->updateOrInsert(
                        [
                            'user_id' => $userId,
                            'product_id' => $productId,
                        ],
                        [
                            'session_id' => null,
                            'view_count' => $i + 1,
                            'last_viewed_at' => now()
                                ->subMinutes(
                                    ($userIndex * 20) + ($i * 5)
                                ),
                            'created_at' => now()->subDays(2),
                            'updated_at' => now(),
                        ]
                    );
            }
        }

        /*
         * Lịch sử xem của khách chưa đăng nhập.
         */
        $guestSessionId = 'guest-session-demo-001';

        foreach ($productIds->take(3) as $index => $productId) {
            DB::table('recently_viewed_products')
                ->updateOrInsert(
                    [
                        'session_id' => $guestSessionId,
                        'product_id' => $productId,
                    ],
                    [
                        'user_id' => null,
                        'view_count' => $index + 1,
                        'last_viewed_at' => now()
                            ->subMinutes($index * 10),
                        'created_at' => now()->subDay(),
                        'updated_at' => now(),
                    ]
                );
        }
    }
}
