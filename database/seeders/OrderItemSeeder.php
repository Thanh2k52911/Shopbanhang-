<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'ORD-DEMO-0001' => [
                [
                    'sku_code' => 'CERAVE-FOAM-236ML',
                    'quantity' => 1,
                    'original_price' => 390000,
                    'unit_price' => 345000,
                    'discount_amount' => 45000,
                    'is_reviewed' => false,
                ],
                [
                    'sku_code' => 'SENKA-WHIP-120G',
                    'quantity' => 1,
                    'original_price' => 135000,
                    'unit_price' => 109000,
                    'discount_amount' => 26000,
                    'is_reviewed' => false,
                ],
            ],

            'ORD-DEMO-0002' => [
                [
                    'sku_code' => 'BIODERMA-H2O-250ML',
                    'quantity' => 1,
                    'original_price' => 350000,
                    'unit_price' => 315000,
                    'discount_amount' => 35000,
                    'is_reviewed' => false,
                ],
                [
                    'sku_code' => 'ANESSA-UV-60ML',
                    'quantity' => 1,
                    'original_price' => 550000,
                    'unit_price' => 495000,
                    'discount_amount' => 55000,
                    'is_reviewed' => false,
                ],
                [
                    'sku_code' => 'SENKA-WHIP-100G',
                    'quantity' => 1,
                    'original_price' => 115000,
                    'unit_price' => 89000,
                    'discount_amount' => 26000,
                    'is_reviewed' => false,
                ],
            ],

            'ORD-DEMO-0003' => [
                [
                    'sku_code' => 'CERAVE-FOAM-473ML',
                    'quantity' => 1,
                    'original_price' => 590000,
                    'unit_price' => 525000,
                    'discount_amount' => 65000,
                    'is_reviewed' => false,
                ],
                [
                    'sku_code' => 'LRP-ANTHELIOS-50ML',
                    'quantity' => 1,
                    'original_price' => 480000,
                    'unit_price' => 425000,
                    'discount_amount' => 55000,
                    'is_reviewed' => false,
                ],
                [
                    'sku_code' => 'SENKA-WHIP-100G',
                    'quantity' => 2,
                    'original_price' => 115000,
                    'unit_price' => 89000,
                    'discount_amount' => 26000,
                    'is_reviewed' => false,
                ],
            ],

            'ORD-DEMO-0004' => [
                [
                    'sku_code' => 'BIODERMA-H2O-250ML',
                    'quantity' => 1,
                    'original_price' => 350000,
                    'unit_price' => 315000,
                    'discount_amount' => 35000,
                    'is_reviewed' => true,
                ],
                [
                    'sku_code' => 'SENKA-WHIP-120G',
                    'quantity' => 1,
                    'original_price' => 135000,
                    'unit_price' => 109000,
                    'discount_amount' => 26000,
                    'is_reviewed' => true,
                ],
            ],

            'ORD-DEMO-0005' => [
                [
                    'sku_code' => 'LRP-ANTHELIOS-50ML',
                    'quantity' => 1,
                    'original_price' => 480000,
                    'unit_price' => 425000,
                    'discount_amount' => 55000,
                    'is_reviewed' => false,
                ],
            ],
        ];

        foreach ($data as $orderCode => $items) {
            $orderId = DB::table('orders')
                ->where('order_code', $orderCode)
                ->value('id');

            if (!$orderId) {
                $this->command?->warn(
                    "Không tìm thấy đơn hàng: {$orderCode}"
                );

                continue;
            }

            foreach ($items as $item) {
                $sku = DB::table('product_skus')
                    ->where('sku_code', $item['sku_code'])
                    ->first();

                if (!$sku) {
                    $this->command?->warn(
                        "Không tìm thấy SKU: {$item['sku_code']}"
                    );

                    continue;
                }

                $product = DB::table('products')
                    ->where('id', $sku->product_id)
                    ->first();

                $variant = $sku->variant_id
                    ? DB::table('product_variants')
                        ->where('id', $sku->variant_id)
                        ->first()
                    : null;

                $imagePath = DB::table('product_images')
                    ->where('product_id', $sku->product_id)
                    ->orderByDesc('is_thumbnail')
                    ->orderBy('sort_order')
                    ->value('image_path');

                $totalPrice = $item['unit_price']
                    * $item['quantity'];

                DB::table('order_items')->updateOrInsert(
                    [
                        'order_id' => $orderId,
                        'sku_id' => $sku->id,
                    ],
                    [
                        'product_id' => $sku->product_id,
                        'variant_id' => $sku->variant_id,
                        'product_name' => $product?->name
                            ?? 'Sản phẩm',
                        'product_slug' => $product?->slug,
                        'variant_name' => $variant?->name,
                        'sku_code' => $sku->sku_code,
                        'barcode' => $sku->barcode,
                        'image_path' => $imagePath,
                        'original_price' => $item['original_price'],
                        'unit_price' => $item['unit_price'],
                        'discount_amount' => $item['discount_amount'],
                        'quantity' => $item['quantity'],
                        'total_price' => $totalPrice,
                        'is_reviewed' => $item['is_reviewed'],
                        'returned_quantity' => 0,
                        'refunded_quantity' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        /*
         * Đồng bộ lại tổng tiền và số lượng đơn hàng
         * dựa trên order_items thực tế.
         */
        $orderIds = DB::table('orders')
            ->whereIn('order_code', array_keys($data))
            ->pluck('id');

        foreach ($orderIds as $orderId) {
            $summary = DB::table('order_items')
                ->where('order_id', $orderId)
                ->selectRaw(
                    'COALESCE(SUM(total_price), 0) as subtotal'
                )
                ->selectRaw(
                    'COALESCE(SUM(quantity), 0) as total_quantity'
                )
                ->first();

            $order = DB::table('orders')
                ->where('id', $orderId)
                ->first();

            if (!$order) {
                continue;
            }

            $totalAmount =
                (float) $summary->subtotal
                - (float) $order->coupon_discount
                + (float) $order->shipping_fee
                + (float) $order->tax_amount
                - (float) $order->point_discount;

            DB::table('orders')
                ->where('id', $orderId)
                ->update([
                    'subtotal' => $summary->subtotal,
                    'product_discount' => DB::table('order_items')
                        ->where('order_id', $orderId)
                        ->selectRaw(
                            'COALESCE(SUM(discount_amount * quantity), 0) as total'
                        )
                        ->value('total'),
                    'total_quantity' => $summary->total_quantity,
                    'total_amount' => max($totalAmount, 0),
                    'updated_at' => now(),
                ]);
        }
    }
}
