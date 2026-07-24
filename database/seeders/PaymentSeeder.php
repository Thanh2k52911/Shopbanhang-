<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $payments = [
            [
                'order_code' => 'ORD-DEMO-0001',
                'payment_code' => 'PAY-DEMO-0001',
                'method' => 'cod',
                'status' => 'pending',
                'currency' => 'VND',
                'provider_transaction_id' => null,
                'bank_code' => null,
                'card_type' => null,
                'payment_url' => null,
                'failure_reason' => null,
                'paid_at' => null,
                'expired_at' => now()->addDays(7),
                'cancelled_at' => null,
            ],
            [
                'order_code' => 'ORD-DEMO-0002',
                'payment_code' => 'PAY-DEMO-0002',
                'method' => 'bank_transfer',
                'status' => 'paid',
                'currency' => 'VND',
                'provider_transaction_id' => 'BANK-DEMO-0002',
                'bank_code' => 'MB',
                'card_type' => 'bank_account',
                'payment_url' => null,
                'failure_reason' => null,
                'paid_at' => now()->subDays(2),
                'expired_at' => null,
                'cancelled_at' => null,
            ],
            [
                'order_code' => 'ORD-DEMO-0003',
                'payment_code' => 'PAY-DEMO-0003',
                'method' => 'vnpay',
                'status' => 'paid',
                'currency' => 'VND',
                'provider_transaction_id' => 'VNPAY-DEMO-0003',
                'bank_code' => 'NCB',
                'card_type' => 'ATM',
                'payment_url' => 'https://sandbox.vnpayment.vn/demo',
                'failure_reason' => null,
                'paid_at' => now()->subDays(3),
                'expired_at' => null,
                'cancelled_at' => null,
            ],
            [
                'order_code' => 'ORD-DEMO-0004',
                'payment_code' => 'PAY-DEMO-0004',
                'method' => 'cod',
                'status' => 'paid',
                'currency' => 'VND',
                'provider_transaction_id' => 'COD-DEMO-0004',
                'bank_code' => null,
                'card_type' => null,
                'payment_url' => null,
                'failure_reason' => null,
                'paid_at' => now()->subDays(2),
                'expired_at' => null,
                'cancelled_at' => null,
            ],
            [
                'order_code' => 'ORD-DEMO-0005',
                'payment_code' => 'PAY-DEMO-0005',
                'method' => 'cod',
                'status' => 'cancelled',
                'currency' => 'VND',
                'provider_transaction_id' => null,
                'bank_code' => null,
                'card_type' => null,
                'payment_url' => null,
                'failure_reason' => 'Đơn hàng đã bị khách hủy.',
                'paid_at' => null,
                'expired_at' => null,
                'cancelled_at' => now()->subDay(),
            ],
        ];

        foreach ($payments as $payment) {
            $order = DB::table('orders')
                ->where('order_code', $payment['order_code'])
                ->first();

            if (!$order) {
                $this->command?->warn(
                    "Không tìm thấy đơn hàng: {$payment['order_code']}"
                );

                continue;
            }

            DB::table('payments')->updateOrInsert(
                [
                    'payment_code' => $payment['payment_code'],
                ],
                [
                    'order_id' => $order->id,
                    'method' => $payment['method'],
                    'status' => $payment['status'],
                    'amount' => $order->total_amount,
                    'currency' => $payment['currency'],
                    'provider_transaction_id' =>
                        $payment['provider_transaction_id'],
                    'bank_code' => $payment['bank_code'],
                    'card_type' => $payment['card_type'],
                    'payment_url' => $payment['payment_url'],
                    'failure_reason' => $payment['failure_reason'],
                    'paid_at' => $payment['paid_at'],
                    'expired_at' => $payment['expired_at'],
                    'cancelled_at' => $payment['cancelled_at'],
                    'created_at' => $order->created_at,
                    'updated_at' => now(),
                ]
            );

            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'payment_status' => match (
                        $payment['status']
                    ) {
                        'paid' => 'paid',
                        'cancelled' => 'cancelled',
                        'failed' => 'failed',
                        default => 'unpaid',
                    },
                    'updated_at' => now(),
                ]);
        }
    }
}
