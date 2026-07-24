<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShipmentSeeder extends Seeder
{
    public function run(): void
    {
        $shipments = [
            [
                'order_code' => 'ORD-DEMO-0001',
                'shipping_method_code' => 'STANDARD',
                'shipment_code' => 'SHP-DEMO-0001',
                'tracking_code' => null,
                'carrier_name' => 'Cosmetic Shop Delivery',
                'service_name' => 'Giao hàng tiêu chuẩn',
                'status' => 'pending',
                'shipping_fee' => 30000,
                'cod_amount' => null,
                'weight' => 400,
                'length' => 20,
                'width' => 15,
                'height' => 10,
                'note' => 'Chờ xác nhận đơn hàng.',
                'provider_data' => null,
                'estimated_delivery_at' => now()->addDays(4),
                'picked_up_at' => null,
                'delivered_at' => null,
                'failed_at' => null,
                'cancelled_at' => null,
            ],
            [
                'order_code' => 'ORD-DEMO-0002',
                'shipping_method_code' => 'GHN_EXPRESS',
                'shipment_code' => 'SHP-DEMO-0002',
                'tracking_code' => 'GHN-DEMO-0002',
                'carrier_name' => 'Giao Hàng Nhanh',
                'service_name' => 'GHN Express',
                'status' => 'ready_to_ship',
                'shipping_fee' => 25000,
                'cod_amount' => 0,
                'weight' => 650,
                'length' => 25,
                'width' => 18,
                'height' => 12,
                'note' => 'Đơn hàng đã đóng gói, chờ bàn giao.',
                'provider_data' => [
                    'service_id' => 53320,
                    'provider' => 'ghn',
                ],
                'estimated_delivery_at' => now()->addDays(2),
                'picked_up_at' => null,
                'delivered_at' => null,
                'failed_at' => null,
                'cancelled_at' => null,
            ],
            [
                'order_code' => 'ORD-DEMO-0003',
                'shipping_method_code' => 'VIETTEL_POST',
                'shipment_code' => 'SHP-DEMO-0003',
                'tracking_code' => 'VTP-DEMO-0003',
                'carrier_name' => 'Viettel Post',
                'service_name' => 'Chuyển phát nhanh',
                'status' => 'in_transit',
                'shipping_fee' => 0,
                'cod_amount' => 0,
                'weight' => 900,
                'length' => 30,
                'width' => 20,
                'height' => 15,
                'note' => 'Đơn hàng đang được vận chuyển.',
                'provider_data' => [
                    'provider' => 'viettel_post',
                    'service' => 'express',
                ],
                'estimated_delivery_at' => now()->addDay(),
                'picked_up_at' => now()->subHours(8),
                'delivered_at' => null,
                'failed_at' => null,
                'cancelled_at' => null,
            ],
            [
                'order_code' => 'ORD-DEMO-0004',
                'shipping_method_code' => 'GHTK',
                'shipment_code' => 'SHP-DEMO-0004',
                'tracking_code' => 'GHTK-DEMO-0004',
                'carrier_name' => 'Giao Hàng Tiết Kiệm',
                'service_name' => 'Giao hàng tiêu chuẩn',
                'status' => 'delivered',
                'shipping_fee' => 25000,
                'cod_amount' => null,
                'weight' => 450,
                'length' => 22,
                'width' => 16,
                'height' => 10,
                'note' => 'Đơn hàng đã giao thành công.',
                'provider_data' => [
                    'provider' => 'ghtk',
                    'delivery_result' => 'success',
                ],
                'estimated_delivery_at' => now()->subDays(2),
                'picked_up_at' => now()->subDays(4),
                'delivered_at' => now()->subDays(2),
                'failed_at' => null,
                'cancelled_at' => null,
            ],
        ];

        foreach ($shipments as $shipment) {
            $order = DB::table('orders')
                ->where('order_code', $shipment['order_code'])
                ->first();

            $shippingMethodId = DB::table('shipping_methods')
                ->where('code', $shipment['shipping_method_code'])
                ->value('id');

            if (!$order || !$shippingMethodId) {
                $this->command?->warn(
                    'Thiếu order hoặc shipping method: '
                    . $shipment['order_code']
                );

                continue;
            }

            $codAmount = $shipment['cod_amount'];

            if ($codAmount === null) {
                $codAmount = $order->payment_method === 'cod'
                    && $order->payment_status !== 'paid'
                        ? $order->total_amount
                        : 0;
            }

            DB::table('shipments')->updateOrInsert(
                [
                    'shipment_code' => $shipment['shipment_code'],
                ],
                [
                    'order_id' => $order->id,
                    'shipping_method_id' => $shippingMethodId,
                    'warehouse_id' => $order->warehouse_id,
                    'tracking_code' => $shipment['tracking_code'],
                    'carrier_name' => $shipment['carrier_name'],
                    'service_name' => $shipment['service_name'],
                    'status' => $shipment['status'],
                    'shipping_fee' => $shipment['shipping_fee'],
                    'cod_amount' => $codAmount,
                    'weight' => $shipment['weight'],
                    'length' => $shipment['length'],
                    'width' => $shipment['width'],
                    'height' => $shipment['height'],
                    'note' => $shipment['note'],
                    'provider_data' => $shipment['provider_data']
                        ? json_encode(
                            $shipment['provider_data'],
                            JSON_UNESCAPED_UNICODE
                        )
                        : null,
                    'estimated_delivery_at' =>
                        $shipment['estimated_delivery_at'],
                    'picked_up_at' => $shipment['picked_up_at'],
                    'delivered_at' => $shipment['delivered_at'],
                    'failed_at' => $shipment['failed_at'],
                    'cancelled_at' => $shipment['cancelled_at'],
                    'created_at' => $order->created_at,
                    'updated_at' => now(),
                ]
            );

            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'shipping_status' => match (
                        $shipment['status']
                    ) {
                        'ready_to_ship' => 'ready_to_ship',
                        'picked_up' => 'picked_up',
                        'in_transit' => 'in_transit',
                        'delivered' => 'delivered',
                        'delivery_failed' => 'failed',
                        'returned' => 'returned',
                        default => 'pending',
                    },
                    'updated_at' => now(),
                ]);
        }
    }
}
