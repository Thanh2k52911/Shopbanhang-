<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Giao hàng tiêu chuẩn',
                'code' => 'STANDARD',
                'provider' => 'internal',
                'description' => 'Phương thức giao hàng tiêu chuẩn trong nội thành và toàn quốc.',
                'base_fee' => 30000,
                'free_shipping_minimum' => 500000,
                'estimated_days_min' => 2,
                'estimated_days_max' => 5,
                'status' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Giao hàng nhanh',
                'code' => 'GHN_EXPRESS',
                'provider' => 'ghn',
                'description' => 'Giao hàng nhanh thông qua đơn vị GHN.',
                'base_fee' => 35000,
                'free_shipping_minimum' => 700000,
                'estimated_days_min' => 1,
                'estimated_days_max' => 3,
                'status' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Giao hàng tiết kiệm',
                'code' => 'GHTK',
                'provider' => 'ghtk',
                'description' => 'Phương thức giao hàng tiết kiệm cho đơn hàng không cần giao gấp.',
                'base_fee' => 25000,
                'free_shipping_minimum' => 600000,
                'estimated_days_min' => 3,
                'estimated_days_max' => 7,
                'status' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Viettel Post',
                'code' => 'VIETTEL_POST',
                'provider' => 'viettel_post',
                'description' => 'Giao hàng toàn quốc qua Viettel Post.',
                'base_fee' => 32000,
                'free_shipping_minimum' => 650000,
                'estimated_days_min' => 2,
                'estimated_days_max' => 6,
                'status' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($methods as $method) {
            DB::table('shipping_methods')->updateOrInsert(
                [
                    'code' => $method['code'],
                ],
                array_merge($method, [
                    'created_at' => now(),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ])
            );
        }
    }
}
