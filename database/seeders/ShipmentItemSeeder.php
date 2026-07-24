<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShipmentItemSeeder extends Seeder
{
    public function run(): void
    {
        $shipmentCodes = [
            'SHP-DEMO-0001',
            'SHP-DEMO-0002',
            'SHP-DEMO-0003',
            'SHP-DEMO-0004',
        ];

        foreach ($shipmentCodes as $shipmentCode) {
            $shipment = DB::table('shipments')
                ->where('shipment_code', $shipmentCode)
                ->first();

            if (!$shipment) {
                $this->command?->warn(
                    "Không tìm thấy shipment: {$shipmentCode}"
                );

                continue;
            }

            $orderItems = DB::table('order_items')
                ->where('order_id', $shipment->order_id)
                ->get();

            foreach ($orderItems as $orderItem) {
                DB::table('shipment_items')->updateOrInsert(
                    [
                        'shipment_id' => $shipment->id,
                        'order_item_id' => $orderItem->id,
                    ],
                    [
                        'quantity' => $orderItem->quantity,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
