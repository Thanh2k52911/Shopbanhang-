<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShipmentStatusHistorySeeder extends Seeder
{
    public function run(): void
    {
        $createdBy = DB::table('users')
            ->orderBy('id')
            ->value('id');

        $histories = [
            'SHP-DEMO-0001' => [
                [
                    'from_status' => null,
                    'to_status' => 'pending',
                    'location' => 'Kho Hà Nội',
                    'description' => 'Đơn hàng đang chờ xử lý vận chuyển.',
                    'source' => 'system',
                    'occurred_at' => now()->subDay(),
                ],
            ],

            'SHP-DEMO-0002' => [
                [
                    'from_status' => null,
                    'to_status' => 'pending',
                    'location' => 'Kho Hà Nội',
                    'description' => 'Đã tạo kiện hàng.',
                    'source' => 'system',
                    'occurred_at' => now()->subDays(2),
                ],
                [
                    'from_status' => 'pending',
                    'to_status' => 'ready_to_ship',
                    'location' => 'Kho Hà Nội',
                    'description' => 'Đã đóng gói và chờ bàn giao cho GHN.',
                    'source' => 'admin',
                    'occurred_at' => now()->subHours(12),
                ],
            ],

            'SHP-DEMO-0003' => [
                [
                    'from_status' => null,
                    'to_status' => 'pending',
                    'location' => 'Kho Hà Nội',
                    'description' => 'Đã tạo kiện hàng.',
                    'source' => 'system',
                    'occurred_at' => now()->subDays(3),
                ],
                [
                    'from_status' => 'pending',
                    'to_status' => 'picked_up',
                    'location' => 'Kho Hà Nội',
                    'description' => 'Viettel Post đã nhận kiện hàng.',
                    'source' => 'provider',
                    'occurred_at' => now()->subHours(10),
                ],
                [
                    'from_status' => 'picked_up',
                    'to_status' => 'in_transit',
                    'location' => 'Trung tâm khai thác Hà Nội',
                    'description' => 'Đơn hàng đang được vận chuyển.',
                    'source' => 'provider',
                    'occurred_at' => now()->subHours(6),
                ],
            ],

            'SHP-DEMO-0004' => [
                [
                    'from_status' => null,
                    'to_status' => 'pending',
                    'location' => 'Kho Hà Nội',
                    'description' => 'Đã tạo kiện hàng.',
                    'source' => 'system',
                    'occurred_at' => now()->subDays(5),
                ],
                [
                    'from_status' => 'pending',
                    'to_status' => 'picked_up',
                    'location' => 'Kho Hà Nội',
                    'description' => 'GHTK đã lấy hàng.',
                    'source' => 'provider',
                    'occurred_at' => now()->subDays(4),
                ],
                [
                    'from_status' => 'picked_up',
                    'to_status' => 'in_transit',
                    'location' => 'Bưu cục Thanh Xuân',
                    'description' => 'Đơn hàng đang trên đường giao.',
                    'source' => 'provider',
                    'occurred_at' => now()->subDays(3),
                ],
                [
                    'from_status' => 'in_transit',
                    'to_status' => 'delivered',
                    'location' => 'Thanh Xuân, Hà Nội',
                    'description' => 'Đơn hàng đã giao thành công.',
                    'source' => 'provider',
                    'occurred_at' => now()->subDays(2),
                ],
            ],
        ];

        foreach ($histories as $shipmentCode => $items) {
            $shipmentId = DB::table('shipments')
                ->where('shipment_code', $shipmentCode)
                ->value('id');

            if (!$shipmentId) {
                $this->command?->warn(
                    "Không tìm thấy shipment: {$shipmentCode}"
                );

                continue;
            }

            foreach ($items as $item) {
                DB::table('shipment_status_histories')->updateOrInsert(
                    [
                        'shipment_id' => $shipmentId,
                        'to_status' => $item['to_status'],
                        'occurred_at' => $item['occurred_at'],
                    ],
                    [
                        'from_status' => $item['from_status'],
                        'location' => $item['location'],
                        'description' => $item['description'],
                        'source' => $item['source'],
                        'created_by' => $createdBy,
                        'provider_data' => $item['source'] === 'provider'
                            ? json_encode([
                                'demo' => true,
                                'shipment_code' => $shipmentCode,
                            ], JSON_UNESCAPED_UNICODE)
                            : null,
                        'created_at' => $item['occurred_at'],
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
