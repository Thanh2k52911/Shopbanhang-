@extends('client.layouts.master')

@section('title', 'Tài khoản của tôi - Cosmetic Shop')

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
@endphp

@section('content')
    <section class="account-dashboard">
        <div class="client-container">
            <nav class="account-dashboard__breadcrumb">
                <a href="{{ route('home') }}">
                    Trang chủ
                </a>

                <span>/</span>

                <span>Tài khoản của tôi</span>
            </nav>

            <header class="account-dashboard__header">
                <div>
                    <p class="home-section__eyebrow">
                        Tài khoản khách hàng
                    </p>

                    <h1>
                        Xin chào,
                        {{ auth()->user()->name }}
                    </h1>

                    <p>
                        Quản lý thông tin tài khoản và theo dõi
                        các đơn hàng của bạn.
                    </p>
                </div>

                <a href="{{ route('products.index') }}">
                    Tiếp tục mua sắm
                </a>
            </header>

            <div class="account-statistics">
                <article class="account-statistic">
                    <span>📦</span>

                    <div>
                        <strong>{{ $totalOrders }}</strong>
                        <p>Tổng đơn hàng</p>
                    </div>
                </article>

                <article class="account-statistic">
                    <span>⏳</span>

                    <div>
                        <strong>{{ $pendingOrders }}</strong>
                        <p>Chờ xác nhận</p>
                    </div>
                </article>

                <article class="account-statistic">
                    <span>🚚</span>

                    <div>
                        <strong>{{ $shippingOrders }}</strong>
                        <p>Đang giao hàng</p>
                    </div>
                </article>

                <article class="account-statistic">
                    <span>✅</span>

                    <div>
                        <strong>{{ $completedOrders }}</strong>
                        <p>Đã hoàn thành</p>
                    </div>
                </article>

                <article class="account-statistic">
                    <span>❌</span>

                    <div>
                        <strong>{{ $cancelledOrders }}</strong>
                        <p>Đã hủy</p>
                    </div>
                </article>

                <article class="account-statistic">
                    <span>💰</span>

                    <div>
                        <strong>
                            {{ number_format(
                                $totalSpent,
                                0,
                                ',',
                                '.'
                            ) }}đ
                        </strong>

                        <p>Tổng chi tiêu</p>
                    </div>
                </article>
            </div>

            <div class="account-dashboard__layout">
                <section class="account-dashboard-card">
                    <div class="account-dashboard-card__heading">
                        <h2>Đơn hàng gần đây</h2>

                        <a href="{{ route(
                            'account.orders.index'
                        ) }}">
                            Xem tất cả
                        </a>
                    </div>

                    @if ($recentOrders->isNotEmpty())
                        <div class="account-recent-orders">
                            @foreach ($recentOrders as $order)
                                <article class="account-recent-order">
                                    <div>
                                        <a href="{{ route(
                                            'account.orders.show',
                                            $order->order_code
                                        ) }}">
                                            {{ $order->order_code }}
                                        </a>

                                        <p>
                                            {{ \Carbon\Carbon::parse(
                                                $order->created_at
                                            )->format('d/m/Y H:i') }}
                                        </p>
                                    </div>

                                    <span
                                        class="order-status-badge
                                            order-status-badge--{{ $order->order_status }}"
                                    >
                                        {{ $statusLabels[
                                            $order->order_status
                                        ] ?? $order->order_status }}
                                    </span>

                                    <div>
                                        <strong>
                                            {{ number_format(
                                                $order->total_amount,
                                                0,
                                                ',',
                                                '.'
                                            ) }}đ
                                        </strong>

                                        <p>
                                            {{ $order->total_quantity }}
                                            sản phẩm
                                        </p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="account-dashboard-card__empty">
                            <span>📦</span>

                            <p>
                                Bạn chưa có đơn hàng nào.
                            </p>

                            <a href="{{ route('products.index') }}">
                                Mua sắm ngay
                            </a>
                        </div>
                    @endif
                </section>

                <aside class="account-dashboard-card">
                    <div class="account-dashboard-card__heading">
                        <h2>Thông tin tài khoản</h2>
                    </div>

                    <dl class="account-profile-summary">
                        <div>
                            <dt>Họ và tên</dt>
                            <dd>{{ auth()->user()->name }}</dd>
                        </div>

                        <div>
                            <dt>Email</dt>
                            <dd>{{ auth()->user()->email }}</dd>
                        </div>

                        <div>
                            <dt>Ngày tham gia</dt>
                            <dd>
                                {{ auth()->user()
                                    ->created_at
                                    ?->format('d/m/Y') }}
                            </dd>
                        </div>
                    </dl>

                    <div class="account-dashboard-actions">
    <a href="{{ route('account.profile.edit') }}">
        Chỉnh sửa thông tin
    </a>

    <a href="{{ route('account.password.request') }}">
        Đổi mật khẩu
    </a>

    <a href="{{ route('account.orders.index') }}">
        Đơn hàng của tôi
    </a>

    <a href="{{ route('account.addresses.index') }}">
        Sổ địa chỉ
    </a>

    <a
        href="{{ route('account.loyalty.index') }}"
        class="account-action-card"
    >
        <span class="account-action-card__icon">
            💎
        </span>

        <div>
            <strong>
                Hạng thành viên & điểm thưởng
            </strong>
        </div>
    </a>

    <a
        href="{{ route('account.favorites.index') }}"
        class="account-action-card"
    >
        <span class="account-action-card__icon">
            ♡
        </span>

        <div>
            <strong>
                Sản phẩm yêu thích của tôi
            </strong>
        </div>
    </a>
</div>
            </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
