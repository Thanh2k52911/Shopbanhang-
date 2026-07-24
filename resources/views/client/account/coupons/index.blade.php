@extends('client.layouts.master')

@section('title', 'Voucher của tôi - Cosmetic Shop')

@section('content')
    <section class="account-coupons-page">
        <div class="client-container">
            <nav class="account-coupons-page__breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a>
                <span>/</span>
                <a href="{{ route('account.index') }}">Tài khoản</a>
                <span>/</span>
                <span>Voucher</span>
            </nav>

            <header class="account-coupons-page__header">
                <div>
                    <p class="home-section__eyebrow">
                        Ưu đãi dành cho bạn
                    </p>

                    <h1>Voucher từ Shop</h1>

                    <p>
                        Theo dõi voucher mới, xem điều kiện
                        và lưu mã để sử dụng khi thanh toán.
                    </p>
                </div>

                <a
                    href="{{ route('checkout.index') }}"
                    class="account-coupons-page__checkout-link"
                >
                    Đi tới thanh toán
                </a>
            </header>

            <div
                class="account-coupons-page__message"
                data-voucher-page-message
                aria-live="polite"
            ></div>

            <section class="voucher-section">
                <div class="voucher-section__heading">
                    <div>
                        <h2>Voucher mới từ Shop</h2>
                        <p>
                            Voucher được Admin cập nhật sẽ xuất hiện tại đây.
                        </p>
                    </div>

                    <span>
                        {{ $shopCoupons->count() }} mã mới
                    </span>
                </div>

                @if ($shopCoupons->isNotEmpty())
                    <div class="account-coupon-grid">
                        @foreach ($shopCoupons as $coupon)
                            @include(
                                'client.account.coupons.partials.coupon-card',
                                [
                                    'coupon' => $coupon,
                                    'savedCoupon' => null,
                                    'canSave' => true,
                                ]
                            )
                        @endforeach
                    </div>
                @else
                    <div class="account-coupons-empty">
                        <span>🎟</span>
                        <h3>Chưa có voucher mới</h3>
                        <p>
                            Voucher mới của Shop sẽ tự động xuất hiện ở đây.
                        </p>
                    </div>
                @endif
            </section>

            <section class="voucher-section">
                <div class="voucher-section__heading">
                    <div>
                        <h2>Voucher đã lưu</h2>
                        <p>
                            Bạn đang có
                            <strong data-saved-coupon-total>
                                {{ $savedCoupons->total() }}
                            </strong>
                            voucher trong Ví.
                        </p>
                    </div>
                </div>

                @if ($savedCoupons->isNotEmpty())
                    <div
                        class="account-coupon-grid"
                        data-saved-coupon-grid
                    >
                        @foreach ($savedCoupons as $savedCoupon)
                            @include(
                                'client.account.coupons.partials.coupon-card',
                                [
                                    'coupon' => $savedCoupon->coupon,
                                    'savedCoupon' => $savedCoupon,
                                    'canSave' => false,
                                ]
                            )
                        @endforeach
                    </div>

                    <div
                        class="account-coupons-empty"
                        data-saved-coupon-empty
                        hidden
                    >
                        <span>🎟</span>
                        <h3>Ví voucher đang trống</h3>
                    </div>

                    @if ($savedCoupons->hasPages())
                        <div data-saved-coupon-pagination>
                            {{ $savedCoupons
                                ->withQueryString()
                                ->links() }}
                        </div>
                    @endif
                @else
                    <div class="account-coupons-empty">
                        <span>🎟</span>
                        <h3>Bạn chưa lưu voucher nào</h3>
                        <p>
                            Chọn “Lưu voucher” ở danh sách phía trên.
                        </p>
                    </div>
                @endif
            </section>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .voucher-section {
            margin-top: 2rem;
        }

        .voucher-section__heading {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-end;
            margin-bottom: 1rem;
        }

        .voucher-section__heading h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .voucher-section__heading p {
            margin: .4rem 0 0;
            color: #6b7280;
        }

        .voucher-section__heading > span {
            color: #db2777;
            font-weight: 800;
        }

        .voucher-detail-list {
            display: grid;
            gap: .45rem;
            margin-top: .8rem;
            color: #6b7280;
            font-size: .82rem;
        }

        .voucher-restrictions {
            margin-top: .8rem;
            padding: .8rem;
            border-radius: .75rem;
            background: #fff7fb;
            color: #6b7280;
            font-size: .8rem;
        }

        .voucher-save-button {
            border: 1px solid #ec4899;
            border-radius: .65rem;
            padding: .65rem .9rem;
            background: #fff;
            color: #db2777;
            font-weight: 800;
            cursor: pointer;
        }

        .voucher-save-button:disabled {
            opacity: .6;
            cursor: default;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content');

                const message = document.querySelector(
                    '[data-voucher-page-message]'
                );

                const showMessage = function (
                    text,
                    type = 'success'
                ) {
                    if (! message) {
                        return;
                    }

                    message.textContent = text;
                    message.classList.toggle(
                        'is-error',
                        type === 'error'
                    );
                    message.classList.toggle(
                        'is-success',
                        type === 'success'
                    );
                };

                document
                    .querySelectorAll(
                        '[data-voucher-save]'
                    )
                    .forEach(function (button) {
                        button.addEventListener(
                            'click',
                            async function () {
                                if (
                                    ! csrfToken
                                    || button.disabled
                                ) {
                                    return;
                                }

                                button.disabled = true;
                                button.textContent =
                                    'Đang lưu...';

                                try {
                                    const response =
                                        await fetch(
                                            button.dataset
                                                .saveUrl,
                                            {
                                                method:
                                                    'POST',
                                                headers: {
                                                    Accept:
                                                        'application/json',
                                                    'X-CSRF-TOKEN':
                                                        csrfToken,
                                                    'X-Requested-With':
                                                        'XMLHttpRequest',
                                                },
                                                credentials:
                                                    'same-origin',
                                            }
                                        );

                                    const data =
                                        await response
                                            .json();

                                    if (
                                        ! response.ok
                                        || ! data.success
                                    ) {
                                        throw new Error(
                                            data.message
                                            || 'Không thể lưu voucher.'
                                        );
                                    }

                                    button.textContent =
                                        'Đã lưu';
                                    showMessage(
                                        data.message
                                    );
                                } catch (error) {
                                    button.disabled =
                                        false;
                                    button.textContent =
                                        'Lưu voucher';
                                    showMessage(
                                        error.message,
                                        'error'
                                    );
                                }
                            }
                        );
                    });
            }
        );
    </script>
@endpush
