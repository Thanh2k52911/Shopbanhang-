<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoyaltyTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')
            ->orderBy('id')
            ->value('id');

        $accounts = DB::table('loyalty_accounts')
            ->orderBy('id')
            ->limit(5)
            ->get()
            ->values();

        if ($accounts->isEmpty()) {
            $this->command?->warn(
                'Không có loyalty account để tạo transactions.'
            );

            return;
        }

        $transactions = [
            [
                'account_index' => 0,
                'order_code' => 'ORD-DEMO-0001',
                'type' => 'earn',
                'points' => 60,
                'balance_before' => 60,
                'balance_after' => 120,
                'monetary_value' => 60000,
                'status' => 'completed',
                'reference_type' => 'order',
                'description' => 'Cộng điểm từ đơn hàng ORD-DEMO-0001.',
            ],
            [
                'account_index' => 1,
                'order_code' => 'ORD-DEMO-0002',
                'type' => 'earn',
                'points' => 75,
                'balance_before' => 345,
                'balance_after' => 420,
                'monetary_value' => 75000,
                'status' => 'completed',
                'reference_type' => 'order',
                'description' => 'Cộng điểm từ đơn hàng ORD-DEMO-0002.',
            ],
            [
                'account_index' => 2,
                'order_code' => 'ORD-DEMO-0003',
                'type' => 'redeem',
                'points' => -20,
                'balance_before' => 970,
                'balance_after' => 950,
                'monetary_value' => 20000,
                'status' => 'completed',
                'reference_type' => 'order',
                'description' => 'Sử dụng 20 điểm cho đơn hàng ORD-DEMO-0003.',
            ],
            [
                'account_index' => 3,
                'order_code' => 'ORD-DEMO-0004',
                'type' => 'earn',
                'points' => 55,
                'balance_before' => 25,
                'balance_after' => 80,
                'monetary_value' => 55000,
                'status' => 'completed',
                'reference_type' => 'order',
                'description' => 'Cộng điểm từ đơn hàng ORD-DEMO-0004.',
            ],
            [
                'account_index' => 4,
                'order_code' => null,
                'type' => 'adjust',
                'points' => 100,
                'balance_before' => 2200,
                'balance_after' => 2300,
                'monetary_value' => 100000,
                'status' => 'completed',
                'reference_type' => 'admin_adjustment',
                'description' => 'Admin cộng điểm thưởng tri ân khách hàng VIP.',
            ],
            [
                'account_index' => 0,
                'order_code' => 'ORD-DEMO-0005',
                'type' => 'cancel',
                'points' => -10,
                'balance_before' => 130,
                'balance_after' => 120,
                'monetary_value' => 10000,
                'status' => 'completed',
                'reference_type' => 'order',
                'description' => 'Hủy điểm do đơn hàng ORD-DEMO-0005 bị hủy.',
            ],
        ];

        foreach ($transactions as $index => $transaction) {
            $account = $accounts->get(
                $transaction['account_index']
            );

            if (!$account) {
                continue;
            }

            $orderId = null;

            if ($transaction['order_code']) {
                $orderId = DB::table('orders')
                    ->where(
                        'order_code',
                        $transaction['order_code']
                    )
                    ->value('id');
            }

            $referenceId = $transaction['reference_type'] === 'order'
                ? $orderId
                : $account->user_id;

            $exists = DB::table('loyalty_transactions')
                ->where(
                    'loyalty_account_id',
                    $account->id
                )
                ->where('type', $transaction['type'])
                ->where(
                    'reference_type',
                    $transaction['reference_type']
                )
                ->where('reference_id', $referenceId)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('loyalty_transactions')->insert([
                'loyalty_account_id' => $account->id,
                'order_id' => $orderId,
                'type' => $transaction['type'],
                'points' => $transaction['points'],
                'balance_before' =>
                    $transaction['balance_before'],
                'balance_after' =>
                    $transaction['balance_after'],
                'monetary_value' =>
                    $transaction['monetary_value'],
                'status' => $transaction['status'],
                'reference_type' =>
                    $transaction['reference_type'],
                'reference_id' => $referenceId,
                'description' =>
                    $transaction['description'],
                'available_at' =>
                    $transaction['status'] === 'completed'
                        ? now()->subDays(6 - $index)
                        : now()->addDays(7),
                'expires_at' => now()->addYear(),
                'created_by' =>
                    $transaction['type'] === 'adjust'
                        ? $adminId
                        : null,
                'created_at' => now()->subDays(6 - $index),
                'updated_at' => now(),
            ]);
        }
    }
}
