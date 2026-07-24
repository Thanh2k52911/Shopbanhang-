<?php

namespace App\Services;

use App\Http\Requests\Client\StoreCheckoutRequest;
use App\Support\BuyNow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Services\CouponService;
use App\Services\Admin\NotificationService;

class OrderService
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {
    }
    public function create(
        StoreCheckoutRequest $request
    ): object {
        $order = DB::transaction(function () use ($request): object {
            $cart = $this->findActiveCart($request);

            if (!$cart) {
                throw ValidationException::withMessages([
                    'cart' => 'Giỏ hàng của bạn không tồn tại.',
                ]);
            }

            $cartItems = $this->getCartItems(
                (int) $cart->id
            );

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Giỏ hàng của bạn đang trống.',
                ]);
            }

            $shippingMethod = DB::table('shipping_methods')
                ->where(
                    'id',
                    (int) $request->validated(
                        'shipping_method_id'
                    )
                )
                ->where('status', true)
                ->whereNull('deleted_at')
                ->first();

            if (!$shippingMethod) {
                throw ValidationException::withMessages([
                    'shipping_method_id' =>
                        'Phương thức vận chuyển không khả dụng.',
                ]);
            }

            /*
             * Chọn một kho có khả năng đáp ứng toàn bộ đơn hàng.
             */
            $warehouse = $this->findFulfillmentWarehouse(
                $cartItems
            );

            if (!$warehouse) {
                throw ValidationException::withMessages([
                    'inventory' =>
                        'Hiện không có kho nào đủ hàng cho toàn bộ đơn này.',
                ]);
            }

            /*
             * Khóa các bản ghi tồn kho để tránh hai đơn cùng mua
             * vượt quá số lượng hiện có.
             */
            $inventories = DB::table('inventories')
                ->where(
                    'warehouse_id',
                    $warehouse->id
                )
                ->whereIn(
                    'sku_id',
                    $cartItems->pluck('sku_id')
                )
                ->lockForUpdate()
                ->get()
                ->keyBy('sku_id');

            foreach ($cartItems as $item) {
                $inventory = $inventories->get($item->sku_id);

                $availableQuantity = $inventory
                    ? (int) $inventory->quantity
                        - (int) $inventory->reserved_quantity
                    : 0;

                if (
                    !$inventory
                    || $availableQuantity < (int) $item->quantity
                ) {
                    throw ValidationException::withMessages([
                        'inventory' =>
                            "Sản phẩm {$item->product_name} không còn đủ hàng.",
                    ]);
                }
            }

            $subtotal = 0.0;
            $productDiscount = 0.0;
            $totalQuantity = 0;

            foreach ($cartItems as $item) {
                $quantity = (int) $item->quantity;
                // Luôn lấy giá SKU hiện tại từ database khi checkout.
                // Không dùng giá snapshot cũ trong cart_items.
                $originalPrice = (float) $item->current_price;

                /*
                 * Tính lại khuyến mãi ở backend.
                 * Không tin discount_amount đang lưu trong giỏ.
                 */
                $unitDiscount = $this->calculateUnitDiscount(
                    (int) $item->product_id,
                    $originalPrice
                );

                $item->original_price = $originalPrice;
                $item->discount_amount = $unitDiscount;
                $item->final_unit_price = max(
                    0,
                    $originalPrice - $unitDiscount
                );
                $item->total_price =
                    $item->final_unit_price * $quantity;

                $subtotal += $originalPrice * $quantity;
                $productDiscount += $unitDiscount * $quantity;
                $totalQuantity += $quantity;
            }

            $productTotal = max(
                0,
                $subtotal - $productDiscount
            );

            $shippingFee = (float) $shippingMethod->base_fee;

            if (
                $shippingMethod->free_shipping_minimum !== null
                && $productTotal >=
                    (float) $shippingMethod
                        ->free_shipping_minimum
            ) {
                $shippingFee = 0;
            }

           $coupon = null;
$couponDiscount = 0.0;
$hasFreeShippingCoupon = false;

$checkoutCoupon = $request
    ->session()
    ->get(CouponService::SESSION_KEY);

if (
    is_array($checkoutCoupon)
    && isset($checkoutCoupon['coupon_id'])
) {
    $coupon = Coupon::query()
        ->where(
            'id',
            (int) $checkoutCoupon['coupon_id']
        )
        ->where('status', true)
        ->lockForUpdate()
        ->first();

    if ($coupon) {
        $couponDiscount = min(
            $productTotal,
            max(
                0,
                (float) (
                    $checkoutCoupon[
                        'discount_amount'
                    ] ?? 0
                )
            )
        );

        $hasFreeShippingCoupon = (bool) (
            $checkoutCoupon['free_shipping']
            ?? false
        );

        if ($hasFreeShippingCoupon) {
            $shippingFee = 0;
        }
    }
}

