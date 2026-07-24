<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CartItemSeeder extends Seeder
{
    public function run(): void
    {
        $cartIds = DB::table('carts')
            ->where('status', 'active')
            ->orderBy('id')
            ->limit(5)
            ->pluck('id')
            ->values();

        $skus = DB::table('product_skus')
            ->where('status', 1)
            ->orderBy('id')
            ->limit(8)
            ->get([
                'id',
                'price',
            ])
            ->values();

        if ($cartIds->isEmpty() || $skus->isEmpty()) {
            $this->command?->warn(
                'Không có cart hoặc product_skus để tạo cart_items.'
            );

            return;
        }

        foreach ($cartIds as $cartIndex => $cartId) {
            $firstSku = $skus[
                $cartIndex % $skus->count()
            ];

            DB::table('cart_items')->updateOrInsert(
                [
                    'cart_id' => $cartId,
                    'sku_id' => $firstSku->id,
                ],
                [
                    'quantity' => ($cartIndex % 3) + 1,
                    'unit_price' => $firstSku->price,
                    'discount_amount' => $cartIndex % 2 === 0
                        ? 10000
                        : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            if ($skus->count() > 1) {
                $secondSku = $skus[
                    ($cartIndex + 3) % $skus->count()
                ];

                DB::table('cart_items')->updateOrInsert(
                    [
                        'cart_id' => $cartId,
                        'sku_id' => $secondSku->id,
                    ],
                    [
                        'quantity' => 1,
                        'unit_price' => $secondSku->price,
                        'discount_amount' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
