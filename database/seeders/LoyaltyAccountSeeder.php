<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoyaltyAccountSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')
            ->orderBy('id')
            ->limit(5)
            ->get()
            ->values();

        if ($users->isEmpty()) {
            $this->command?->warn(
                'Không có user để tạo loyalty accounts.'
            );

            return;
        }

        $accounts = [
            [
                'tier_code' => 'MEMBER',
                'available_points' => 120,
                'pending_points' => 20,
                'lifetime_earned_points' => 150,
                'lifetime_redeemed_points' => 30,
                'lifetime_spending' => 1500000,
            ],
            [
                'tier_code' => 'SILVER',
                'available_points' => 420,
                'pending_points' => 50,
                'lifetime_earned_points' => 520,
                'lifetime_redeemed_points' => 100,
                'lifetime_spending' => 4500000,
            ],
            [
                'tier_code' => 'GOLD',
                'available_points' => 950,
                'pending_points' => 80,
                'lifetime_earned_points' => 1250,
                'lifetime_redeemed_points' => 300,
                'lifetime_spending' => 12000000,
            ],
            [
                'tier_code' => 'MEMBER',
                'available_points' => 80,
                'pending_points' => 0,
                'lifetime_earned_points' => 80,
                'lifetime_redeemed_points' => 0,
                'lifetime_spending' => 545000,
            ],
            [
                'tier_code' => 'DIAMOND',
                'available_points' => 2300,
                'pending_points' => 150,
                'lifetime_earned_points' => 3200,
                'lifetime_redeemed_points' => 900,
                'lifetime_spending' => 28000000,
            ],
        ];

        foreach ($accounts as $index => $account) {
            $user = $users->get($index);

            if (!$user) {
                continue;
            }

            $tierId = DB::table('loyalty_tiers')
                ->where('code', $account['tier_code'])
                ->value('id');

            DB::table('loyalty_accounts')->updateOrInsert(
                [
                    'user_id' => $user->id,
                ],
                [
                    'tier_id' => $tierId,
                    'available_points' => $account['available_points'],
                    'pending_points' => $account['pending_points'],
                    'lifetime_earned_points' =>
                        $account['lifetime_earned_points'],
                    'lifetime_redeemed_points' =>
                        $account['lifetime_redeemed_points'],
                    'lifetime_spending' =>
                        $account['lifetime_spending'],
                    'tier_started_at' => now()->subMonths(
                        max(1, $index + 1)
                    ),
                    'tier_expires_at' => now()->addYear(),
                    'created_at' => now()->subMonths(
                        max(1, $index + 2)
                    ),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
