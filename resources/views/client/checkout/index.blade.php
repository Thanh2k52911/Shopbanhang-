@extends('client.layouts.master')

@section('title', 'Thanh toán - Cosmetic Shop')

@section('content')
    @auth
    <div
        class="saved-coupon-modal"
        data-saved-coupon-modal
        hidden
    >
        <button
            type="button"
            class="saved-coupon-modal__backdrop"
            data-saved-coupon-close
            aria-label="Đóng danh sách ưu đãi"
        ></button>

        <div
            class="saved-coupon-modal__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="saved-coupon-modal-title"
        >
            <header class="saved-coupon-modal__header">
                <div>
                    <p class="home-section__eyebrow">
                        Ví voucher
                    </p>

                    <h2 id="saved-coupon-modal-title">
                        Ưu đãi của tôi
                    </h2>
                </div>

                <button
                    type="button"
                    class="saved-coupon-modal__close"
                    data-saved-coupon-close
                    aria-label="Đóng"
                >
                    ×
                </button>
            </header>

            <div
                class="saved-coupon-modal__message"
                data-saved-coupon-modal-message
                aria-live="polite"
            ></div>

            <div
                class="saved-coupon-modal__loading"
                data-saved-coupon-loading
                hidden
            >
                Đang tải ưu đãi...
            </div>

            <div
                class="saved-coupon-modal__list"
                data-saved-coupon-list
            ></div>

            <div
                class="saved-coupon-modal__empty"
                data-saved-coupon-empty
                hidden
            >
                <span aria-hidden="true">🎟</span>

                <h3>Chưa có mã giảm giá đã lưu</h3>

                <p>
                    Nhập mã ở phần thanh toán rồi bấm
                    “Lưu mã” để sử dụng sau.
                </p>
            </div>

            <footer class="saved-coupon-modal__footer">
                <a href="{{ route('account.coupons.index') }}">
                    Quản lý toàn bộ ưu đãi
                </a>
            </footer>
        </div>
    </div>
@endauth
    <section class="checkout-page">
        <div class="client-container">
            <nav class="checkout-page__breadcrumb">
                <a href="{{ route('home') }}">
                    Trang chủ
                </a>

                <span>/</span>

                <a href="{{ route('cart.index') }}">
                    Giỏ hàng
                </a>

                <span>/</span>

                <span>Thanh toán</span>
            </nav>

            <header class="checkout-page__header">
                <p class="home-section__eyebrow">
                    Hoàn tất đơn hàng
                </p>

                <h1>Thanh toán</h1>
            </header>

            @if (!empty($isBuyNow))
                <div class="checkout-buy-now-notice">
                    <div>
                        <strong>Thanh toán sản phẩm mua ngay</strong>
                        <span>
                            Đơn này chỉ gồm sản phẩm bạn vừa chọn. Giỏ hàng hiện tại vẫn được giữ nguyên.
                        </span>
                    </div>

                    <a href="{{ route('checkout.index', ['cart' => 1]) }}">
                        Thanh toán giỏ hàng
                    </a>
                </div>
            @endif
            <form
    action="{{ route('checkout.store') }}"
    method="POST"
>
@if ($errors->any())
    <div class="checkout-errors">
        <strong>
            Vui lòng kiểm tra lại thông tin:
        </strong>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@error('cart')
    <div class="checkout-errors">
        {{ $message }}
    </div>
@enderror

@error('inventory')
    <div class="checkout-errors">
        {{ $message }}
    </div>
