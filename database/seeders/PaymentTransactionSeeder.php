<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $transactions = [
            [
                'payment_code' => 'PAY-DEMO-0001',
                'type' => 'payment',
                'transaction_id' => null,
                'status' => 'pending',
                'response_code' => null,
                'message' => 'Chờ khách thanh toán khi nhận hàng.',
                'request_data' => [
                    'method' => 'cod',
                ],
                'response_data' => null,
                'processed_at' => null,
            ],
            [
                'payment_code' => 'PAY-DEMO-0002',
                'type' => 'payment',
                'transaction_id' => 'BANK-DEMO-0002',
                'status' => 'paid',
                'response_code' => '00',
                'message' => 'Thanh toán chuyển khoản thành công.',
                'request_data' => [
                    'bank_code' => 'MB',
                    'method' => 'bank_transfer',
                ],
                'response_data' => [
                    'status' => 'success',
                    'transaction_id' => 'BANK-DEMO-0002',
                ],
                'processed_at' => now()->subDays(2),
            ],
            [
                'payment_code' => 'PAY-DEMO-0003',
                'type' => 'payment',
                'transaction_id' => 'VNPAY-DEMO-0003',
                'status' => 'processing',
                'response_code' => null,
                'message' => 'Khởi tạo yêu cầu thanh toán VNPay.',
                'request_data' => [
                    'bank_code' => 'NCB',
                    'method' => 'vnpay',
                ],
                'response_data' => null,
                'processed_at' => now()->subDays(3)->subMinutes(2),
            ],
            [
                'payment_code' => 'PAY-DEMO-0003',
                'type' => 'callback',
                'transaction_id' => 'VNPAY-DEMO-0003',
                'status' => 'paid',
                'response_code' => '00',
                'message' => 'VNPay trả kết quả thanh toán thành công.',
                'request_data' => [
                    'vnp_ResponseCode' => '00',
                ],
                'response_data' => [
                    'verified' => true,
                ],
                'processed_at' => now()->subDays(3),
            ],
            [
                'payment_code' => 'PAY-DEMO-0004',
                'type' => 'payment',
                'transaction_id' => 'COD-DEMO-0004',
                'status' => 'paid',
                'response_code' => 'COD_SUCCESS',
                'message' => 'Thu tiền COD thành công.',
                'request_data' => [
                    'method' => 'cod',
                ],
                'response_data' => [
                    'received' => true,
                ],
                'processed_at' => now()->subDays(2),
            ],
            [
                'payment_code' => 'PAY-DEMO-0005',
                'type' => 'cancel',
                'transaction_id' => null,
                'status' => 'cancelled',
                'response_code' => 'ORDER_CANCELLED',
                'message' => 'Giao dịch bị hủy do đơn hàng đã hủy.',
                'request_data' => [
                    'reason' => 'customer_cancelled',
                ],
                'response_data' => [
                    'cancelled' => true,
                ],
                'processed_at' => now()->subDay(),
            ],
        ];

        foreach ($transactions as $index => $transaction) {
            $payment = DB::table('payments')
                ->where('payment_code', $transaction['payment_code'])
                ->first();

            if (!$payment) {
                $this->command?->warn(
                    "Không tìm thấy payment: {$transaction['payment_code']}"
                );

                continue;
            }

            DB::table('payment_transactions')->updateOrInsert(
                [
                    'payment_id' => $payment->id,
                    'type' => $transaction['type'],
                    'transaction_id' => $transaction['transaction_id'],
                ],
                [
                    'amount' => $payment->amount,
                    'status' => $transaction['status'],
                    'response_code' => $transaction['response_code'],
                    'message' => $transaction['message'],
                    'request_data' => $transaction['request_data']
                        ? json_encode(
                            $transaction['request_data'],
                            JSON_UNESCAPED_UNICODE
                        )
                        : null,
                    'response_data' => $transaction['response_data']
                        ? json_encode(
                            $transaction['response_data'],
                            JSON_UNESCAPED_UNICODE
                        )
                        : null,
                    'ip_address' => '127.0.0.1',
                    'processed_at' => $transaction['processed_at'],
                    'created_at' => now()->subMinutes(
                        count($transactions) - $index
                    ),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
