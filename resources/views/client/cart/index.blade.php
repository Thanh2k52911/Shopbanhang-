@extends('client.layouts.master')

@section('title', 'Giỏ hàng - Cosmetic Shop')

@section('content')
    <section class="cart-page">
        <div class="client-container">
            <nav class="cart-page__breadcrumb">
                <a href="{{ route('home') }}">
                    Trang chủ
                </a>

                <span>/</span>

                <span>Giỏ hàng</span>
            </nav>

            <header class="cart-page__header">
                <div>
                    <p class="home-section__eyebrow">
                        Đơn hàng của bạn
                    </p>

                    <h1>Giỏ hàng</h1>
                </div>

                <a href="{{ route('products.index') }}">
                    Tiếp tục mua sắm
                </a>
            </header>

            @if ($items->isNotEmpty())
                <div class="cart-page__layout">
                    <div class="cart-items">
                        @foreach ($items as $item)
                            <article
                                class="cart-item"
                                data-cart-item
                                data-item-id="{{ $item->id }}"
                            >
                                <a
                                    href="{{ route(
                                        'products.show',
                                        $item->slug
                                    ) }}"
                                    class="cart-item__image"
                                >
                                    @if ($item->image_path)
                                        <img
                                            src="{{ asset(
                                                'images/' . $item->image_path
                                            ) }}"
                                            alt="{{ $item->name }}"
                                        >
                                    @else
                                        <span>Cosmetic Shop</span>
                                    @endif
                                </a>

                                <div class="cart-item__info">
                                    @if ($item->brand_name)
                                        <p class="cart-item__brand">
                                            {{ $item->brand_name }}
                                        </p>
                                    @endif

                                    <h2>
                                        <a href="{{ route(
                                            'products.show',
                                            $item->slug
                                        ) }}">
                                            {{ $item->name }}
                                        </a>
                                    </h2>

                                    <p class="cart-item__sku">
                                        SKU:
                                        {{ $item->sku_code }}
                                    </p>

                                    @if ($item->variant_name)
                                        <p class="cart-item__variant">
                                            {{ $item->variant_name }}
                                        </p>
                                    @endif

                                    <div class="cart-item__prices">
                                        <strong>
                                            {{ number_format(
                                                $item->final_unit_price,
                                                0,
                                                ',',
                                                '.'
                                            ) }}đ
                                        </strong>

                                        @if (
                                            $item->discount_amount > 0
                                        )
                                            <del>
                                                {{ number_format(
                                                    $item->unit_price,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}đ
                                            </del>
                                        @endif
                                    </div>
                                </div>

                                <div class="cart-item__quantity">
                                    <button
                                        type="button"
                                        data-cart-minus
                                    >
                                        −
                                    </button>

                                    <input
                                        type="number"
                                        min="1"
                                        max="{{ max(
                                            1,
                                            (int) $item
                                                ->available_quantity
                                        ) }}"
                                        value="{{ $item->quantity }}"
                                        data-cart-quantity
                                    >

                                    <button
                                        type="button"
                                        data-cart-plus
                                    >
                                        +
                                    </button>
                                </div>

                                <div class="cart-item__total">
                                    <span>Thành tiền</span>

                                    <strong data-line-total>
                                        {{ number_format(
                                            $item->line_total,
                                            0,
                                            ',',
                                            '.'
                                        ) }}đ
                                    </strong>
                                </div>

                                <button
                                    type="button"
                                    class="cart-item__remove"
                                    data-cart-remove
                                    aria-label="Xóa sản phẩm"
                                >
                                    ×
                                </button>
                            </article>
                        @endforeach
                    </div>

                    <aside class="cart-summary">
                        <h2>Tóm tắt đơn hàng</h2>

                        <dl>
                            <div>
                                <dt>Tạm tính</dt>
                                <dd data-cart-subtotal>
                                    {{ number_format(
                                        $subtotal,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ
                                </dd>
                            </div>

                            <div>
                                <dt>Giảm giá</dt>
                                <dd data-cart-discount>
                                    -{{ number_format(
                                        $discountTotal,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ
                                </dd>
                            </div>

                            <div class="cart-summary__total">
                                <dt>Tổng cộng</dt>
                                <dd data-cart-grand-total>
                                    {{ number_format(
                                        $grandTotal,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ
                                </dd>
                            </div>
                        </dl>

                        <a href="{{ route('checkout.index', ['cart' => 1]) }}" class="cart-summary__checkout">
                            Tiến hành thanh toán
                        </a>
                    </aside>
                </div>
            @else
                <div class="cart-empty">
                    <span>🛒</span>

                    <h2>Giỏ hàng đang trống</h2>

                    <p>
                        Hãy thêm sản phẩm yêu thích vào giỏ hàng.
                    </p>

                    <a href="{{ route('products.index') }}">
                        Khám phá sản phẩm
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection
