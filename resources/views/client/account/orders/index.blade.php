@extends('client.layouts.master')

@section('title', 'Đơn hàng của tôi - Cosmetic Shop')

@php
    $statusLabels = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'processing' => 'Đang xử lý',
        'packed' => 'Đã đóng gói',
        'shipping' => 'Đang giao hàng',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];

    $filterStatuses = [
        '' => 'Tất cả',
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'processing' => 'Đang xử lý',
        'packed' => 'Đã đóng gói',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];
@endphp

@section('content')
    <section class="account-orders">
        <div class="client-container">
            <nav class="account-orders__breadcrumb">
                <a href="{{ route('home') }}">
                    Trang chủ
                </a>

                <span>/</span>

                <span>Đơn hàng của tôi</span>
            </nav>

            <header class="account-orders__header">
                <div>
                    <p class="home-section__eyebrow">
                        Tài khoản
                    </p>

                    <h1>Đơn hàng của tôi</h1>

                    <p>
                        Theo dõi trạng thái và xem lại các đơn hàng
                        bạn đã đặt.
                    </p>
                </div>

                <a href="{{ route('products.index') }}">
                    Tiếp tục mua sắm
                </a>
            </header>

            <nav class="order-status-tabs">
                @foreach ($filterStatuses as $value => $label)
                    @php
                        $count = $value === ''
                            ? $statusCounts->sum()
                            : (int) $statusCounts->get($value, 0);
                    @endphp

                    <a
                        href="{{ route(
                            'account.orders.index',
                            $value !== ''
                                ? ['status' => $value]
                                : []
                        ) }}"
                        class="{{ $status === $value
                            ? 'order-status-tabs__item--active'
                            : '' }}"
                    >
                        {{ $label }}

                        <span>{{ $count }}</span>
                    </a>
                @endforeach
            </nav>

            @if ($orders->isNotEmpty())
                <div class="account-order-list">
                    @foreach ($orders as $order)
                        <article class="account-order-card">
                            <header class="account-order-card__header">
                                <div>
                                    <span>Mã đơn hàng</span>

                                    <strong>
                                        {{ $order->order_code }}
                                    </strong>
                                </div>

                                <span
                                    class="order-status-badge
                                        order-status-badge--{{ $order->order_status }}"
                                >
                                    {{ $statusLabels[
                                        $order->order_status
                                    ] ?? $order->order_status }}
                                </span>
                            </header>

                            <div class="account-order-card__body">
                                <div class="account-order-card__image">
                                    @if ($order->first_image)
                                        <img
                                            src="{{ asset(
                                                'images/'
                                                . $order->first_image
                                            ) }}"
                                            alt="Sản phẩm trong đơn hàng"
                                        >
                                    @else
                                        <span>Cosmetic Shop</span>
                                    @endif
                                </div>

                                <div class="account-order-card__information">
                                    <p>
                                        Ngày đặt:
                                        <strong>
                                            {{ \Carbon\Carbon::parse(
                                                $order->created_at
                                            )->format('d/m/Y H:i') }}
                                        </strong>
                                    </p>

                                    <p>
                                        Số loại sản phẩm:
                                        <strong>
                                            {{ $order->item_count }}
                                        </strong>
                                    </p>

                                    <p>
                                        Tổng số lượng:
                                        <strong>
                                            {{ $order->total_quantity }}
                                        </strong>
                                    </p>

                                    <p>
                                        Thanh toán:
                                        <strong>
                                            {{ strtoupper(
                                                $order->payment_method
                                            ) }}
                                            —
                                            {{ $order->payment_status }}
                                        </strong>
                                    </p>
                                </div>

                                <div class="account-order-card__total">
                                    <span>Tổng thanh toán</span>

                                    <strong>
                                        {{ number_format(
                                            $order->total_amount,
                                            0,
                                            ',',
                                            '.'
                                        ) }}đ
                                    </strong>

                                    <a href="{{ route(
                                        'account.orders.show',
                                        $order->order_code
                                    ) }}">
                                        Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="product-pagination">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="account-orders__empty">
                    <span>📦</span>

                    <h2>Chưa có đơn hàng</h2>

                    <p>
                        Bạn chưa có đơn hàng nào ở trạng thái này.
                    </p>

                    <a href="{{ route('products.index') }}">
                        Mua sắm ngay
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection
