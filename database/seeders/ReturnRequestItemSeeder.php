<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReturnRequestItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'return_code' => 'RET-DEMO-0001',
                'sku_code' => 'BIODERMA-H2O-250ML',
                'quantity' => 1,
                'reason' => 'Hộp sản phẩm bị móp',
                'description' => 'Sản phẩm chưa mở nắp và còn nguyên tem.',
                'product_condition' => 'damaged',
                'requested_refund_amount' => 315000,
                'approved_refund_amount' => 315000,
                'inspection_result' => 'approved',
                'inspection_note' => 'Chấp nhận trả hàng.',
                'inventory_action' => 'damaged',
            ],
            [
                'return_code' => 'RET-DEMO-0002',
                'sku_code' => 'SENKA-WHIP-120G',
                'quantity' => 1,
                'reason' => 'Không phù hợp với nhu cầu',
                'description' => 'Khách chưa mở sản phẩm.',
                'product_condition' => 'unopened',
                'requested_refund_amount' => 109000,
                'approved_refund_amount' => 0,
                'inspection_result' => null,
                'inspection_note' => null,
                'inventory_action' => null,
            ],
        ];

        foreach ($items as $item) {
            $returnRequest = DB::table('return_requests')
                ->where('return_code', $item['return_code'])
                ->first();

            if (!$returnRequest) {
                $this->command?->warn(
                    "Không tìm thấy yêu cầu đổi trả: {$item['return_code']}"
                );

                continue;
            }

            $orderItem = DB::table('order_items')
                ->where('order_id', $returnRequest->order_id)
                ->where('sku_code', $item['sku_code'])
                ->first();

            if (!$orderItem) {
                $this->command?->warn(
                    "Không tìm thấy order item: {$item['sku_code']}"
                );

                continue;
            }

            DB::table('return_request_items')->updateOrInsert(
                [
                    'return_request_id' => $returnRequest->id,
                    'order_item_id' => $orderItem->id,
                ],
                [
                    'quantity' => $item['quantity'],
                    'reason' => $item['reason'],
                    'description' => $item['description'],
                    'product_condition' =>
                        $item['product_condition'],
                    'requested_refund_amount' =>
                        $item['requested_refund_amount'],
                    'approved_refund_amount' =>
                        $item['approved_refund_amount'],
                    'inspection_result' =>
                        $item['inspection_result'],
                    'inspection_note' =>
                        $item['inspection_note'],
                    'inventory_action' =>
                        $item['inventory_action'],
                    'created_at' => now()->subDay(),
                    'updated_at' => now(),
                ]
            );

            if (
                $item['approved_refund_amount'] > 0
                && $item['quantity'] > 0
            ) {
                DB::table('order_items')
                    ->where('id', $orderItem->id)
                    ->update([
                        'returned_quantity' => $item['quantity'],
                        'updated_at' => now(),
                    ]);
            }
        }
    }
}
