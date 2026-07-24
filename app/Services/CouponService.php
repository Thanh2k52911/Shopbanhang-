<?php

namespace App\Services;

use App\Models\Coupon;
use App\Support\BuyNow;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CouponService
{
    public const SESSION_KEY = 'checkout_coupon';

    public const AUTO_DISABLED_KEY =
        'checkout_coupon_auto_disabled';

    /**
     * Áp dụng coupon do khách chủ động chọn.
     *
     * @return array<string, mixed>
     */
    public function apply(
        Request $request,
        string $code,
        string $source = 'manual'
    ): array {
        $coupon = $this->findCouponByCode($code);

        $evaluation = $this->evaluateCoupon(
            $coupon,
            $request,
            0
        );

        $couponData = $this->makeSessionData(
            $coupon,
            $evaluation,
            $source
        );

        $request->session()->put(
            self::SESSION_KEY,
            $couponData
        );

        /*
         * Khi khách tự chọn một mã, cho phép hệ thống dùng
         * voucher trở lại, kể cả trước đó khách đã bấm "Không dùng".
         */
        $request->session()->forget(
            self::AUTO_DISABLED_KEY
        );

        return $couponData;
    }

    /**
     * Gỡ coupon. Mặc định hiểu là khách chủ động không muốn dùng mã,
     * nên Checkout không tự áp lại trong cùng phiên.
     */
    public function remove(
        Request $request,
        bool $disableAutomatic = true
    ): void {
        $request->session()->forget(
            self::SESSION_KEY
        );

        if ($disableAutomatic) {
            $request->session()->put(
                self::AUTO_DISABLED_KEY,
                true
            );
        }
    }

    /**
     * Cho phép tự chọn voucher trở lại.
     */
    public function enableAutomatic(
        Request $request
    ): void {
        $request->session()->forget(
            self::AUTO_DISABLED_KEY
        );
    }

    /**
     * Lấy coupon đang áp dụng.
     *
     * @return array<string, mixed>|null
     */
    public function getApplied(
        Request $request
    ): ?array {
        $coupon = $request->session()->get(
            self::SESSION_KEY
        );

        return is_array($coupon)
            ? $coupon
            : null;
    }

    /**
     * Kiểm tra lại coupon theo giỏ hàng mới nhất.
     *
     * @return array<string, mixed>|null
     */
    public function refresh(
        Request $request
    ): ?array {
        $appliedCoupon = $this->getApplied(
            $request
        );

        if (! $appliedCoupon) {
            return null;
        }

        try {
            return $this->apply(
                $request,
                (string) $appliedCoupon['code'],
                (string) (
                    $appliedCoupon['source']
                    ?? 'manual'
                )
            );
        } catch (RuntimeException) {
            /*
             * Coupon tự mất hiệu lực không đồng nghĩa với việc khách
             * từ chối dùng voucher. Cho phép hệ thống tìm mã khác.
             */
            $this->remove(
                $request,
                false
            );

            return null;
        }
    }

    /**
     * Tự áp voucher có tổng giá trị tiết kiệm thực tế cao nhất.
     *
     * @return array<string, mixed>|null
     */
    public function autoApplyBest(
        Request $request,
        float $shippingFee
    ): ?array {
        if (
    $this->getApplied($request)
    || (bool) $request->session()->get(
        self::AUTO_DISABLED_KEY,
        false
    )
) {
    return $this->getApplied($request);
}

        $best = $this->availableCoupons(
            $request,
            $shippingFee
        )
            ->where('usable', true)
            ->where('saving_amount', '>', 0)
            ->sortByDesc('saving_amount')
            ->sortBy(function (array $coupon): int {
                return $coupon['end_at_timestamp']
                    ?? PHP_INT_MAX;
            })
            ->first();

        if (! $best) {
            return null;
        }

        $couponData = [
            'coupon_id' =>
                (int) $best['coupon_id'],

            'code' =>
                (string) $best['code'],

            'name' =>
                (string) $best['name'],

            'discount_type' =>
                (string) $best['discount_type'],

            'discount_amount' =>
                (float) $best['discount_amount'],

            'free_shipping' =>
                (bool) $best['free_shipping'],

            'cart_subtotal' =>
                (float) $best['cart_subtotal'],

            'eligible_subtotal' =>
                (float) $best['eligible_subtotal'],

            'saving_amount' =>
                (float) $best['saving_amount'],

            'source' => 'auto',
        ];

        $request->session()->put(
            self::SESSION_KEY,
            $couponData
        );

        return $couponData;
    }

    /**
     * Danh sách voucher Shop đang cung cấp cho tài khoản hiện tại,
     * kèm số tiền tiết kiệm thực tế theo giỏ hàng.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function availableCoupons(
        Request $request,
        float $shippingFee = 0
    ): Collection {
        $user = $request->user();

        $query = Coupon::query()
            ->with([
                'products:id,name',
                'categories:id,name',
                'users:id',
            ])
            ->whereNull('deleted_at')
            ->where('status', true)
            ->where(function ($dateQuery): void {
                $dateQuery
                    ->whereNull('start_at')
                    ->orWhere(
                        'start_at',
                        '<=',
                        now()
                    );
            })
            ->where(function ($dateQuery): void {
                $dateQuery
                    ->whereNull('end_at')
                    ->orWhere(
                        'end_at',
                        '>=',
                        now()
                    );
            })
            ->where(function ($accessQuery) use (
                $user
            ): void {
                $accessQuery->where(
                    'is_public',
                    true
                );

                if ($user) {
                    $accessQuery->orWhereHas(
                        'users',
                        fn ($userQuery) =>
                            $userQuery->where(
                                'users.id',
                                $user->id
                            )
                    );
                }
            })
            ->orderByRaw(
                'CASE WHEN end_at IS NULL THEN 1 ELSE 0 END'
            )
            ->orderBy('end_at')
            ->latest('id');

        $savedCouponIds = $user
            ? DB::table('saved_coupons')
                ->where('user_id', $user->id)
                ->pluck('coupon_id')
                ->map(
                    fn ($id): int => (int) $id
                )
            : collect();

        return $query
            ->get()
            ->map(function (Coupon $coupon) use (
                $request,
                $shippingFee,
                $savedCouponIds
            ): array {
                try {
                    $evaluation =
                        $this->evaluateCoupon(
                            $coupon,
                            $request,
                            $shippingFee
                        );

                    $usable = true;
                    $unavailableReason = null;
                } catch (RuntimeException $exception) {
                    $evaluation = [
                        'cart_subtotal' => 0.0,
                        'eligible_subtotal' => 0.0,
                        'discount_amount' => 0.0,
                        'free_shipping' =>
                            $coupon->isFreeShipping(),
                        'shipping_saving' => 0.0,
                        'saving_amount' => 0.0,
                    ];

                    $usable = false;
                    $unavailableReason =
                        $exception->getMessage();
                }

                $userUsageCount = $request->user()
                    ? CouponUsage::query()
                        ->where(
                            'coupon_id',
                            $coupon->id
                        )
                        ->where(
                            'user_id',
                            $request->user()->id
                        )
                        ->count()
                    : 0;

                $remainingUserUses = max(
                    0,
                    (int) $coupon
                        ->usage_limit_per_user
                    - $userUsageCount
                );

                $remainingGlobalUses =
                    $coupon->usage_limit === null
                        ? null
                        : max(
                            0,
                            (int) $coupon->usage_limit
                            - (int) $coupon->used_count
                        );

                return [
                    'coupon_id' =>
                        (int) $coupon->id,

                    'code' =>
                        (string) $coupon->code,

                    'name' =>
                        (string) $coupon->name,

                    'description' =>
                        $coupon->description,

                    'discount_type' =>
                        (string) $coupon->discount_type,

                    'discount_value' =>
                        (float) $coupon->discount_value,

                    'maximum_discount' =>
                        $coupon->maximum_discount !== null
                            ? (float) $coupon
                                ->maximum_discount
                            : null,

                    'minimum_order_amount' =>
                        (float) $coupon
                            ->minimum_order_amount,

                    'first_order_only' =>
                        (bool) $coupon
                            ->first_order_only,

                    'is_public' =>
                        (bool) $coupon->is_public,

                    'end_at' =>
                        $coupon->end_at
                            ?->toIso8601String(),

                    'end_at_display' =>
                        $coupon->end_at
                            ?->format('d/m/Y H:i'),

                    'end_at_timestamp' =>
                        $coupon->end_at
                            ?->getTimestamp(),

                    'remaining_user_uses' =>
                        $remainingUserUses,

                    'remaining_global_uses' =>
                        $remainingGlobalUses,

                    'product_names' =>
                        $coupon->products
                            ->pluck('name')
                            ->values()
                            ->all(),

                    'category_names' =>
                        $coupon->categories
                            ->pluck('name')
                            ->values()
                            ->all(),

                    'saved' =>
                        $savedCouponIds->contains(
                            (int) $coupon->id
                        ),

                    'usable' => $usable,

                    'unavailable_reason' =>
                        $unavailableReason,

                    'cart_subtotal' =>
                        (float) $evaluation[
                            'cart_subtotal'
                        ],

                    'eligible_subtotal' =>
                        (float) $evaluation[
                            'eligible_subtotal'
                        ],

                    'discount_amount' =>
                        (float) $evaluation[
                            'discount_amount'
                        ],

                    'free_shipping' =>
                        (bool) $evaluation[
                            'free_shipping'
                        ],

                    'shipping_saving' =>
                        (float) $evaluation[
                            'shipping_saving'
                        ],

                    'saving_amount' =>
                        (float) $evaluation[
                            'saving_amount'
                        ],
                ];
            })
            ->sortByDesc('saving_amount')
            ->values()
            ->map(function (
                array $coupon,
                int $index
            ): array {
                $coupon['is_best'] =
                    $index === 0
                    && $coupon['usable']
                    && $coupon['saving_amount'] > 0;

                return $coupon;
            });
    }

    /**
     * Tìm coupon bằng code.
     */
    private function findCouponByCode(
        string $code
    ): Coupon {
        $normalizedCode = mb_strtoupper(
            trim($code)
        );

        if ($normalizedCode === '') {
            throw new RuntimeException(
                'Vui lòng nhập mã giảm giá.'
            );
        }

        $coupon = Coupon::query()
            ->with([
                'products:id,name',
                'categories:id,name',
                'users:id',
            ])
            ->whereRaw(
                'UPPER(code) = ?',
                [$normalizedCode]
            )
            ->first();

        if (! $coupon) {
            throw new RuntimeException(
                'Mã giảm giá không tồn tại.'
            );
        }

        return $coupon;
    }

    /**
     * Tính giá trị thực tế coupon với giỏ hàng hiện tại.
     *
     * @return array<string, float|bool>
     */
    private function evaluateCoupon(
        Coupon $coupon,
        Request $request,
        float $shippingFee
    ): array {
        $this->validateCoupon(
            $coupon,
            $request
        );

        $cart = $this->findActiveCart(
            $request
        );

        if (! $cart) {
            throw new RuntimeException(
                'Không tìm thấy giỏ hàng đang hoạt động.'
            );
        }

        $cartItems = $this->getCartItems(
            (int) $cart->id
        );

        if ($cartItems->isEmpty()) {
            throw new RuntimeException(
                'Giỏ hàng đang trống.'
            );
        }

        $cartSubtotal =
            $this->calculateCartSubtotal(
                $cartItems
            );

        if (
            $cartSubtotal
            < (float) $coupon
                ->minimum_order_amount
        ) {
            throw new RuntimeException(
                'Đơn hàng phải đạt tối thiểu '
                . number_format(
                    (float) $coupon
                        ->minimum_order_amount,
                    0,
                    ',',
                    '.'
                )
                . 'đ.'
            );
        }

        $eligibleSubtotal =
            $this->calculateEligibleSubtotal(
                $coupon,
                $cartItems
            );

        if (
            ! $coupon->isFreeShipping()
            && $eligibleSubtotal <= 0
        ) {
            throw new RuntimeException(
                'Không có sản phẩm phù hợp với mã này.'
            );
        }

        $discountAmount =
            $coupon->isFreeShipping()
                ? 0.0
                : (float) $coupon
                    ->calculateDiscount(
                        $eligibleSubtotal
                    );

        $shippingSaving =
            $coupon->isFreeShipping()
                ? max(0, $shippingFee)
                : 0.0;

        $savingAmount =
            $discountAmount
            + $shippingSaving;

        if ($savingAmount <= 0) {
            throw new RuntimeException(
                'Mã chưa tạo ra giá trị giảm cho giỏ hàng hiện tại.'
            );
        }

        return [
            'cart_subtotal' =>
                round($cartSubtotal, 2),

            'eligible_subtotal' =>
                round($eligibleSubtotal, 2),

            'discount_amount' =>
                round($discountAmount, 2),

            'free_shipping' =>
                $coupon->isFreeShipping(),

            'shipping_saving' =>
                round($shippingSaving, 2),

            'saving_amount' =>
                round($savingAmount, 2),
        ];
    }

    /**
     * @param array<string, mixed> $evaluation
     * @return array<string, mixed>
     */
    private function makeSessionData(
        Coupon $coupon,
        array $evaluation,
        string $source
    ): array {
        return [
            'coupon_id' =>
                (int) $coupon->id,

            'code' =>
                (string) $coupon->code,

            'name' =>
                (string) $coupon->name,

            'discount_type' =>
                (string) $coupon->discount_type,

            'discount_amount' =>
                (float) $evaluation[
                    'discount_amount'
                ],

            'free_shipping' =>
                (bool) $evaluation[
                    'free_shipping'
                ],

            'cart_subtotal' =>
                (float) $evaluation[
                    'cart_subtotal'
                ],

            'eligible_subtotal' =>
                (float) $evaluation[
                    'eligible_subtotal'
                ],

            'saving_amount' =>
                (float) $evaluation[
                    'saving_amount'
                ],

            'source' => $source,
        ];
    }

    /**
     * Kiểm tra các điều kiện chung.
     */
    private function validateCoupon(
        Coupon $coupon,
        Request $request
    ): void {
        if (! $coupon->status) {
            throw new RuntimeException(
                'Mã giảm giá đã bị vô hiệu hóa.'
            );
        }

        if (
            $coupon->start_at
            && $coupon->start_at->isFuture()
        ) {
            throw new RuntimeException(
                'Mã giảm giá chưa đến thời gian sử dụng.'
            );
        }

        if (
            $coupon->end_at
            && $coupon->end_at->isPast()
        ) {
            throw new RuntimeException(
                'Mã giảm giá đã hết hạn.'
            );
        }

        if ($coupon->hasReachedUsageLimit()) {
            throw new RuntimeException(
                'Mã giảm giá đã hết lượt sử dụng.'
            );
        }

        $user = $request->user();

        if (! $coupon->is_public) {
            if (! $user) {
                throw new RuntimeException(
                    'Vui lòng đăng nhập để sử dụng mã này.'
                );
            }

            if (
                ! $coupon->users->contains(
                    'id',
                    $user->id
                )
            ) {
                throw new RuntimeException(
                    'Mã này không dành cho tài khoản của bạn.'
                );
            }
        } elseif (
            $coupon->users->isNotEmpty()
            && (
                ! $user
                || ! $coupon->users->contains(
                    'id',
                    $user->id
                )
            )
        ) {
            throw new RuntimeException(
                'Mã này không áp dụng cho tài khoản của bạn.'
            );
        }

        if ($coupon->first_order_only) {
            if (! $user) {
                throw new RuntimeException(
                    'Vui lòng đăng nhập để dùng mã cho đơn đầu tiên.'
                );
            }

            $hasCompletedOrder =
                DB::table('orders')
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->where(
                        'order_status',
                        'completed'
                    )
                    ->exists();

            if ($hasCompletedOrder) {
                throw new RuntimeException(
                    'Mã này chỉ áp dụng cho đơn hàng đầu tiên.'
                );
            }
        }

        if ($user) {
            $usageCount = CouponUsage::query()
                ->where(
                    'coupon_id',
                    $coupon->id
                )
                ->where(
                    'user_id',
                    $user->id
                )
                ->count();

            if (
                $usageCount
                >= (int) $coupon
                    ->usage_limit_per_user
            ) {
                throw new RuntimeException(
                    'Bạn đã sử dụng hết lượt của mã này.'
                );
            }
        }
    }

    /**
     * Tìm giỏ hàng active.
     */
    private function findActiveCart(
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

    /**
     * Lấy item giỏ hàng và giá sau khuyến mãi sản phẩm.
     */
    private function getCartItems(
        int $cartId
    ): Collection {
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
            ->where('ci.cart_id', $cartId)
            ->where('ps.status', true)
            ->where('p.status', true)
            ->whereNull('p.deleted_at')
            ->get([
                'ci.id',
                'ci.sku_id',
                'ci.quantity',
                'ps.price as current_price',
                'ps.product_id',
                'p.category_id',
            ])
            ->map(function (object $item): object {
                $item->current_price = max(
                    0,
                    (float) $item->current_price
                );

                $item->discount_amount =
                    $this->calculateUnitDiscount(
                        (int) $item->product_id,
                        $item->current_price
                    );

                $item->final_unit_price = max(
                    0,
                    $item->current_price
                    - $item->discount_amount
                );

                return $item;
            });
    }

    private function calculateCartSubtotal(
        Collection $cartItems
    ): float {
        return round(
            $cartItems->sum(
                fn ($item): float =>
                    (float) $item
                        ->final_unit_price
                    * (int) $item->quantity
            ),
            2
        );
    }

    private function calculateEligibleSubtotal(
        Coupon $coupon,
        Collection $cartItems
    ): float {
        $productIds = $coupon->products
            ->pluck('id')
            ->map(fn ($id): int => (int) $id);

        $categoryIds = $coupon->categories
            ->pluck('id')
            ->map(fn ($id): int => (int) $id);

        return round(
            $cartItems
                ->filter(function ($item) use (
                    $productIds,
                    $categoryIds
                ): bool {
                    if (
                        $productIds->isEmpty()
                        && $categoryIds->isEmpty()
                    ) {
                        return true;
                    }

                    return $productIds->contains(
                        (int) $item->product_id
                    )
                    || $categoryIds->contains(
                        (int) $item->category_id
                    );
                })
                ->sum(
                    fn ($item): float =>
                        (float) $item
                            ->final_unit_price
                        * (int) $item->quantity
                ),
            2
        );
    }

    private function calculateUnitDiscount(
        int $productId,
        float $originalPrice
    ): float {
        $discount = DB::table(
            'product_discounts as pd'
        )
            ->join(
                'discount_campaigns as dc',
                'pd.campaign_id',
                '=',
                'dc.id'
            )
            ->where(
                'pd.product_id',
                $productId
            )
            ->where('dc.status', true)
            ->where(
                'dc.start_date',
                '<=',
                now()
            )
            ->where(
                'dc.end_date',
                '>=',
                now()
            )
            ->where(function ($query): void {
                $query
                    ->whereNull(
                        'pd.limit_quantity'
                    )
                    ->orWhereColumn(
                        'pd.sold_quantity',
                        '<',
                        'pd.limit_quantity'
                    );
            })
            ->orderByDesc(
                'dc.is_flash_sale'
            )
            ->orderBy('dc.end_date')
            ->select([
                'pd.discount_percent',
                'pd.discount_amount',
            ])
            ->first();

        if (! $discount) {
            return 0.0;
        }

        if (
            $discount->discount_percent
            !== null
        ) {
            return min(
                $originalPrice,
                round(
                    $originalPrice
                    * (
                        (float) $discount
                            ->discount_percent
                        / 100
                    ),
                    2
                )
            );
        }

        if (
            $discount->discount_amount
            !== null
        ) {
            return min(
                $originalPrice,
                max(
                    0,
                    (float) $discount
                        ->discount_amount
                )
            );
        }

        return 0.0;
    }
}
