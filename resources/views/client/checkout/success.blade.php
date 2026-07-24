@extends('client.layouts.master')

@section('title', 'Đặt hàng thành công - Cosmetic Shop')

@section('content')
    <section class="checkout-success">
        <div class="client-container">
            <div class="checkout-success__card">
                <div class="checkout-success__icon">
                    ✓
                </div>

                <p class="home-section__eyebrow">
                    Đặt hàng thành công
                </p>

                <h1>
                    Cảm ơn bạn đã mua hàng!
                </h1>

                <p class="checkout-success__message">
                    Đơn hàng của bạn đã được tiếp nhận và đang
                    chờ cửa hàng xác nhận.
                </p>

                <div class="checkout-success__code">
                    <span>Mã đơn hàng</span>

                    <strong>
                        {{ $order->order_code }}
                    </strong>
                </div>

                <div class="checkout-success__layout">
                    <section class="checkout-success__section">
                        <h2>Thông tin đơn hàng</h2>

                        <dl>
                            <div>
                                <dt>Trạng thái đơn</dt>
                                <dd>
                                    {{ $order->order_status }}
                                </dd>
                            </div>

                            <div>
                                <dt>Thanh toán</dt>
                                <dd>
                                    {{ strtoupper(
                                        $order->payment_method
                                    ) }}
                                    —
                                    {{ $order->payment_status }}
                                </dd>
                            </div>

                            <div>
                                <dt>Tổng số lượng</dt>
                                <dd>
                                    {{ $order->total_quantity }}
                                </dd>
                            </div>

                            <div>
                                <dt>Tổng thanh toán</dt>
                                <dd class="checkout-success__price">
                                    {{ number_format(
                                        $order->total_amount,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ
                                </dd>
                            </div>
                        </dl>
                    </section>

                    @if ($address)
                        <section class="checkout-success__section">
                            <h2>Thông tin nhận hàng</h2>

                            <p>
                                <strong>
                                    {{ $address->receiver_name }}
                                </strong>
                            </p>

                            <p>{{ $address->phone }}</p>

                            @if ($address->email)
                                <p>{{ $address->email }}</p>
                            @endif

                            <p>{{ $address->full_address }}</p>

                            @if ($address->note)
                                <p>
                                    Ghi chú:
                                    {{ $address->note }}
                                </p>
                            @endif
                        </section>
                    @endif
                </div>

                <section class="checkout-success__products">
                    <h2>Sản phẩm đã đặt</h2>

                    @foreach ($items as $item)
                        <article class="checkout-success__product">
                            <div class="checkout-success__product-image">
                                @if ($item->image_path)
                                    <img
                                        src="{{ asset(
                                            'images/'
                                            . $item->image_path
                                        ) }}"
                                        alt="{{ $item->product_name }}"
                                    >
                                @else
                                    <span>Cosmetic Shop</span>
                                @endif
                            </div>

                            <div>
                                <h3>
                                    {{ $item->product_name }}
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
                                    $item->total_price,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </strong>
                        </article>
                    @endforeach
                </section>

                <div class="checkout-success__actions">
                    <a href="{{ route('products.index') }}">
                        Tiếp tục mua sắm
                    </a>

                    <a href="{{ route('home') }}">
                        Về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