$taxAmount = 0.0;
$pointDiscount = 0.0;

            $totalAmount = max(
                0,
                $productTotal
                - $couponDiscount
                - $pointDiscount
                + $shippingFee
                + $taxAmount
            );

            $orderId = DB::table('orders')->insertGetId([
                'order_code' => $this->generateUniqueCode(
                    'orders',
                    'order_code',
                    'ORD'
                ),
                'user_id' => auth()->id(),
                'coupon_id' => $coupon?->id,
                'warehouse_id' => $warehouse->id,
                'shipping_method_id' => $shippingMethod->id,

                'order_status' => 'pending',
                'payment_status' => 'unpaid',
                'shipping_status' => 'pending',
                'payment_method' =>
                    $request->validated('payment_method'),

                'subtotal' => $subtotal,
                'product_discount' => $productDiscount,
                'coupon_discount' => $couponDiscount,
                'shipping_fee' => $shippingFee,
                'tax_amount' => $taxAmount,
                'point_discount' => $pointDiscount,
                'total_amount' => $totalAmount,
                'total_quantity' => $totalQuantity,

                'customer_name' =>
                    $request->validated('name'),
                'customer_email' =>
                    $request->validated('email'),
                'customer_phone' =>
                    $request->validated('phone'),
                'customer_note' =>
                    $request->validated('note'),

                'ip_address' => $request->ip(),
                'user_agent' =>
                    Str::limit(
                        (string) $request->userAgent(),
                        65000,
                        ''
                    ),

                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $order = DB::table('orders')
                ->where('id', $orderId)
                ->first();
            /*
|--------------------------------------------------------------------------
| Lưu lịch sử sử dụng coupon
|--------------------------------------------------------------------------
*/

if ($coupon) {

    CouponUsage::query()->create([

        'coupon_id' => $coupon->id,

        'order_id' => $orderId,

        'user_id' => auth()->id(),

        'discount_amount'
            => $couponDiscount,

        'created_at' => now(),

        'updated_at' => now(),
    ]);

    $coupon->increment(
        'used_count'
    );

    $request
    ->session()
    ->forget(CouponService::SESSION_KEY);
}

            $fullAddress = collect([
                $request->validated('address'),
                $request->validated('ward'),
                $request->validated('district'),
                $request->validated('province'),
            ])
                ->filter()
                ->implode(', ');

            DB::table('order_addresses')->insert([
                'order_id' => $orderId,
                'type' => 'shipping',
                'receiver_name' =>
                    $request->validated('name'),
                'phone' =>
                    $request->validated('phone'),
                'email' =>
                    $request->validated('email'),
                'province' =>
                    $request->validated('province'),
                'district' =>
                    $request->validated('district'),
                'ward' =>
                    $request->validated('ward'),
                'address' =>
                    $request->validated('address'),
                'full_address' => $fullAddress,
                'note' =>
                    $request->validated('note'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($cartItems as $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'sku_id' => $item->sku_id,

                    'product_name' => $item->product_name,
                    'product_slug' => $item->product_slug,
                    'variant_name' => $item->variant_name,
                    'sku_code' => $item->sku_code,
                    'barcode' => $item->barcode,
                    'image_path' => $item->image_path,

                    'original_price' =>
                        $item->original_price,
                    'unit_price' =>
                        $item->final_unit_price,
                    'discount_amount' =>
                        $item->discount_amount,
                    'quantity' => $item->quantity,
                    'total_price' =>
                        $item->total_price,

                    'is_reviewed' => false,
                    'returned_quantity' => 0,
                    'refunded_quantity' => 0,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                /*
                 * Giữ hàng cho đơn pending.
                 * Chưa xuất kho và chưa tăng sold_quantity.
                 */
                DB::table('inventories')
                    ->where(
                        'warehouse_id',
                        $warehouse->id
                    )
                    ->where('sku_id', $item->sku_id)
                    ->increment(
                        'reserved_quantity',
                        (int) $item->quantity,
                        ['updated_at' => now()]
                    );
            }

            $paymentId = DB::table('payments')
                ->insertGetId([
                    'order_id' => $orderId,
                    'payment_code' =>
                        $this->generateUniqueCode(
                            'payments',
                            'payment_code',
                            'PAY'
                        ),
                    'method' =>
                        $request->validated(
                            'payment_method'
                        ),
                    'status' => 'pending',
                    'amount' => $totalAmount,
                    'currency' => 'VND',
                    'provider_transaction_id' => null,
                    'bank_code' => null,
                    'card_type' => null,
                    'payment_url' => null,
                    'failure_reason' => null,
                    'paid_at' => null,
                    'expired_at' => null,
                    'cancelled_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            /*
             * Với COD chưa có giao dịch thanh toán thực tế,
             * vì vậy chưa tạo payment_transactions.
             */

            DB::table('order_status_histories')->insert([
                'order_id' => $orderId,
                'from_status' => null,
                'to_status' => 'pending',
                'status_type' => 'order',
                'note' => 'Khách hàng đã tạo đơn hàng.',
                'source' => 'customer',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
             * Chuyển giỏ thành converted để lưu lịch sử.
             * Không xóa cart_items.
             */
            DB::table('carts')
                ->where('id', $cart->id)
                ->update([
                    'status' => 'converted',
                    'expires_at' => null,
                    'updated_at' => now(),
                ]);

            if ($cart->status === 'buy_now') {
                $request->session()->forget(
                    BuyNow::SESSION_CART_KEY
                );
            }

            return $order;
        }, 3);

        $this->notificationService->safely(function () use ($order): void {
            $this->notificationService->notifyNewOrder(
                (int) $order->id,
                (string) $order->order_code,
                (string) ($order->customer_name ?? 'Khách hàng'),
                (float) $order->total_amount,
                [
                    'user_id' => $order->user_id,
                    'payment_status' => $order->payment_status,
                    'shipping_status' => $order->shipping_status,
                ]
            );
        });

        return $order;
    }

    private function findActiveCart(
        StoreCheckoutRequest $request
    ): ?object {
        $buyNowCartId = (int) $request->session()->get(
            BuyNow::SESSION_CART_KEY,
            0
        );

        if ($buyNowCartId > 0) {
            $buyNowQuery = DB::table('carts')
                ->where('id', $buyNowCartId)
                ->where('status', 'buy_now');

            if (auth()->check()) {
                $buyNowQuery->where(
                    'user_id',
                    auth()->id()
                );
            } else {
                $buyNowQuery
                    ->whereNull('user_id')
                    ->where(
                        'session_id',
                        $request->session()->getId()
                    );
            }

            $buyNowCart = $buyNowQuery
                ->lockForUpdate()
                ->first();

            if ($buyNowCart) {
                return $buyNowCart;
            }

            $request->session()->forget(
                BuyNow::SESSION_CART_KEY
            );
        }

        $query = DB::table('carts')
            ->where('status', 'active');

        if (auth()->check()) {
            $query->where(
                'user_id',
                auth()->id()
            );
        } else {
            $query
                ->whereNull('user_id')
                ->where(
                    'session_id',
                    $request->session()->getId()
                );
        }

        return $query
            ->latest('id')
            ->lockForUpdate()
            ->first();
    }

    private function getCartItems(int $cartId)
    {
        return DB::table('cart_items as ci')
            ->join(
                'product_skus as ps',
                'ci.sku_id',
                '=',
                'ps.id'
            )
            ->join(
                'products as p',
                'ps.product_id',
                '=',
                'p.id'
            )
            ->leftJoin(
                'product_variants as pv',
                'ps.variant_id',
                '=',
                'pv.id'
            )
            ->where('ci.cart_id', $cartId)
            ->where('ps.status', true)
            ->where('p.status', true)
            ->whereNull('p.deleted_at')
            ->select([
                'ci.id as cart_item_id',
                'ci.quantity',
                'ci.unit_price as cart_unit_price',

                'ps.id as sku_id',
                'ps.product_id',
                'ps.variant_id',
                'ps.sku_code',
                'ps.barcode',
                'ps.price as current_price',

                'p.name as product_name',
                'p.slug as product_slug',

                'pv.name as variant_name',
            ])
            ->selectSub(function ($query): void {
                $query
                    ->from('product_images')
                    ->select('image_path')
                    ->whereColumn(
                        'product_images.product_id',
                        'p.id'
                    )
                    ->orderByDesc('is_thumbnail')
                    ->orderBy('sort_order')
                    ->limit(1);
            }, 'image_path')
            ->orderBy('ci.id')
            ->lockForUpdate()
            ->get();
    }

    private function findFulfillmentWarehouse(
        $cartItems
    ): ?object {
        $warehouses = DB::table('warehouses')
            ->where('status', true)
            ->orderByRaw(
                "CASE
                    WHEN name = 'Kho tổng Hà Nội' THEN 0
                    ELSE 1
                END"
            )
            ->orderBy('id')
            ->get();

        foreach ($warehouses as $warehouse) {
            $inventories = DB::table('inventories')
                ->where(
                    'warehouse_id',
                    $warehouse->id
                )
                ->whereIn(
                    'sku_id',
                    $cartItems->pluck('sku_id')
                )
                ->get()
                ->keyBy('sku_id');

            $canFulfill = true;

            foreach ($cartItems as $item) {
                $inventory = $inventories->get(
                    $item->sku_id
                );

                $available = $inventory
                    ? (int) $inventory->quantity
                        - (int) $inventory
                            ->reserved_quantity
                    : 0;

                if ($available < (int) $item->quantity) {
                    $canFulfill = false;
                    break;
                }
            }

            if ($canFulfill) {
                return $warehouse;
            }
        }

        return null;
    }

    private function calculateUnitDiscount(
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

        if (!$discount) {
            return 0;
        }

        if ($discount->discount_percent !== null) {
            return round(
                $originalPrice
                * (
                    (float) $discount->discount_percent
                    / 100
                ),
                2
            );
        }

        if ($discount->discount_amount !== null) {
            return min(
                $originalPrice,
                (float) $discount->discount_amount
            );
        }

        return 0;
    }

    private function generateUniqueCode(
        string $table,
        string $column,
        string $prefix
    ): string {
        do {
            $code = $prefix
                . now()->format('YmdHis')
                . Str::upper(Str::random(6));
        } while (
            DB::table($table)
                ->where($column, $code)
                ->exists()
        );

        return $code;
    }
}