@enderror
    @csrf
            <div class="checkout-page__layout">
                <div class="checkout-form-column">
                    <section class="checkout-card">
                        <h2>Thông tin nhận hàng</h2>

                        @auth
                            @if ($savedAddresses->isNotEmpty())
                                <div class="checkout-field checkout-field--full">
                                    <label for="checkout-address-id">Địa chỉ đã lưu</label>
                                    <select id="checkout-address-id" name="address_id" data-address-selector>
                                        <option value="">Nhập địa chỉ khác</option>
                                        @foreach ($savedAddresses as $savedAddress)
                                            <option
                                                value="{{ $savedAddress->id }}"
                                                data-name="{{ $savedAddress->receiver_name }}"
                                                data-phone="{{ $savedAddress->phone }}"
                                                data-address="{{ $savedAddress->address }}"
                                                data-province="{{ $savedAddress->province }}"
                                                data-district="{{ $savedAddress->district }}"
                                                data-ward="{{ $savedAddress->ward }}"
                                                @selected((string) old('address_id', $defaultAddress?->id) === (string) $savedAddress->id)
                                            >
                                                {{ $savedAddress->receiver_name }} — {{ $savedAddress->full_address }}
                                                {{ $savedAddress->is_default ? ' (Mặc định)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <a href="{{ route('account.addresses.index') }}">Quản lý sổ địa chỉ</a>
                                </div>
                            @else
                                <p class="checkout-field checkout-field--full">
                                    Bạn chưa có địa chỉ đã lưu.
                                    <a href="{{ route('account.addresses.create') }}">Thêm địa chỉ</a>
                                </p>
                            @endif
                        @endauth

                        <div class="checkout-grid">
                            <div class="checkout-field">
                                <label for="checkout-name">
                                    Họ và tên
                                </label>

                                <input
                                    type="text"
                                    id="checkout-name"
                                    name="name"
                                    value="{{ old(
                                        'name',
                                        $defaultAddress?->receiver_name ?? $user?->name
                                    ) }}"
                                    required
                                    placeholder="Nhập họ và tên"
                                >
                            </div>

                            <div class="checkout-field">
                                <label for="checkout-phone">
                                    Số điện thoại
                                </label>

                                <input
                                    type="text"
                                    id="checkout-phone"
                                    name="phone"
                                    value="{{ old('phone', $defaultAddress?->phone) }}"
                                    placeholder="Nhập số điện thoại"
                                >
                            </div>

                            <div class="checkout-field checkout-field--full">
                                <label for="checkout-email">
                                    Email
                                </label>

                                <input
                                    type="email"
                                    id="checkout-email"
                                    name="email"
                                    value="{{ old(
                                        'email',
                                        $user?->email
                                    ) }}"
                                    placeholder="Nhập email"
                                >
                            </div>

                            <div class="checkout-field checkout-field--full">
                                <label for="checkout-address">
                                    Địa chỉ nhận hàng
                                </label>

                                <input
                                    type="text"
                                    id="checkout-address"
                                    name="address"
                                    value="{{ old('address', $defaultAddress?->address) }}"
                                    placeholder="Số nhà, đường, phường/xã..."
                                >
                            </div>
                            <div
                                class="checkout-field checkout-field--full checkout-location-selector"
                                data-vietnam-address
                                data-initial-province="{{ old('province', $defaultAddress?->province) }}"
                                data-initial-district="{{ old('district', $defaultAddress?->district) }}"
                                data-initial-ward="{{ old('ward', $defaultAddress?->ward) }}"
                            >
                                <div
                                    class="checkout-grid"
                                    data-vn-address-select-fields
                                >
                                    <div class="checkout-field">
                                        <label for="checkout-province">
                                            Tỉnh/Thành phố
                                        </label>

                                        <select
                                            id="checkout-province"
                                            name="province"
                                            data-field-name="province"
                                            data-vn-province
                                            required
                                        >
                                            <option value="">Đang tải...</option>
                                        </select>
                                    </div>

                                    <div class="checkout-field">
                                        <label for="checkout-district">
                                            Quận/Huyện
                                        </label>

                                        <select
                                            id="checkout-district"
                                            name="district"
                                            data-field-name="district"
                                            data-vn-district
                                            required
                                            disabled
                                        >
                                            <option value="">
                                                Chọn Tỉnh/Thành phố trước
                                            </option>
                                        </select>
                                    </div>

                                    <div class="checkout-field checkout-field--full">
                                        <label for="checkout-ward">
                                            Phường/Xã
                                        </label>

                                        <select
                                            id="checkout-ward"
                                            name="ward"
                                            data-field-name="ward"
                                            data-vn-ward
                                            required
                                            disabled
                                        >
                                            <option value="">
                                                Chọn Quận/Huyện trước
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div
                                    class="checkout-grid"
                                    data-vn-address-manual-fields
                                    hidden
                                >
                                    <div class="checkout-field">
                                        <label for="checkout-manual-province">
                                            Tỉnh/Thành phố
                                        </label>

                                        <input
                                            id="checkout-manual-province"
                                            type="text"
                                            data-vn-manual-province
                                            placeholder="Nhập Tỉnh/Thành phố"
                                        >
                                    </div>

                                    <div class="checkout-field">
                                        <label for="checkout-manual-district">
                                            Quận/Huyện
                                        </label>

                                        <input
                                            id="checkout-manual-district"
                                            type="text"
                                            data-vn-manual-district
                                            placeholder="Nhập Quận/Huyện"
                                        >
                                    </div>

                                    <div class="checkout-field checkout-field--full">
                                        <label for="checkout-manual-ward">
                                            Phường/Xã
                                        </label>

                                        <input
                                            id="checkout-manual-ward"
                                            type="text"
                                            data-vn-manual-ward
                                            placeholder="Nhập Phường/Xã"
                                        >
                                    </div>
                                </div>

                                <div class="checkout-location-selector__footer">
                                    <p data-vn-address-status hidden></p>

                                    <button
                                        type="button"
                                        data-vn-address-manual-toggle
                                    >
                                        Nhập địa chỉ hành chính thủ công
                                    </button>
                                </div>
                            </div>

                            <div class="checkout-field checkout-field--full">
                                <label for="checkout-note">
                                    Ghi chú đơn hàng
                                </label>

                                <textarea
                                    id="checkout-note"
                                    name="note"
                                    rows="4"
                                    placeholder="Ghi chú cho đơn hàng..."
                                ></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="checkout-card">
                        <h2>Phương thức vận chuyển</h2>

                        <div class="checkout-options">
                            @forelse (
                                $shippingMethods
                                as $shippingMethod
                            )
                                <label class="checkout-option">
                                    <input
                                        type="radio"
                                        name="shipping_method_id"
                                        value="{{ $shippingMethod->id }}"
                                        data-shipping-method
                                        data-free-shipping-minimum="{{ $shippingMethod->free_shipping_minimum }}"
                                        data-shipping-fee="{{ (float) $shippingMethod->base_fee }}"

                                        @checked(
                                            $defaultShippingMethod
                                            && $shippingMethod->id
                                                === $defaultShippingMethod->id
                                        )
                                    >

                                    <span>
                                        <strong>
                                            {{ $shippingMethod->name }}
                                        </strong>

                                        @if (
                                            $shippingMethod
                                                ->description
                                        )
                                            <small>
                                                {{ $shippingMethod
                                                    ->description }}
                                            </small>
                                        @endif
                                            @if (
                                                $shippingMethod->estimated_days_min !== null
                                                && $shippingMethod->estimated_days_max !== null
                                                )
                                                <small>
                                                    Dự kiến:
                                                        {{ $shippingMethod->estimated_days_min }}
                                                            -
                                                        {{ $shippingMethod->estimated_days_max }}
                                                            ngày
                                                </small>
                                            @endif
                                            @if ($shippingMethod->provider)
                                                <small>
                                                        Đơn vị:
                                                        {{ $shippingMethod->provider }}
                                                </small>
                                            @endif
                                    </span>

                                    <b>
                                        {{ number_format(
                                            $shippingMethod
                                                ->base_fee,
                                            0,
                                            ',',
                                            '.'
                                        ) }}đ
                                    </b>
                                </label>
                            @empty
                                <p>
                                    Chưa có phương thức vận chuyển.
                                </p>
                            @endforelse
                        </div>

                    </section>

                                        <section class="checkout-card">
                        <h2>Phương thức thanh toán</h2>

                        <label class="checkout-option">
                            <input
                                type="radio"
                                name="payment_method"
                                value="cod"
                                checked
                            >

                            <span>
                                <strong>
                                    Thanh toán khi nhận hàng
                                </strong>

                                <small>
                                    Thanh toán bằng tiền mặt
                                    khi nhận được hàng.
                                </small>
                            </span>
                        </label>
                    </section>
                </div>

                <aside class="checkout-summary">
                    <h2>Đơn hàng của bạn</h2>

                    <div class="checkout-products">
                        @foreach ($items as $item)
                            <article class="checkout-product">
                                <div class="checkout-product__image">
                                    @if ($item->image_path)
                                        <img
                                            src="{{ asset(
                                                'images/'
                                                . $item->image_path
                                            ) }}"
                                            alt="{{ $item->name }}"
                                        >
                                    @else
                                        <span>
                                            Cosmetic Shop
                                        </span>
                                    @endif
                                </div>

                                <div>
                                    <h3>
                                        {{ $item->name }}
                                    </h3>

                                    <p>
                                        SKU:
                                        {{ $item->sku_code }}
                                    </p>

                                    <p>
                                        Số lượng:
                                        {{ $item->quantity }}
                                    </p>
                                </div>

                                <strong>
                                    {{ number_format(
                                        $item->line_total,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ
                                </strong>
                            </article>
                        @endforeach
                    </div>

                    <section class="checkout-coupon">
                        <div class="checkout-coupon__heading">
                            <h3>Mã giảm giá</h3>

                            @auth
                                <button
                                    type="button"
                                    class="checkout-coupon__saved-button"
                                    data-saved-coupon-open
                                >
                                    Ưu đãi của tôi
                                </button>
                            @else
                                <a
                                    href="{{ route('login') }}"
                                    class="checkout-coupon__saved-button"
                                >
                                    Đăng nhập để xem ưu đãi
                                </a>
                            @endauth
                        </div>
                        @if (session('coupon_success'))
    <div class="checkout-coupon__flash is-success">
        {{ session('coupon_success') }}
    </div>
@endif

@if (session('coupon_error'))
    <div class="checkout-coupon__flash is-error">
        {{ session('coupon_error') }}
    </div>
@endif
@if (
    $appliedCoupon &&
    (($appliedCoupon['source'] ?? '') === 'auto')
)
    <div class="checkout-coupon__flash is-success">
        🎉 Hệ thống đã tự động chọn mã giảm giá tốt nhất cho bạn.
        Nếu muốn, bạn vẫn có thể chọn mã khác.
    </div>
@endif

                        <div class="checkout-coupon__form">
                            <input
                                type="text"
                                placeholder="Nhập mã giảm giá"
                                maxlength="50"
                                value="{{ $appliedCoupon['code'] ?? '' }}"
                                data-checkout-coupon-input
                                @disabled($appliedCoupon)
                            >

                            <button
                                type="button"
                                data-checkout-coupon-apply
                                @disabled($appliedCoupon)
                            >
                                Áp dụng
                            </button>

                            @auth
                                <button
                                    type="button"
                                    class="checkout-coupon__save-button"
                                    data-checkout-coupon-save
                                >
                                    Lưu mã
                                </button>
                            @endauth
                        </div>

                        <p
                            class="checkout-coupon__message"
                            data-checkout-coupon-message
                            aria-live="polite"
                        ></p>

                        <div
                            class="checkout-coupon__applied"
                            data-checkout-coupon-applied
                            @if (!$appliedCoupon)
                                hidden
                            @endif
                        >
                            <div>
                                <span>Đã áp dụng</span>

                                <strong data-checkout-coupon-code>
                                    {{ $appliedCoupon['code'] ?? '' }}
                                </strong>

                                <small data-checkout-coupon-name>
                                    {{ $appliedCoupon['name'] ?? '' }}
                                </small>
                            </div>

                            <div
    style="
        display:flex;
        gap:10px;
        align-items:center;
    "
>
    <button
        type="button"
        class="checkout-coupon__change"
        data-saved-coupon-open
    >
        Đổi mã
    </button>

    <button
        type="button"
        data-checkout-coupon-remove
    >
        Gỡ mã
    </button>
</div>
                        </div>
                    </section>

                    <dl class="checkout-totals">
                        <div>
                            <dt>Tạm tính</dt>
                            <dd>
                                {{ number_format(
                                    $subtotal,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </dd>
                        </div>

                        <div>
    <dt>Khuyến mãi sản phẩm</dt>

    <dd>
        -{{ number_format(
            $discountTotal,
            0,
            ',',
            '.'
        ) }}đ
    </dd>
</div>
<div
    data-checkout-coupon-row
    @if (!$appliedCoupon)
        hidden
    @endif
>
    <dt>
        Giảm từ mã

        <span data-checkout-coupon-label>
            @if ($appliedCoupon)
                ({{ $appliedCoupon['code'] }})
            @endif
        </span>
    </dt>

    <dd data-checkout-coupon-discount>
        -{{ number_format(
            $couponDiscount,
            0,
            ',',
            '.'
        ) }}đ
    </dd>
</div>

                        <div>
                            <dt>Phí vận chuyển</dt>
                            <dd
                                data-checkout-shipping-fee
                                data-initial-shipping-fee="{{ $shippingFee }}"
                            >
                            {{ number_format(
                            $shippingFee,
                                0,
                                ',',
                                '.'
                            ) }}đ
                            </dd>
                        </div>

                        <div class="checkout-totals__grand">
                            <dt>Tổng thanh toán</dt>
                            <dd data-checkout-grand-total>
                                {{ number_format(
                                    $grandTotal,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </dd>
                        </div>
                    </dl>

                    <button
    type="submit"
    class="checkout-submit"
>
    Đặt hàng
</button>
                </aside>
            </div>
        </form>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .checkout-card {
            border: 1px solid #e7ebf1;
            border-radius: 1.2rem;
            box-shadow: 0 16px 34px rgb(15 23 42 / 5%);
        }

        .checkout-card > h2 {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f1f3f6;
        }

        .checkout-card > h2::before {
            content: '⌖';
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border-radius: 999px;
            background: #fce7f3;
            color: #be185d;
            font-size: 1rem;
        }

        .checkout-field label {
            display: block;
            margin-bottom: .5rem;
            color: #273142;
            font-weight: 800;
        }

        .checkout-field input,
        .checkout-field select,
        .checkout-field textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #d9dee8;
            border-radius: .85rem;
            background: #fff;
        }

        .checkout-field input,
        .checkout-field select {
            min-height: 54px;
            padding-inline: 1rem;
        }

        .checkout-field textarea {
            padding: .9rem 1rem;
            resize: vertical;
        }

        .checkout-field input:focus,
        .checkout-field select:focus,
        .checkout-field textarea:focus {
            outline: 0;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgb(236 72 153 / 12%);
        }

        .checkout-field select:disabled {
            cursor: wait;
            background: #f8fafc;
            color: #9ca3af;
        }

        .checkout-location-selector {
            margin-top: .25rem;
            padding: 1rem;
            border: 1px solid #edf0f5;
            border-radius: 1rem;
            background: #fbfcfe;
        }

        .checkout-location-selector__footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-top: .8rem;
        }

        .checkout-location-selector__footer p {
            margin: 0;
            color: #64748b;
            font-size: .82rem;
        }

        .checkout-location-selector__footer p[data-type="success"] {
            color: #047857;
        }

        .checkout-location-selector__footer p[data-type="warning"] {
            color: #b45309;
        }

        .checkout-location-selector__footer p[data-type="error"] {
            color: #b91c1c;
        }

        .checkout-location-selector__footer button {
            border: 0;
            background: transparent;
            color: #db2777;
            font-weight: 800;
            cursor: pointer;
        }

        [data-address-selector] {
            margin-bottom: .55rem;
        }

        @media (max-width: 640px) {
            .checkout-location-selector__footer {
                align-items: flex-start;
                flex-direction: column;
            }
        }
        .checkout-coupon__change{
    border:none;
    background:#ec4899;
    color:#fff;
    padding:10px 18px;
    border-radius:8px;
    cursor:pointer;
    font-weight:700;
    transition:.2s;
}

.checkout-coupon__change:hover{
    background:#db2777;
}
    </style>
@endpush

@push('scripts')
    <script>

        window.checkoutTotals = {
            subtotal: @json($subtotal),

            discountTotal:
                @json($discountTotal),

            productTotal:
                @json($productTotal),

            couponDiscount:
                @json($couponDiscount),

            freeShippingCoupon:
                @json($hasFreeShippingCoupon),

            coupon:
                @json($appliedCoupon),

            applyCouponUrl:
                @json(route('checkout.coupon.apply')),

            removeCouponUrl:
                @json(route('checkout.coupon.remove')),
                 @auth
                savedCouponsUrl:
                    @json(route('account.checkout.saved-coupons.index')),

                saveCouponByCodeUrl:
                    @json(route('account.coupons.save-by-code')),
            @else
                savedCouponsUrl: null,
                saveCouponByCodeUrl: null,
            @endauth
        };
    </script>
@endpush
