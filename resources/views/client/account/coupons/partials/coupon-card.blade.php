@php
    $isExpired = $coupon->end_at
        && $coupon->end_at->isPast();

    $isNotStarted = $coupon->start_at
        && $coupon->start_at->isFuture();

    $isUsageLimitReached =
        $coupon->usage_limit !== null
        && (int) $coupon->used_count
            >= (int) $coupon->usage_limit;

    $isUsable = $coupon->status
        && ! $isExpired
        && ! $isNotStarted
        && ! $isUsageLimitReached;

    $discountText = match (
        $coupon->discount_type
    ) {
        'percentage' =>
            'Giảm '
            . rtrim(
                rtrim(
                    number_format(
                        (float) $coupon->discount_value,
                        2,
                        '.',
                        ''
                    ),
                    '0'
                ),
                '.'
            )
            . '%',

        'fixed' =>
            'Giảm '
            . number_format(
                (float) $coupon->discount_value,
                0,
                ',',
                '.'
            )
            . 'đ',

        'free_shipping' =>
            'Miễn phí vận chuyển',

        default => 'Ưu đãi đặc biệt',
    };

    $productNames = $coupon->products
        ?->pluck('name')
        ->filter()
        ->values()
        ?? collect();

    $categoryNames = $coupon->categories
        ?->pluck('name')
        ->filter()
        ->values()
        ?? collect();
@endphp

<article
    class="account-coupon-card
        {{ ! $isUsable ? 'is-disabled' : '' }}"
    @if ($savedCoupon)
        data-saved-coupon-card="{{ $savedCoupon->id }}"
    @endif
>
    <div class="account-coupon-card__ticket">
        <span>🎟</span>
    </div>

    <div class="account-coupon-card__body">
        <div class="account-coupon-card__heading">
            <div>
                <strong>{{ $coupon->code }}</strong>
                <h3>{{ $coupon->name }}</h3>
            </div>

            <span>{{ $discountText }}</span>
        </div>

        @if ($coupon->description)
            <p class="account-coupon-card__description">
                {{ $coupon->description }}
            </p>
        @endif

        <div class="voucher-detail-list">
            <span>
                Đơn tối thiểu:
                <strong>
                    {{ number_format(
                        (float) $coupon
                            ->minimum_order_amount,
                        0,
                        ',',
                        '.'
                    ) }}đ
                </strong>
            </span>

            @if ($coupon->maximum_discount)
                <span>
                    Giảm tối đa:
                    <strong>
                        {{ number_format(
                            (float) $coupon
                                ->maximum_discount,
                            0,
                            ',',
                            '.'
                        ) }}đ
                    </strong>
                </span>
            @endif

            <span>
                Hạn dùng:
                <strong>
                    {{ $coupon->end_at
                        ?->format('d/m/Y H:i')
                        ?? 'Không giới hạn' }}
                </strong>
            </span>

            <span>
                Mỗi tài khoản:
                <strong>
                    {{ $coupon
                        ->usage_limit_per_user }}
                    lượt
                </strong>
            </span>

            @if ($coupon->first_order_only)
                <span>
                    Chỉ áp dụng cho đơn hàng đầu tiên.
                </span>
            @endif
        </div>

        @if (
            $productNames->isNotEmpty()
            || $categoryNames->isNotEmpty()
        )
            <div class="voucher-restrictions">
                @if ($productNames->isNotEmpty())
                    <p>
                        <strong>Sản phẩm áp dụng:</strong>
                        {{ $productNames->implode(', ') }}
                    </p>
                @endif

                @if ($categoryNames->isNotEmpty())
                    <p>
                        <strong>Danh mục áp dụng:</strong>
                        {{ $categoryNames->implode(', ') }}
                    </p>
                @endif
            </div>
        @else
            <div class="voucher-restrictions">
                Áp dụng cho toàn bộ sản phẩm đủ điều kiện.
            </div>
        @endif

        <div class="account-coupon-card__footer">
            <span
                class="account-coupon-card__status
                    {{ $isUsable
                        ? 'is-usable'
                        : 'is-unusable' }}"
            >
                {{ $isUsable
                    ? 'Có thể sử dụng'
                    : 'Không khả dụng' }}
            </span>

            <div class="account-coupon-card__actions">
                @if ($isUsable)
                    <a
                        href="{{ route(
                            'checkout.index',
                            ['coupon' => $coupon->code]
                        ) }}"
                        class="account-coupon-card__apply"
                    >
                        Dùng ngay
                    </a>
                @endif

                @if ($canSave)
                    <button
                        type="button"
                        class="voucher-save-button"
                        data-voucher-save
                        data-save-url="{{ route(
                            'account.coupons.store',
                            $coupon
                        ) }}"
                    >
                        Lưu voucher
                    </button>
                @elseif ($savedCoupon)
                    <button
                        type="button"
                        class="account-coupon-card__remove"
                        data-saved-coupon-remove
                        data-remove-url="{{ route(
                            'account.coupons.destroy',
                            $savedCoupon
                        ) }}"
                    >
                        Xóa
                    </button>
                @endif
            </div>
        </div>
    </div>
</article>
