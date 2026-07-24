<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefundSeeder extends Seeder
{
    public function run(): void
    {
        $processedBy = DB::table('users')
            ->orderBy('id')
            ->value('id');

        $refunds = [
            [
                'refund_code' => 'REF-DEMO-0001',
                'return_code' => 'RET-DEMO-0001',
                'order_code' => 'ORD-DEMO-0004',
                'payment_code' => 'PAY-DEMO-0004',
                'amount' => 315000,
                'method' => 'bank_transfer',
                'status' => 'processing',
                'provider_transaction_id' => null,
                'bank_name' => 'MB Bank',
                'bank_account_number' => '0335667915',
                'bank_account_name' => 'NGUYEN VAN AN',
                'reason' => 'Hoàn tiền sản phẩm bị móp hộp.',
                'admin_note' => 'Đã xác nhận yêu cầu và đang xử lý chuyển khoản.',
                'failure_reason' => null,
                'processed_at' => now(),
                'completed_at' => null,
                'failed_at' => null,
                'cancelled_at' => null,
            ],
        ];

        foreach ($refunds as $refund) {
            $returnRequestId = DB::table('return_requests')
                ->where('return_code', $refund['return_code'])
                ->value('id');

            $orderId = DB::table('orders')
                ->where('order_code', $refund['order_code'])
                ->value('id');

            $paymentId = DB::table('payments')
                ->where('payment_code', $refund['payment_code'])
                ->value('id');

            if (!$orderId) {
                $this->command?->warn(
                    "Không tìm thấy order: {$refund['order_code']}"
                );

                continue;
            }

            DB::table('refunds')->updateOrInsert(
                [
                    'refund_code' => $refund['refund_code'],
                ],
                [
                    'return_request_id' => $returnRequestId,
                    'order_id' => $orderId,
                    'payment_id' => $paymentId,
                    'amount' => $refund['amount'],
                    'method' => $refund['method'],
                    'status' => $refund['status'],
                    'provider_transaction_id' =>
                        $refund['provider_transaction_id'],
                    'bank_name' => $refund['bank_name'],
                    'bank_account_number' =>
                        $refund['bank_account_number'],
                    'bank_account_name' =>
                        $refund['bank_account_name'],
                    'reason' => $refund['reason'],
                    'admin_note' => $refund['admin_note'],
                    'failure_reason' => $refund['failure_reason'],
                    'processed_by' => $processedBy,
                    'processed_at' => $refund['processed_at'],
                    'completed_at' => $refund['completed_at'],
                    'failed_at' => $refund['failed_at'],
                    'cancelled_at' => $refund['cancelled_at'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('orders')
                ->where('id', $orderId)
                ->update([
                    'payment_status' => 'partially_refunded',
                    'updated_at' => now(),
                ]);

            DB::table('payments')
                ->where('id', $paymentId)
                ->update([
                    'status' => 'partially_refunded',
                    'updated_at' => now(),
                ]);

            DB::table('order_items')
                ->where('order_id', $orderId)
                ->where('sku_code', 'BIODERMA-H2O-250ML')
                ->update([
                    'refunded_quantity' => 1,
                    'updated_at' => now(),
                ]);
        }
    }
}
