<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VariantValueSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'Dung tích' => [
                '30ml',
                '50ml',
                '60ml',
                '100ml',
                '120ml',
                '150ml',
                '200ml',
                '236ml',
                '250ml',
                '473ml',
                '500ml',
            ],

            'Khối lượng' => [
                '50g',
                '100g',
                '120g',
                '150g',
                '200g',
            ],

            'Màu sắc' => [
                'Đỏ',
                'Đỏ cam',
                'Hồng',
                'Hồng đất',
                'Cam đất',
                'Nude',
                'Tự nhiên',
            ],
        ];

        foreach ($attributes as $attributeName => $values) {
            $attributeId = DB::table('variant_attributes')
                ->where('name', $attributeName)
                ->value('id');

            if (!$attributeId) {
                $attributeId = DB::table('variant_attributes')->insertGetId([
                    'name' => $attributeName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($values as $value) {
                DB::table('variant_values')->updateOrInsert(
                    [
                        'attribute_id' => $attributeId,
                        'value' => $value,
                    ],
                    [
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        if (!DB::table('variant_values')->exists()) {
            throw new RuntimeException(
                'Không thể tạo dữ liệu cho bảng variant_values.'
            );
        }
    }
}
