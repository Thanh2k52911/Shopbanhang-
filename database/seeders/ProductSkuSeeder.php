<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductSku;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSkuSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Tạo SKU cho các sản phẩm có biến thể.
         */
        $variants = ProductVariant::query()->get();

        foreach ($variants as $variant) {
            ProductSku::updateOrCreate(
                [
                    'sku_code' => $variant->sku,
                ],
                [
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'barcode' => $this->barcodeFor(
                        'VARIANT-' . $variant->sku
                    ),
                    'price' => $variant->price,
                    'cost_price' => round(
                        (float) $variant->price * 0.7,
                        2
                    ),
                    'weight' => $variant->weight,
                    'status' => true,
                ]
            );
        }

        /*
         * Tạo SKU cho các sản phẩm nền không có biến thể.
         */
        $standaloneSkus = [
            [
                'product_slug' =>
                    'serum-the-ordinary-niacinamide-10-zinc-1',
                'sku_code' => 'ORDINARY-NIA-30ML',
                'barcode' => '8936000000061',
                'price' => 289000,
                'cost_price' => 210000,
                'weight' => 30,
            ],
            [
                'product_slug' =>
                    'kem-duong-am-cerave-moisturizing-cream',
                'sku_code' => 'CERAVE-CREAM-340G',
                'barcode' => '8936000000085',
                'price' => 399000,
                'cost_price' => 300000,
                'weight' => 340,
            ],
        ];

        foreach ($standaloneSkus as $skuData) {
            $product = Product::query()
                ->where('slug', $skuData['product_slug'])
                ->first();

            if (!$product) {
                $this->command?->warn(
                    "Không tìm thấy sản phẩm: "
                    . $skuData['product_slug']
                );

                continue;
            }

            ProductSku::updateOrCreate(
                [
                    'sku_code' => $skuData['sku_code'],
                ],
                [
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'barcode' => $skuData['barcode'],
                    'price' => $skuData['price'],
                    'cost_price' => $skuData['cost_price'],
                    'weight' => $skuData['weight'],
                    'status' => true,
                ]
            );
        }
    }

    private function barcodeFor(string $value): string
    {
        /*
         * Sinh barcode ổn định để chạy lại seeder không đổi dữ liệu.
         * Không dùng fake()->unique() vì mỗi lần chạy sẽ đổi barcode.
         */
        $digits = preg_replace(
            '/\D/',
            '',
            (string) sprintf(
                '%u',
                crc32($value)
            )
        );

        return '89' . str_pad(
            substr($digits, 0, 11),
            11,
            '0'
        );
    }
}
