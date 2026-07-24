<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
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
                'Không có user để tạo notifications.'
            );

            return;
        }

        $notifications = [
            [
                'user_index' => 0,
                'type' => 'App\\Notifications\\OrderCreatedNotification',
                'title' => 'Đơn hàng đã được tạo',
                'message' => 'Đơn hàng ORD-DEMO-0001 của bạn đã được tạo thành công.',
                'category' => 'order',
                'action_url' => '/account/orders/ORD-DEMO-0001',
                'image' => null,
                'priority' => 'normal',
                'data' => [
                    'order_code' => 'ORD-DEMO-0001',
                    'status' => 'pending',
                ],
                'read_at' => null,
            ],
            [
                'user_index' => 1,
                'type' => 'App\\Notifications\\PaymentSuccessNotification',
                'title' => 'Thanh toán thành công',
                'message' => 'Thanh toán cho đơn ORD-DEMO-0002 đã được xác nhận.',
                'category' => 'payment',
                'action_url' => '/account/orders/ORD-DEMO-0002',
                'image' => null,
                'priority' => 'high',
                'data' => [
                    'order_code' => 'ORD-DEMO-0002',
                    'payment_code' => 'PAY-DEMO-0002',
                    'status' => 'paid',
                ],
                'read_at' => now()->subHours(6),
            ],
            [
                'user_index' => 2,
                'type' => 'App\\Notifications\\ShipmentNotification',
                'title' => 'Đơn hàng đang được vận chuyển',
                'message' => 'Đơn ORD-DEMO-0003 đang được Viettel Post vận chuyển.',
                'category' => 'shipping',
                'action_url' => '/account/orders/ORD-DEMO-0003',
                'image' => null,
                'priority' => 'normal',
                'data' => [
                    'order_code' => 'ORD-DEMO-0003',
                    'tracking_code' => 'VTP-DEMO-0003',
                    'status' => 'in_transit',
                ],
                'read_at' => null,
            ],
            [
                'user_index' => 3,
                'type' => 'App\\Notifications\\OrderCompletedNotification',
                'title' => 'Đơn hàng đã giao thành công',
                'message' => 'Đơn ORD-DEMO-0004 đã được giao thành công.',
                'category' => 'order',
                'action_url' => '/account/orders/ORD-DEMO-0004',
                'image' => null,
                'priority' => 'normal',
                'data' => [
                    'order_code' => 'ORD-DEMO-0004',
                    'status' => 'completed',
                ],
                'read_at' => now()->subDay(),
            ],
            [
                'user_index' => 4,
                'type' => 'App\\Notifications\\PromotionNotification',
                'title' => 'Ưu đãi dành riêng cho bạn',
                'message' => 'Sử dụng mã VIP100K để giảm 100.000 đồng cho đơn đủ điều kiện.',
                'category' => 'promotion',
                'action_url' => '/coupons',
                'image' => 'notifications/vip-coupon.jpg',
                'priority' => 'normal',
                'data' => [
                    'coupon_code' => 'VIP100K',
                ],
                'read_at' => null,
            ],
            [
                'user_index' => 0,
                'type' => 'App\\Notifications\\ReturnApprovedNotification',
                'title' => 'Yêu cầu trả hàng đã được duyệt',
                'message' => 'Yêu cầu RET-DEMO-0001 đã được cửa hàng chấp nhận.',
                'category' => 'order',
                'action_url' => '/account/returns/RET-DEMO-0001',
                'image' => null,
                'priority' => 'high',
                'data' => [
                    'return_code' => 'RET-DEMO-0001',
                    'status' => 'approved',
                ],
                'read_at' => null,
            ],
        ];

        foreach ($notifications as $index => $notification) {
            $user = $users->get($notification['user_index']);

            if (!$user) {
                continue;
            }

            $exists = DB::table('notifications')
                ->where('notifiable_type', 'App\\Models\\User')
                ->where('notifiable_id', $user->id)
                ->where('title', $notification['title'])
                ->where('message', $notification['message'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => $notification['type'],
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $user->id,
                'title' => $notification['title'],
                'message' => $notification['message'],
                'category' => $notification['category'],
                'action_url' => $notification['action_url'],
                'image' => $notification['image'],
                'priority' => $notification['priority'],
                'data' => json_encode(
                    $notification['data'],
                    JSON_UNESCAPED_UNICODE
                ),
                'read_at' => $notification['read_at'],
                'created_at' => now()->subHours(6 - $index),
                'updated_at' => now(),
            ]);
        }
    }
}
