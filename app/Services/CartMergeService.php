<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CartMergeService
{
    public function mergeGuestCartIntoUser(
        string $guestSessionId,
        int $userId
    ): void {
        DB::transaction(function () use (
            $guestSessionId,
            $userId
        ): void {
            $guestCart = DB::table('carts')
                ->whereNull('user_id')
                ->where('session_id', $guestSessionId)
                ->where('status', 'active')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$guestCart) {
                return;
            }

            $userCart = DB::table('carts')
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            /*
             * Tài khoản chưa có giỏ:
             * chuyển thẳng giỏ khách thành giỏ tài khoản.
             */
            if (!$userCart) {
                DB::table('carts')
                    ->where('id', $guestCart->id)
                    ->update([
                        'user_id' => $userId,
                        'session_id' => null,
                        'expires_at' => now()->addDays(30),
                        'updated_at' => now(),
                    ]);

                return;
            }

            /*
             * Tài khoản đã có giỏ:
             * gộp từng SKU từ giỏ khách vào giỏ tài khoản.
             */
            $guestItems = DB::table('cart_items')
                ->where('cart_id', $guestCart->id)
                ->lockForUpdate()
                ->get();

            foreach ($guestItems as $guestItem) {
                $availableQuantity = (int) DB::table(
                    'inventories'
                )
                    ->where('sku_id', $guestItem->sku_id)
                    ->selectRaw(
                        'COALESCE(
                            SUM(quantity - reserved_quantity),
                            0
                        ) AS available_quantity'
                    )
                    ->value('available_quantity');

                if ($availableQuantity <= 0) {
                    continue;
                }

                $userItem = DB::table('cart_items')
                    ->where('cart_id', $userCart->id)
                    ->where('sku_id', $guestItem->sku_id)
                    ->lockForUpdate()
                    ->first();

                if ($userItem) {
                    $mergedQuantity = min(
                        $availableQuantity,
                        (int) $userItem->quantity
                            + (int) $guestItem->quantity
                    );

                    DB::table('cart_items')
                        ->where('id', $userItem->id)
                        ->update([
                            'quantity' => $mergedQuantity,
                            'unit_price' =>
                                $guestItem->unit_price,
                            'discount_amount' =>
                                $guestItem->discount_amount,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('cart_items')->insert([
                        'cart_id' => $userCart->id,
                        'sku_id' => $guestItem->sku_id,
                        'quantity' => min(
                            $availableQuantity,
                            (int) $guestItem->quantity
                        ),
                        'unit_price' =>
                            $guestItem->unit_price,
                        'discount_amount' =>
                            $guestItem->discount_amount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('cart_items')
                ->where('cart_id', $guestCart->id)
                ->delete();

            DB::table('carts')
                ->where('id', $guestCart->id)
                ->delete();

            DB::table('carts')
                ->where('id', $userCart->id)
                ->update([
                    'expires_at' => now()->addDays(30),
                    'updated_at' => now(),
                ]);
        });
    }
}
