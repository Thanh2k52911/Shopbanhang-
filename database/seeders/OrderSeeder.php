<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')
            ->orderBy('id')
            ->limit(5)
            ->get();

        $warehouseId = DB::table('warehouses')
            ->where('status', 1)
            ->orderBy('id')
            ->value('id');

        $couponWelcome = DB::table('coupons')
            ->where('code', 'WELCOME10')
            ->value('id');

        $couponFixed = DB::table('coupons')
            ->where('code', 'GIAM50K')
            ->value('id');

        $couponShipping = DB::table('coupons')
            ->where('code', 'FREESHIP')
            ->value('id');

        if ($users->isEmpty()) {
            $this->command?->warn(
                'Không có user để tạo đơn hàng.'
            );

            return;
        }

        $orders = [
            [
                'order_code' => 'ORD-DEMO-0001',
                'user_id' => $users->get(0)?->id,
                'coupon_id' => $couponWelcome,
                'order_status' => 'pending',
                'payment_status' => 'unpaid',
                'shipping_status' => 'pending',
                'payment_method' => 'cod',
                'subtotal' => 690000,
                'product_discount' => 50000,
                'coupon_discount' => 64000,
                'shipping_fee' => 30000,
                'tax_amount' => 0,
                'point_discount' => 0,
                'total_amount' => 606000,
                'total_quantity' => 2,
                'customer_name' => $users->get(0)?->name ?? 'Nguyễn Văn An',
                'customer_email' => $users->get(0)?->email,
                'customer_phone' => '0901000001',
                'customer_note' => 'Giao hàng trong giờ hành chính.',
                'admin_note' => null,
                'cancel_reason' => null,
                'cancelled_by' => null,
                'confirmed_by' => null,
                'confirmed_at' => null,
                'processing_at' => null,
                'packed_at' => null,
                'shipping_at' => null,
                'completed_at' => null,
                'cancelled_at' => null,
                'created_at' => now()->subDays(1),
            ],
            [
                'order_code' => 'ORD-DEMO-0002',
                'user_id' => $users->get(1)?->id,
                'coupon_id' => $couponFixed,
                'order_status' => 'confirmed',
                'payment_status' => 'paid',
                'shipping_status' => 'ready_to_ship',
                'payment_method' => 'bank_transfer',
                'subtotal' => 840000,
                'product_discount' => 60000,
                'coupon_discount' => 50000,
                'shipping_fee' => 25000,
                'tax_amount' => 0,
                'point_discount' => 0,
                'total_amount' => 755000,
                'total_quantity' => 3,
                'customer_name' => $users->get(1)?->name ?? 'Trần Thị Bình',
                'customer_email' => $users->get(1)?->email,
                'customer_phone' => '0901000002',
                'customer_note' => null,
                'admin_note' => 'Khách đã thanh toán chuyển khoản.',
                'cancel_reason' => null,
                'cancelled_by' => null,
                'confirmed_by' => $users->first()->id,
                'confirmed_at' => now()->subHours(12),
                'processing_at' => null,
                'packed_at' => null,
                'shipping_at' => null,
                'completed_at' => null,
                'cancelled_at' => null,
                'created_at' => now()->subDays(2),
            ],
            [
                'order_code' => 'ORD-DEMO-0003',
                'user_id' => $users->get(2)?->id,
                'coupon_id' => $couponShipping,
                'order_status' => 'shipping',
                'payment_status' => 'paid',
                'shipping_status' => 'in_transit',
                'payment_method' => 'vnpay',
                'subtotal' => 1020000,
                'product_discount' => 70000,
                'coupon_discount' => 30000,
                'shipping_fee' => 0,
                'tax_amount' => 0,
                'point_discount' => 20000,
                'total_amount' => 900000,
                'total_quantity' => 4,
                'customer_name' => $users->get(2)?->name ?? 'Lê Minh Châu',
                'customer_email' => $users->get(2)?->email,
                'customer_phone' => '0901000003',
                'customer_note' => 'Gọi trước khi giao.',
                'admin_note' => null,
                'cancel_reason' => null,
                'cancelled_by' => null,
                'confirmed_by' => $users->first()->id,
                'confirmed_at' => now()->subDays(2),
                'processing_at' => now()->subDays(2)->addHours(2),
                'packed_at' => now()->subDays(1)->addHours(2),
                'shipping_at' => now()->subHours(10),
                'completed_at' => null,
                'cancelled_at' => null,
                'created_at' => now()->subDays(3),
            ],
            [
                'order_code' => 'ORD-DEMO-0004',
                'user_id' => $users->get(3)?->id,
                'coupon_id' => null,
                'order_status' => 'completed',
                'payment_status' => 'paid',
                'shipping_status' => 'delivered',
                'payment_method' => 'cod',
                'subtotal' => 560000,
                'product_discount' => 40000,
                'coupon_discount' => 0,
                'shipping_fee' => 25000,
                'tax_amount' => 0,
                'point_discount' => 0,
                'total_amount' => 545000,
                'total_quantity' => 2,
                'customer_name' => $users->get(3)?->name ?? 'Phạm Hoàng Dũng',
                'customer_email' => $users->get(3)?->email,
                'customer_phone' => '0901000004',
                'customer_note' => null,
                'admin_note' => 'Đơn hàng đã hoàn thành.',
                'cancel_reason' => null,
                'cancelled_by' => null,
                'confirmed_by' => $users->first()->id,
                'confirmed_at' => now()->subDays(6),
                'processing_at' => now()->subDays(6)->addHours(1),
                'packed_at' => now()->subDays(5),
                'shipping_at' => now()->subDays(4),
                'completed_at' => now()->subDays(2),
                'cancelled_at' => null,
                'created_at' => now()->subDays(7),
            ],
            [
                'order_code' => 'ORD-DEMO-0005',
                'user_id' => $users->get(4)?->id,
                'coupon_id' => null,
                'order_status' => 'cancelled',
                'payment_status' => 'cancelled',
                'shipping_status' => 'pending',
                'payment_method' => 'cod',
                'subtotal' => 425000,
                'product_discount' => 0,
                'coupon_discount' => 0,
                'shipping_fee' => 30000,
                'tax_amount' => 0,
                'point_discount' => 0,
                'total_amount' => 455000,
                'total_quantity' => 1,
                'customer_name' => $users->get(4)?->name ?? 'Nguyễn Thu Hà',
                'customer_email' => $users->get(4)?->email,
                'customer_phone' => '0901000005',
                'customer_note' => null,
                'admin_note' => null,
                'cancel_reason' => 'Khách hàng đổi ý và yêu cầu hủy đơn.',
                'cancelled_by' => $users->get(4)?->id,
                'confirmed_by' => null,
                'confirmed_at' => null,
                'processing_at' => null,
                'packed_at' => null,
                'shipping_at' => null,
                'completed_at' => null,
                'cancelled_at' => now()->subDays(1),
                'created_at' => now()->subDays(2),
            ],
        ];

        foreach ($orders as $order) {
            DB::table('orders')->updateOrInsert(
                [
                    'order_code' => $order['order_code'],
                ],
                [
                    'user_id' => $order['user_id'],
                    'coupon_id' => $order['coupon_id'],
                    'warehouse_id' => $warehouseId,
                    'order_status' => $order['order_status'],
                    'payment_status' => $order['payment_status'],
                    'shipping_status' => $order['shipping_status'],
                    'payment_method' => $order['payment_method'],
                    'subtotal' => $order['subtotal'],
                    'product_discount' => $order['product_discount'],
                    'coupon_discount' => $order['coupon_discount'],
                    'shipping_fee' => $order['shipping_fee'],
                    'tax_amount' => $order['tax_amount'],
                    'point_discount' => $order['point_discount'],
                    'total_amount' => $order['total_amount'],
                    'total_quantity' => $order['total_quantity'],
                    'customer_name' => $order['customer_name'],
                    'customer_email' => $order['customer_email'],
                    'customer_phone' => $order['customer_phone'],
                    'customer_note' => $order['customer_note'],
                    'admin_note' => $order['admin_note'],
                    'cancel_reason' => $order['cancel_reason'],
                    'cancelled_by' => $order['cancelled_by'],
                    'confirmed_by' => $order['confirmed_by'],
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Laravel Seeder Demo',
                    'confirmed_at' => $order['confirmed_at'],
                    'processing_at' => $order['processing_at'],
                    'packed_at' => $order['packed_at'],
                    'shipping_at' => $order['shipping_at'],
                    'completed_at' => $order['completed_at'],
                    'cancelled_at' => $order['cancelled_at'],
                    'created_at' => $order['created_at'],
                    'updated_at' => now(),
                    'deleted_at' => null,
                ]
            );
        }
    }
}
