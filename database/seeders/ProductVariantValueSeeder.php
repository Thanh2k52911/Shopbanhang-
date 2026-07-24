<?php

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\VariantValue;
use App\Models\ProductVariantValue;
use Illuminate\Database\Seeder;

class ProductVariantValueSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'CERAVE-FOAM-236ML' => '236ml',
            'CERAVE-FOAM-473ML' => '473ml',
            'SENKA-WHIP-100G' => '100g',
            'SENKA-WHIP-120G' => '120g',
            'BIODERMA-H2O-250ML' => '250ml',
            'BIODERMA-H2O-500ML' => '500ml',
            'ANESSA-UV-60ML' => '60ml',
            'LRP-ANTHELIOS-50ML' => '50ml',
        ];

        foreach ($data as $sku => $value) {

            $variant = ProductVariant::where('sku', $sku)->first();

            $variantValue = VariantValue::where('value', $value)->first();

            if (!$variant || !$variantValue) {
                continue;
            }

            ProductVariantValue::updateOrCreate(
                [
                    'variant_id' => $variant->id,
                    'value_id' => $variantValue->id,
                ]
            );
        }
    }
}
