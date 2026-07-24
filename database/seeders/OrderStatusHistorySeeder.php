<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusHistorySeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')
            ->orderBy('id')
            ->value('id');

        $histories = [
            'ORD-DEMO-0001' => [
                [
                    'from_status' => null,
                    'to_status' => 'pending',
                    'note' => 'Đơn hàng được tạo và đang chờ xác nhận.',
                    'source' => 'system',
                    'created_by' => null,
                    'occurred_at' => now()->subDay(),
                ],
            ],

            'ORD-DEMO-0002' => [
                [
                    'from_status' => null,
                    'to_status' => 'pending',
                    'note' => 'Đơn hàng được tạo.',
                    'source' => 'system',
                    'created_by' => null,
                    'occurred_at' => now()->subDays(2),
                ],
                [
                    'from_status' => 'pending',
                    'to_status' => 'confirmed',
                    'note' => 'Nhân viên đã xác nhận đơn hàng.',
                    'source' => 'admin',
                    'created_by' => $adminId,
                    'occurred_at' => now()->subHours(12),
                ],
            ],

            'ORD-DEMO-0003' => [
                [
                    'from_status' => null,
                    'to_status' => 'pending',
                    'note' => 'Đơn hàng được tạo.',
                    'source' => 'system',
                    'created_by' => null,
                    'occurred_at' => now()->subDays(3),
                ],
                [
                    'from_status' => 'pending',
                    'to_status' => 'confirmed',
                    'note' => 'Đơn hàng đã được xác nhận.',
                    'source' => 'admin',
                    'created_by' => $adminId,
                    'occurred_at' => now()->subDays(2),
                ],
                [
                    'from_status' => 'confirmed',
                    'to_status' => 'processing',
                    'note' => 'Kho bắt đầu chuẩn bị sản phẩm.',
                    'source' => 'admin',
                    'created_by' => $adminId,
                    'occurred_at' => now()
                        ->subDays(2)
                        ->addHours(2),
                ],
                [
                    'from_status' => 'processing',
                    'to_status' => 'packed',
                    'note' => 'Đơn hàng đã được đóng gói.',
                    'source' => 'admin',
                    'created_by' => $adminId,
                    'occurred_at' => now()
                        ->subDay()
                        ->addHours(2),
                ],
                [
                    'from_status' => 'packed',
                    'to_status' => 'shipping',
                    'note' => 'Đơn hàng đã bàn giao cho đơn vị vận chuyển.',
                    'source' => 'system',
                    'created_by' => null,
                    'occurred_at' => now()->subHours(10),
                ],
            ],

            'ORD-DEMO-0004' => [
                [
                    'from_status' => null,
                    'to_status' => 'pending',
                    'note' => 'Đơn hàng được tạo.',
                    'source' => 'system',
                    'created_by' => null,
                    'occurred_at' => now()->subDays(7),
                ],
                [
                    'from_status' => 'pending',
                    'to_status' => 'confirmed',
                    'note' => 'Đơn hàng đã được xác nhận.',
                    'source' => 'admin',
                    'created_by' => $adminId,
                    'occurred_at' => now()->subDays(6),
                ],
                [
                    'from_status' => 'confirmed',
                    'to_status' => 'processing',
                    'note' => 'Đơn hàng đang được xử lý.',
                    'source' => 'admin',
                    'created_by' => $adminId,
                    'occurred_at' => now()
                        ->subDays(6)
                        ->addHour(),
                ],
                [
                    'from_status' => 'processing',
                    'to_status' => 'packed',
                    'note' => 'Đơn hàng đã được đóng gói.',
                    'source' => 'admin',
                    'created_by' => $adminId,
                    'occurred_at' => now()->subDays(5),
                ],
                [
                    'from_status' => 'packed',
                    'to_status' => 'shipping',
                    'note' => 'Đơn hàng đang được giao.',
                    'source' => 'system',
                    'created_by' => null,
                    'occurred_at' => now()->subDays(4),
                ],
                [
                    'from_status' => 'shipping',
                    'to_status' => 'completed',
                    'note' => 'Khách hàng đã nhận được sản phẩm.',
                    'source' => 'system',
                    'created_by' => null,
                    'occurred_at' => now()->subDays(2),
                ],
            ],

            'ORD-DEMO-0005' => [
                [
                    'from_status' => null,
                    'to_status' => 'pending',
                    'note' => 'Đơn hàng được tạo.',
                    'source' => 'system',
                    'created_by' => null,
                    'occurred_at' => now()->subDays(2),
                ],
                [
                    'from_status' => 'pending',
                    'to_status' => 'cancelled',
                    'note' => 'Khách hàng yêu cầu hủy đơn.',
                    'source' => 'customer',
                    'created_by' => null,
                    'occurred_at' => now()->subDay(),
                ],
            ],
        ];

        foreach ($histories as $orderCode => $items) {
            $orderId = DB::table('orders')
                ->where('order_code', $orderCode)
                ->value('id');

            if (!$orderId) {
                $this->command?->warn(
                    "Không tìm thấy order: {$orderCode}"
                );

                continue;
            }

            foreach ($items as $item) {
                $exists = DB::table('order_status_histories')
                    ->where('order_id', $orderId)
                    ->where('to_status', $item['to_status'])
                    ->where('occurred_at', $item['occurred_at'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('order_status_histories')->insert([
                    'order_id' => $orderId,
                    'from_status' => $item['from_status'],
                    'to_status' => $item['to_status'],
                    'note' => $item['note'],
                    'source' => $item['source'],
                    'created_by' => $item['created_by'],
                    'occurred_at' => $item['occurred_at'],
                    'created_at' => $item['occurred_at'],
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
