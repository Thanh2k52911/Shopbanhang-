<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactMessageSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')
            ->orderBy('id')
            ->limit(4)
            ->get()
            ->values();

        $adminId = DB::table('users')
            ->orderBy('id')
            ->value('id');

        $messages = [
            [
                'contact_code' => 'CT-DEMO-0001',
                'user_index' => 0,
                'name' => 'Nguyễn Văn An',
                'email' => 'an@example.com',
                'phone' => '0901000001',
                'type' => 'order',
                'subject' => 'Kiểm tra trạng thái đơn hàng',
                'message' => 'Cho tôi hỏi đơn ORD-DEMO-0001 khi nào được xác nhận?',
                'order_code' => 'ORD-DEMO-0001',
                'status' => 'new',
                'priority' => 'normal',
                'assigned_to' => null,
                'admin_note' => null,
                'replied_at' => null,
                'closed_at' => null,
            ],
            [
                'contact_code' => 'CT-DEMO-0002',
                'user_index' => 1,
                'name' => 'Trần Thị Bình',
                'email' => 'binh@example.com',
                'phone' => '0901000002',
                'type' => 'payment',
                'subject' => 'Xác nhận thanh toán chuyển khoản',
                'message' => 'Tôi đã chuyển khoản cho đơn ORD-DEMO-0002.',
                'order_code' => 'ORD-DEMO-0002',
                'status' => 'replied',
                'priority' => 'high',
                'assigned_to' => $adminId,
                'admin_note' => 'Đã kiểm tra và xác nhận thanh toán.',
                'replied_at' => now()->subHours(8),
                'closed_at' => null,
            ],
            [
                'contact_code' => 'CT-DEMO-0003',
                'user_index' => 2,
                'name' => 'Lê Minh Châu',
                'email' => 'chau@example.com',
                'phone' => '0901000003',
                'type' => 'shipping',
                'subject' => 'Đơn hàng giao chậm',
                'message' => 'Đơn ORD-DEMO-0003 đang giao nhưng chưa có cập nhật mới.',
                'order_code' => 'ORD-DEMO-0003',
                'status' => 'processing',
                'priority' => 'high',
                'assigned_to' => $adminId,
                'admin_note' => 'Đang liên hệ đơn vị vận chuyển.',
                'replied_at' => null,
                'closed_at' => null,
            ],
            [
                'contact_code' => 'CT-DEMO-0004',
                'user_index' => 3,
                'name' => 'Phạm Hoàng Dũng',
                'email' => 'dung@example.com',
                'phone' => '0901000004',
                'type' => 'return',
                'subject' => 'Yêu cầu hỗ trợ trả hàng',
                'message' => 'Tôi muốn được hướng dẫn quy trình gửi trả sản phẩm.',
                'order_code' => 'ORD-DEMO-0004',
                'status' => 'closed',
                'priority' => 'normal',
                'assigned_to' => $adminId,
                'admin_note' => 'Đã hướng dẫn khách tạo yêu cầu trả hàng.',
                'replied_at' => now()->subDay(),
                'closed_at' => now()->subHours(12),
            ],
            [
                'contact_code' => 'CT-DEMO-0005',
                'user_index' => null,
                'name' => 'Nguyễn Thảo',
                'email' => 'nguyenthao@example.com',
                'phone' => '0901999999',
                'type' => 'product',
                'subject' => 'Tư vấn sản phẩm da nhạy cảm',
                'message' => 'Shop tư vấn giúp tôi sữa rửa mặt phù hợp với da nhạy cảm.',
                'order_code' => null,
                'status' => 'new',
                'priority' => 'normal',
                'assigned_to' => null,
                'admin_note' => null,
                'replied_at' => null,
                'closed_at' => null,
            ],
        ];

        foreach ($messages as $index => $message) {
            $userId = $message['user_index'] !== null
                ? $users->get($message['user_index'])?->id
                : null;

            $orderId = $message['order_code']
                ? DB::table('orders')
                    ->where('order_code', $message['order_code'])
                    ->value('id')
                : null;

            DB::table('contact_messages')->updateOrInsert(
                [
                    'contact_code' => $message['contact_code'],
                ],
                [
                    'user_id' => $userId,
                    'name' => $message['name'],
                    'email' => $message['email'],
                    'phone' => $message['phone'],
                    'type' => $message['type'],
                    'subject' => $message['subject'],
                    'message' => $message['message'],
                    'order_id' => $orderId,
                    'status' => $message['status'],
                    'priority' => $message['priority'],
                    'assigned_to' => $message['assigned_to'],
                    'admin_note' => $message['admin_note'],
                    'replied_at' => $message['replied_at'],
                    'closed_at' => $message['closed_at'],
                    'created_at' => now()->subDays(5 - $index),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
