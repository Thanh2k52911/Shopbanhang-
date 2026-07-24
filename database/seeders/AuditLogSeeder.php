<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')
            ->orderBy('id')
            ->value('id');

        $productId = DB::table('products')
            ->where(
                'slug',
                'sua-rua-mat-cerave-foaming-facial-cleanser'
            )
            ->value('id');

        $orderId = DB::table('orders')
            ->where('order_code', 'ORD-DEMO-0002')
            ->value('id');

        $couponId = DB::table('coupons')
            ->where('code', 'WELCOME10')
            ->value('id');

        $logs = [
            [
                'action' => 'created',
                'auditable_type' => 'App\\Models\\Product',
                'auditable_id' => $productId,
                'description' => 'Tạo sản phẩm CeraVe Foaming Facial Cleanser.',
                'old_values' => null,
                'new_values' => [
                    'name' => 'Sữa Rửa Mặt CeraVe Foaming Facial Cleanser',
                    'status' => 1,
                ],
                'route_name' => 'admin.products.store',
                'url' => '/admin/products',
                'request_method' => 'POST',
            ],
            [
                'action' => 'updated',
                'auditable_type' => 'App\\Models\\Product',
                'auditable_id' => $productId,
                'description' => 'Cập nhật trạng thái nổi bật của sản phẩm.',
                'old_values' => [
                    'is_featured' => false,
                ],
                'new_values' => [
                    'is_featured' => true,
                ],
                'route_name' => 'admin.products.update',
                'url' => '/admin/products/' . ($productId ?? 1),
                'request_method' => 'PUT',
            ],
            [
                'action' => 'approved',
                'auditable_type' => 'App\\Models\\Order',
                'auditable_id' => $orderId,
                'description' => 'Xác nhận đơn hàng ORD-DEMO-0002.',
                'old_values' => [
                    'order_status' => 'pending',
                ],
                'new_values' => [
                    'order_status' => 'confirmed',
                ],
                'route_name' => 'admin.orders.confirm',
                'url' => '/admin/orders/' . ($orderId ?? 1) . '/confirm',
                'request_method' => 'POST',
            ],
            [
                'action' => 'updated',
                'auditable_type' => 'App\\Models\\Coupon',
                'auditable_id' => $couponId,
                'description' => 'Cập nhật thời hạn coupon WELCOME10.',
                'old_values' => [
                    'status' => false,
                ],
                'new_values' => [
                    'status' => true,
                ],
                'route_name' => 'admin.coupons.update',
                'url' => '/admin/coupons/' . ($couponId ?? 1),
                'request_method' => 'PUT',
            ],
            [
                'action' => 'login',
                'auditable_type' => 'App\\Models\\User',
                'auditable_id' => $adminId,
                'description' => 'Quản trị viên đăng nhập hệ thống.',
                'old_values' => null,
                'new_values' => null,
                'route_name' => 'login',
                'url' => '/login',
                'request_method' => 'POST',
            ],
        ];

        foreach ($logs as $index => $log) {
            $exists = DB::table('audit_logs')
                ->where('action', $log['action'])
                ->where('auditable_type', $log['auditable_type'])
                ->where('auditable_id', $log['auditable_id'])
                ->where('description', $log['description'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('audit_logs')->insert([
                'user_id' => $adminId,
                'action' => $log['action'],
                'auditable_type' => $log['auditable_type'],
                'auditable_id' => $log['auditable_id'],
                'description' => $log['description'],
                'old_values' => $log['old_values']
                    ? json_encode(
                        $log['old_values'],
                        JSON_UNESCAPED_UNICODE
                    )
                    : null,
                'new_values' => $log['new_values']
                    ? json_encode(
                        $log['new_values'],
                        JSON_UNESCAPED_UNICODE
                    )
                    : null,
                'route_name' => $log['route_name'],
                'url' => $log['url'],
                'request_method' => $log['request_method'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Laravel Seeder Demo',
                'created_at' => now()->subHours(5 - $index),
                'updated_at' => now(),
            ]);
        }
    }
}
