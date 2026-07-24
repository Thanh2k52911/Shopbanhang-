<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')
            ->orderBy('id')
            ->limit(4)
            ->pluck('id');

        foreach ($userIds as $index => $userId) {
            DB::table('carts')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'status' => 'active',
                ],
                [
                    'session_id' => null,
                    'expires_at' => now()->addDays(30),
                    'created_at' => now()
                        ->subDays($index + 1),
                    'updated_at' => now(),
                ]
            );
        }

        // Giỏ hàng khách chưa đăng nhập
        DB::table('carts')->updateOrInsert(
            [
                'session_id' => 'guest-cart-demo-001',
                'status' => 'active',
            ],
            [
                'user_id' => null,
                'expires_at' => now()->addDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Một giỏ hàng đã được chuyển thành đơn hàng
        if ($userIds->isNotEmpty()) {
            DB::table('carts')->updateOrInsert(
                [
                    'user_id' => $userIds->first(),
                    'status' => 'converted',
                ],
                [
                    'session_id' => null,
                    'expires_at' => null,
                    'created_at' => now()->subDays(10),
                    'updated_at' => now()->subDays(8),
                ]
            );
        }
    }
}
