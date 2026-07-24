<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouponUserSeeder extends Seeder
{
    public function run(): void
    {
        $couponId = DB::table('coupons')
            ->where('code', 'VIP100K')
            ->value('id');

        $userIds = DB::table('users')
            ->orderBy('id')
            ->limit(3)
            ->pluck('id');

        if (!$couponId || $userIds->isEmpty()) {
            $this->command?->warn(
                'Không có coupon VIP100K hoặc không có user.'
            );

            return;
        }

        foreach ($userIds as $userId) {
            DB::table('coupon_users')->updateOrInsert([
                'coupon_id' => $couponId,
                'user_id' => $userId,
            ]);
        }
    }
}
