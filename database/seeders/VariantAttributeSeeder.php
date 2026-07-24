<?php

namespace Database\Seeders;

use App\Models\VariantAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VariantAttributeSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $attributes = [
                'Dung tích',
                'Khối lượng',
                'Màu sắc',
                'Tone màu',
                'Loại da',
                'Mùi hương',
                'Chỉ số SPF',
                'Kích thước',
            ];

            foreach ($attributes as $attributeName) {
                VariantAttribute::updateOrCreate(
                    [
                        'name' => $attributeName,
                    ]
                );
            }
        });
    }
}
