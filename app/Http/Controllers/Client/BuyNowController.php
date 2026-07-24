<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\CouponService;
use App\Support\BuyNow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class BuyNowController extends Controller
{
    public function store(
        Request $request,
        CouponService $couponService
    ): JsonResponse {
        $validated = $request->validate([
            'sku_id' => [
                'nullable',
                'integer',
                'exists:product_skus,id',
                'required_without:product_id',
            ],
            'product_id' => [
                'nullable',
                'integer',
                'exists:products,id',
                'required_without:sku_id',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:99',
            ],
        ]);

        try {
            $cartId = DB::transaction(function () use (
                $request,
                $validated
            ): int {
                $sku = $this->findSku(
                    isset($validated['sku_id'])
                        ? (int) $validated['sku_id']
                        : null,
                    isset($validated['product_id'])
                        ? (int) $validated['product_id']
                        : null
                );

                if (! $sku) {
                    throw ValidationException::withMessages([
                        'product' => 'Sản phẩm hoặc phiên bản này không khả dụng.',
                    ]);
                }

                $quantity = (int) $validated['quantity'];
                $availableQuantity = $this->availableQuantity(
                    (int) $sku->id
                );

                if ($availableQuantity < $quantity) {
                    throw ValidationException::withMessages([
                        'quantity' => $availableQuantity > 0
                            ? "Kho chỉ còn {$availableQuantity} sản phẩm."
                            : 'Sản phẩm hiện đã hết hàng.',
                    ]);
                }

                $this->abandonPreviousBuyNowCart($request);

                $cartId = (int) DB::table('carts')->insertGetId([
                    'user_id' => $request->user()?->id,
                    'session_id' => $request->user()
                        ? null
                        : $request->session()->getId(),
                    'status' => 'buy_now',
                    'expires_at' => now()->addHours(2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('cart_items')->insert([
                    'cart_id' => $cartId,
                    'sku_id' => $sku->id,
                    'quantity' => $quantity,
                    'unit_price' => $sku->price,
                    'discount_amount' => $this->getUnitDiscount(
                        (int) $sku->product_id,
                        (float) $sku->price
                    ),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $request->session()->put(
                    BuyNow::SESSION_CART_KEY,
                    $cartId
                );

                return $cartId;
            });

            /*
             * Coupon đang lưu có thể được tính theo giỏ thường.
             * Xóa để Checkout tự đánh giá và chọn lại theo sản phẩm mua ngay.
             */
            $couponService->remove($request, false);

            return response()->json([
                'success' => true,
                'message' => 'Đang chuyển đến trang thanh toán.',
                'checkout_url' => route('checkout.index'),
                'buy_now_cart_id' => $cartId,
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Không thể mua ngay sản phẩm. Vui lòng thử lại.',
            ], 500);
        }
    }

    private function findSku(
        ?int $skuId,
        ?int $productId
    ): ?object {
        $query = DB::table('product_skus as ps')
            ->join('products as p', 'ps.product_id', '=', 'p.id')
            ->where('ps.status', true)
            ->where('p.status', true)
            ->whereNull('p.deleted_at')
            ->select([
                'ps.id',
                'ps.product_id',
                'ps.price',
            ]);

        if ($skuId !== null) {
            return $query
                ->where('ps.id', $skuId)
                ->first();
        }

        return $query
            ->where('ps.product_id', $productId)
            ->orderBy('ps.price')
            ->orderBy('ps.id')
            ->first();
    }

    private function availableQuantity(int $skuId): int
    {
        return max(
            0,
            (int) DB::table('inventories')
                ->where('sku_id', $skuId)
                ->selectRaw(
                    'COALESCE(SUM(quantity - reserved_quantity), 0) AS available_quantity'
                )
                ->value('available_quantity')
        );
    }

    private function abandonPreviousBuyNowCart(
        Request $request
    ): void {
        $oldCartId = (int) $request->session()->get(
            BuyNow::SESSION_CART_KEY,
            0
        );

        if ($oldCartId < 1) {
            return;
        }

        DB::table('carts')
            ->where('id', $oldCartId)
            ->where('status', 'buy_now')
            ->update([
                'status' => 'abandoned',
                'expires_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function getUnitDiscount(
        int $productId,
        float $originalPrice
    ): float {
        $discount = DB::table('product_discounts as pd')
            ->join(
                'discount_campaigns as dc',
                'pd.campaign_id',
                '=',
                'dc.id'
            )
            ->where('pd.product_id', $productId)
            ->where('dc.status', true)
            ->where('dc.start_date', '<=', now())
            ->where('dc.end_date', '>=', now())
            ->where(function ($query): void {
                $query
                    ->whereNull('pd.limit_quantity')
                    ->orWhereColumn(
                        'pd.sold_quantity',
                        '<',
                        'pd.limit_quantity'
                    );
            })
            ->orderByDesc('dc.is_flash_sale')
            ->orderBy('dc.end_date')
            ->select([
                'pd.discount_percent',
                'pd.discount_amount',
            ])
            ->first();

        if (! $discount) {
            return 0.0;
        }

        if ($discount->discount_percent !== null) {
            return round(
                $originalPrice
                * ((float) $discount->discount_percent / 100),
                2
            );
        }

        return $discount->discount_amount !== null
            ? min($originalPrice, (float) $discount->discount_amount)
            : 0.0;
    }
}
