@extends('client.layouts.master')

@section('title', 'Sổ địa chỉ - Cosmetic Shop')

@section('content')
<section class="account-profile-page">
    <div class="client-container">
        <nav class="account-profile-page__breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span>/</span>
            <a href="{{ route('account.index') }}">Tài khoản</a>
            <span>/</span>
            <span>Sổ địa chỉ</span>
        </nav>

        <header class="account-profile-page__header">
            <div>
                <p class="home-section__eyebrow">Địa chỉ nhận hàng</p>
                <h1>Sổ địa chỉ</h1>
                <p>Quản lý địa chỉ và chọn địa chỉ mặc định cho lần thanh toán tiếp theo.</p>
            </div>
            <a href="{{ route('account.addresses.create') }}">Thêm địa chỉ</a>
        </header>

        @if (session('address_success'))
            <div class="account-message account-message--success">
                {{ session('address_success') }}
            </div>
        @endif

        @if ($addresses->isEmpty())
            <section class="account-profile-card">
                <div class="account-dashboard-card__empty">
                    <span>📍</span>
                    <p>Bạn chưa lưu địa chỉ nhận hàng nào.</p>
                    <a href="{{ route('account.addresses.create') }}">Thêm địa chỉ đầu tiên</a>
                </div>
            </section>
        @else
            <div class="account-statistics">
                @foreach ($addresses as $address)
                    <article class="account-profile-card">
                        <div class="account-dashboard-card__heading">
                            <h2>{{ $address->receiver_name }}</h2>
                            @if ($address->is_default)
                                <span class="order-status-badge order-status-badge--completed">Mặc định</span>
                            @endif
                        </div>

                        <dl class="account-profile-information">
                            <div><dt>Số điện thoại</dt><dd>{{ $address->phone }}</dd></div>
                            <div><dt>Địa chỉ</dt><dd>{{ $address->full_address }}</dd></div>
                        </dl>

                        <div class="account-dashboard-actions">
                            <a href="{{ route('account.addresses.edit', $address) }}">Sửa địa chỉ</a>

                            @unless ($address->is_default)
                                <form method="POST" action="{{ route('account.addresses.default', $address) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit">Đặt làm mặc định</button>
                                </form>
                            @endunless

                            <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('Xóa địa chỉ này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Xóa</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
