<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Support\BuyNow;
use App\Http\Requests\Client\StoreCheckoutRequest;
use App\Models\UserAddress;
use App\Services\CouponService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function index(
        Request $request,
        CouponService $couponService
    ): View|RedirectResponse {
        if ($request->boolean('cart')) {
            $request->session()->forget(
                BuyNow::SESSION_CART_KEY
            );
        }

        $cart = $this->findCheckoutCart(
            $request
        );

        if (! $cart) {
            return redirect()
                ->route('cart.index')
                ->with(
                    'cart_error',
                    'Giỏ hàng của bạn đang trống.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Mã được khách chọn từ Ví voucher
        |--------------------------------------------------------------------------
        */

        $couponCode = trim(
            (string) $request->query(
                'coupon',
                ''
            )
        );

        if ($couponCode !== '') {
            try {
                $couponService->apply(
                    $request,
                    $couponCode,
                    'manual'
                );

                return redirect()
                    ->route('checkout.index')
                    ->with(
                        'coupon_success',
                        'Đã áp dụng mã '
                        . mb_strtoupper(
                            $couponCode
                        )
                        . '.'
                    );
            } catch (RuntimeException $exception) {
                return redirect()
                    ->route('checkout.index')
                    ->with(
                        'coupon_error',
                        $exception->getMessage()
                    );
            }
        }

        $items = DB::table('cart_items as ci')
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
                'brands as b',
                'p.brand_id',
                '=',
                'b.id'
            )
            ->where(
                'ci.cart_id',
                $cart->id
            )
            ->select([
                'ci.id',
                'ci.quantity',
                'ci.unit_price',
                'ci.discount_amount',
                'ps.id as sku_id',
                'ps.sku_code',
                'p.id as product_id',
                'p.name',
                'p.slug',
                'b.name as brand_name',
            ])
            ->selectSub(
                function ($query): void {
                    $query
                        ->from('product_images')
                        ->select('image_path')
                        ->whereColumn(
                            'product_images.product_id',
                            'p.id'
                        )
                        ->orderByDesc(
                            'is_thumbnail'
                        )
                        ->orderBy('sort_order')
                        ->limit(1);
                },
                'image_path'
            )
            ->get()
            ->map(function ($item) {
                $item->unit_price =
                    (float) $item->unit_price;

                $item->discount_amount =
                    (float) $item
                        ->discount_amount;

                $item->final_unit_price = max(
                    0,
                    $item->unit_price
                    - $item->discount_amount
                );

                $item->line_total =
                    $item->final_unit_price
                    * (int) $item->quantity;

                return $item;
            });

        if ($items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with(
                    'cart_error',
                    'Giỏ hàng của bạn đang trống.'
                );
        }

        $subtotal = (float) $items->sum(
            fn ($item): float =>
                $item->unit_price
                * (int) $item->quantity
        );

        $discountTotal = (float) $items->sum(
            fn ($item): float =>
                $item->discount_amount
                * (int) $item->quantity
        );

        $productTotal = max(
            0,
            $subtotal - $discountTotal
        );

        /*
        |--------------------------------------------------------------------------
        | Phương thức và phí vận chuyển mặc định
        |--------------------------------------------------------------------------
        */

        $shippingMethods = DB::table(
            'shipping_methods'
        )
            ->where('status', true)
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'provider',
                'description',
                'base_fee',
                'free_shipping_minimum',
                'estimated_days_min',
                'estimated_days_max',
            ]);

        $defaultShippingMethod =
            $shippingMethods->first();

        $baseShippingFee = 0.0;

        if ($defaultShippingMethod) {
            $baseShippingFee = (float)
                $defaultShippingMethod->base_fee;

            if (
                $defaultShippingMethod
                    ->free_shipping_minimum
                !== null
                && $productTotal
                    >= (float)
                        $defaultShippingMethod
                            ->free_shipping_minimum
            ) {
                $baseShippingFee = 0.0;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Voucher: giữ mã khách chọn hoặc tự chọn mã tiết kiệm nhất
        |--------------------------------------------------------------------------
        */

        $appliedCoupon =
            $couponService->refresh(
                $request
            );

        if (! $appliedCoupon) {
            $appliedCoupon =
                $couponService->autoApplyBest(
                    $request,
                    $baseShippingFee
                );
        }

        $couponDiscount = $appliedCoupon
            ? min(
                (float) (
                    $appliedCoupon[
                        'discount_amount'
                    ] ?? 0
                ),
                $productTotal
            )
            : 0.0;

        $hasFreeShippingCoupon =
            (bool) (
                $appliedCoupon[
                    'free_shipping'
                ] ?? false
            );

        $shippingFee =
            $hasFreeShippingCoupon
                ? 0.0
                : $baseShippingFee;

        $grandTotal = max(
            0,
            $productTotal
            - $couponDiscount
            + $shippingFee
        );

        $user = $request->user();

        $savedAddresses = collect();
        $defaultAddress = null;

        if ($user) {
            $savedAddresses =
                UserAddress::query()
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->orderByDesc(
                        'is_default'
                    )
                    ->latest('id')
                    ->get();

            $defaultAddress =
                $savedAddresses->firstWhere(
                    'is_default',
                    true
                )
                ?? $savedAddresses->first();
        }

        $isBuyNow = $cart->status === 'buy_now';

        return view(
            'client.checkout.index',
            compact(
                'cart',
                'isBuyNow',
                'items',
                'subtotal',
                'discountTotal',
                'productTotal',
                'shippingMethods',
                'defaultShippingMethod',
                'shippingFee',
                'appliedCoupon',
                'couponDiscount',
                'hasFreeShippingCoupon',
                'grandTotal',
                'user',
                'savedAddresses',
                'defaultAddress'
            )
        );
    }

    private function findCheckoutCart(
        Request $request
    ): ?object {
        $buyNowCartId = (int) $request->session()->get(
            BuyNow::SESSION_CART_KEY,
            0
        );

        if ($buyNowCartId > 0) {
            $buyNowQuery = DB::table('carts')
                ->where('id', $buyNowCartId)
                ->where('status', 'buy_now');

            if ($request->user()) {
                $buyNowQuery->where(
                    'user_id',
                    $request->user()->id
                );
            } else {
                $buyNowQuery
                    ->whereNull('user_id')
                    ->where(
                        'session_id',
                        $request->session()->getId()
                    );
            }

            $buyNowCart = $buyNowQuery->first();

            if ($buyNowCart) {
                return $buyNowCart;
            }

            $request->session()->forget(
                BuyNow::SESSION_CART_KEY
            );
        }

        $query = DB::table('carts')
            ->where('status', 'active');

        if ($request->user()) {
            $query->where(
                'user_id',
                $request->user()->id
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
            ->first();
    }

    public function store(
        StoreCheckoutRequest $request,
        OrderService $orderService
    ): RedirectResponse {
        $order = $orderService->create(
            $request
        );

        return redirect()
            ->route(
                'checkout.success',
                $order->order_code
            )
            ->with(
                'checkout_success',
                'Đặt hàng thành công.'
            );
    }

    public function success(
        Request $request,
        string $orderCode
    ): View {
        $orderQuery = DB::table('orders')
            ->where(
                'order_code',
                $orderCode
            )
            ->whereNull('deleted_at');

        if ($request->user()) {
            $orderQuery->where(
                'user_id',
                $request->user()->id
            );
        } else {
            $orderQuery
                ->whereNull('user_id')
                ->where(
                    'ip_address',
                    $request->ip()
                );
        }

        $order = $orderQuery->first();

        abort_if(! $order, 404);

        $address = DB::table(
            'order_addresses'
        )
            ->where(
                'order_id',
                $order->id
            )
            ->where(
                'type',
                'shipping'
            )
            ->first();

        $items = DB::table('order_items')
            ->where(
                'order_id',
                $order->id
            )
            ->orderBy('id')
            ->get();

        $payment = DB::table('payments')
            ->where(
                'order_id',
                $order->id
            )
            ->latest('id')
            ->first();

        return view(
            'client.checkout.success',
            compact(
                'order',
                'address',
                'items',
                'payment'
            )
        );
    }
}
