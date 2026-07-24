<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductFavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')
            ->orderBy('id')
            ->limit(5)
            ->pluck('id')
            ->values();

        $productIds = DB::table('products')
            ->where('status', 1)
            ->orderBy('id')
            ->limit(8)
            ->pluck('id')
            ->values();

        if ($userIds->isEmpty() || $productIds->isEmpty()) {
            $this->command?->warn(
                'Không có user hoặc product để tạo dữ liệu yêu thích.'
            );

            return;
        }

        $favorites = [];

        foreach ($userIds as $userIndex => $userId) {
            $firstProductIndex = $userIndex % $productIds->count();

            $favorites[] = [
                'user_id' => $userId,
                'product_id' => $productIds[$firstProductIndex],
            ];

            if ($productIds->count() > 1) {
                $secondProductIndex = (
                    $userIndex + 2
                ) % $productIds->count();

                $favorites[] = [
                    'user_id' => $userId,
                    'product_id' => $productIds[$secondProductIndex],
                ];
            }
        }

        foreach ($favorites as $favorite) {
            DB::table('product_favorites')->updateOrInsert(
                [
                    'user_id' => $favorite['user_id'],
                    'product_id' => $favorite['product_id'],
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
