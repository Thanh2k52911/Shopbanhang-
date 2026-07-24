<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReturnRequestSeeder extends Seeder
{
    public function run(): void
    {
        $requests = [
            [
                'return_code' => 'RET-DEMO-0001',
                'order_code' => 'ORD-DEMO-0004',
                'request_type' => 'return',
                'status' => 'approved',
                'reason' => 'Sản phẩm bị móp hộp khi nhận hàng',
                'description' => 'Khách nhận thấy hộp sản phẩm bị móp và muốn trả lại một sản phẩm.',
                'requested_amount' => 315000,
                'approved_amount' => 315000,
                'return_shipping_fee' => 30000,
                'shipping_fee_payer' => 'shop',
                'customer_note' => 'Sản phẩm chưa sử dụng.',
                'admin_note' => 'Đã xác nhận tình trạng qua hình ảnh.',
                'rejection_reason' => null,
                'approved_at' => now()->subDay(),
                'rejected_at' => null,
                'received_at' => null,
                'completed_at' => null,
                'cancelled_at' => null,
            ],
            [
                'return_code' => 'RET-DEMO-0002',
                'order_code' => 'ORD-DEMO-0004',
                'request_type' => 'refund',
                'status' => 'pending',
                'reason' => 'Sản phẩm không phù hợp với da',
                'description' => 'Khách yêu cầu hoàn tiền cho sản phẩm chưa mở.',
                'requested_amount' => 109000,
                'approved_amount' => 0,
                'return_shipping_fee' => 0,
                'shipping_fee_payer' => 'customer',
                'customer_note' => 'Mong shop hỗ trợ xử lý sớm.',
                'admin_note' => null,
                'rejection_reason' => null,
                'approved_at' => null,
                'rejected_at' => null,
                'received_at' => null,
                'completed_at' => null,
                'cancelled_at' => null,
            ],
        ];

        $processedBy = DB::table('users')
            ->orderBy('id')
            ->value('id');

        foreach ($requests as $request) {
            $order = DB::table('orders')
                ->where('order_code', $request['order_code'])
                ->first();

            if (!$order) {
                $this->command?->warn(
                    "Không tìm thấy đơn hàng: {$request['order_code']}"
                );

                continue;
            }

            DB::table('return_requests')->updateOrInsert(
                [
                    'return_code' => $request['return_code'],
                ],
                [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'request_type' => $request['request_type'],
                    'status' => $request['status'],
                    'reason' => $request['reason'],
                    'description' => $request['description'],
                    'requested_amount' => $request['requested_amount'],
                    'approved_amount' => $request['approved_amount'],
                    'return_shipping_fee' =>
                        $request['return_shipping_fee'],
                    'shipping_fee_payer' =>
                        $request['shipping_fee_payer'],
                    'customer_note' => $request['customer_note'],
                    'admin_note' => $request['admin_note'],
                    'rejection_reason' =>
                        $request['rejection_reason'],
                    'processed_by' =>
                        $request['status'] === 'approved'
                            ? $processedBy
                            : null,
                    'approved_at' => $request['approved_at'],
                    'rejected_at' => $request['rejected_at'],
                    'received_at' => $request['received_at'],
                    'completed_at' => $request['completed_at'],
                    'cancelled_at' => $request['cancelled_at'],
                    'created_at' => now()->subDays(2),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
