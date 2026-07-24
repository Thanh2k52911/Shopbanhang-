<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReturnRequestImageSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            [
                'return_code' => 'RET-DEMO-0001',
                'sku_code' => 'BIODERMA-H2O-250ML',
                'image_path' => 'returns/ret-demo-0001-1.jpg',
                'caption' => 'Ảnh hộp sản phẩm bị móp.',
                'uploaded_by_type' => 'customer',
                'sort_order' => 1,
            ],
            [
                'return_code' => 'RET-DEMO-0001',
                'sku_code' => 'BIODERMA-H2O-250ML',
                'image_path' => 'returns/ret-demo-0001-2.jpg',
                'caption' => 'Ảnh tem sản phẩm còn nguyên.',
                'uploaded_by_type' => 'customer',
                'sort_order' => 2,
            ],
            [
                'return_code' => 'RET-DEMO-0002',
                'sku_code' => 'SENKA-WHIP-120G',
                'image_path' => 'returns/ret-demo-0002-1.jpg',
                'caption' => 'Ảnh sản phẩm chưa mở.',
                'uploaded_by_type' => 'customer',
                'sort_order' => 1,
            ],
        ];

        foreach ($images as $image) {
            $returnRequest = DB::table('return_requests')
                ->where('return_code', $image['return_code'])
                ->first();

            if (!$returnRequest) {
                continue;
            }

            $orderItem = DB::table('order_items')
                ->where('order_id', $returnRequest->order_id)
                ->where('sku_code', $image['sku_code'])
                ->first();

            $returnRequestItemId = $orderItem
                ? DB::table('return_request_items')
                    ->where(
                        'return_request_id',
                        $returnRequest->id
                    )
                    ->where('order_item_id', $orderItem->id)
                    ->value('id')
                : null;

            $uploadedBy = $returnRequest->user_id;

            DB::table('return_request_images')->updateOrInsert(
                [
                    'return_request_id' => $returnRequest->id,
                    'image_path' => $image['image_path'],
                ],
                [
                    'return_request_item_id' =>
                        $returnRequestItemId,
                    'caption' => $image['caption'],
                    'uploaded_by_type' =>
                        $image['uploaded_by_type'],
                    'uploaded_by' => $uploadedBy,
                    'sort_order' => $image['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
