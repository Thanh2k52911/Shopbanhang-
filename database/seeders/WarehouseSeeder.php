<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $warehouses = [
                [
                    'name' => 'Kho tổng Hà Nội',
                    'address' => 'Nam Từ Liêm, Hà Nội',
                    'status' => true,
                ],
                [
                    'name' => 'Kho Long Biên',
                    'address' => 'Long Biên, Hà Nội',
                    'status' => true,
                ],
                [
                    'name' => 'Kho Thành phố Hồ Chí Minh',
                    'address' => 'Thành phố Thủ Đức, Thành phố Hồ Chí Minh',
                    'status' => true,
                ],
                [
                    'name' => 'Kho Đà Nẵng',
                    'address' => 'Hải Châu, Đà Nẵng',
                    'status' => true,
                ],
            ];

            foreach ($warehouses as $warehouseData) {
                Warehouse::updateOrCreate(
                    [
                        'name' => $warehouseData['name'],
                    ],
                    [
                        'address' => $warehouseData['address'],
                        'status' => $warehouseData['status'],
                    ]
                );
            }
        });
    }
}
