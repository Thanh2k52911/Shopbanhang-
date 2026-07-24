<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SearchHistorySeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')
            ->orderBy('id')
            ->limit(4)
            ->get()
            ->values();

        $products = DB::table('products')
            ->where('status', 1)
            ->orderBy('id')
            ->limit(6)
            ->get()
            ->values();

        if ($products->isEmpty()) {
            $this->command?->warn(
                'Không có product để tạo search histories.'
            );

            return;
        }

        $searches = [
            [
                'user_index' => 0,
                'session_id' => null,
                'keyword' => 'sữa rửa mặt da dầu',
                'filters' => [
                    'skin_type' => 'Da dầu',
                    'brand' => 'CeraVe',
                ],
                'result_count' => 12,
                'product_index' => 0,
            ],
            [
                'user_index' => 1,
                'session_id' => null,
                'keyword' => 'kem chống nắng',
                'filters' => [
                    'origin' => 'Nhật Bản',
                    'price_max' => 600000,
                ],
                'result_count' => 18,
                'product_index' => 3,
            ],
            [
                'user_index' => 2,
                'session_id' => null,
                'keyword' => 'nước tẩy trang da nhạy cảm',
                'filters' => [
                    'skin_type' => 'Da nhạy cảm',
                ],
                'result_count' => 7,
                'product_index' => 2,
            ],
            [
                'user_index' => 3,
                'session_id' => null,
                'keyword' => 'serum niacinamide',
                'filters' => [
                    'ingredient' => 'Niacinamide',
                ],
                'result_count' => 9,
                'product_index' => 5,
            ],
            [
                'user_index' => null,
                'session_id' => 'guest-session-demo-001',
                'keyword' => 'mặt nạ ngủ',
                'filters' => [
                    'origin' => 'Hàn Quốc',
                ],
                'result_count' => 5,
                'product_index' => 4,
            ],
            [
                'user_index' => null,
                'session_id' => 'guest-session-demo-002',
                'keyword' => 'son kem lì',
                'filters' => [
                    'category' => 'Son môi',
                ],
                'result_count' => 11,
                'product_index' => null,
            ],
        ];

        foreach ($searches as $index => $search) {
            $userId = $search['user_index'] !== null
                ? $users->get($search['user_index'])?->id
                : null;

            $clickedProductId = $search['product_index'] !== null
                ? $products->get($search['product_index'])?->id
                : null;

            $exists = DB::table('search_histories')
                ->where('user_id', $userId)
                ->where('session_id', $search['session_id'])
                ->where('keyword', $search['keyword'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('search_histories')->insert([
                'user_id' => $userId,
                'session_id' => $search['session_id'],
                'keyword' => $search['keyword'],
                'filters' => json_encode(
                    $search['filters'],
                    JSON_UNESCAPED_UNICODE
                ),
                'result_count' => $search['result_count'],
                'clicked_product_id' => $clickedProductId,
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subMinutes(
                    60 - ($index * 8)
                ),
                'updated_at' => now(),
            ]);
        }
    }
}
