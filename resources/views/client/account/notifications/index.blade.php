@extends('client.layouts.master')

@section('title', 'Thông báo của tôi - Cosmetic Shop')

@section('content')
<section class="client-notifications-page">
    <div class="client-container">
        <div class="client-notifications-page__header">
            <div>
                <p class="client-notifications-page__eyebrow">TÀI KHOẢN</p>
                <h1>Thông báo của tôi</h1>
                <p>Cập nhật về đơn hàng, vận chuyển, đổi trả, hoàn tiền, loyalty và ưu đãi.</p>
            </div>

            @if ($unreadCount > 0)
                <form action="{{ route('account.notifications.read-all') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button class="client-notifications-page__read-all" type="submit">
                        Đánh dấu tất cả đã đọc
                    </button>
                </form>
            @endif
        </div>

        @if (session('success'))
            <div class="client-notifications-page__alert">{{ session('success') }}</div>
        @endif

        <div class="client-notifications-page__filters">
            @php
                $categories = [
                    '' => 'Tất cả',
                    'order' => 'Đơn hàng',
                    'shipping' => 'Vận chuyển',
                    'return' => 'Đổi trả / hoàn tiền',
                    'loyalty' => 'Loyalty',
                    'promotion' => 'Khuyến mãi',
                    'system' => 'Hệ thống',
                ];
            @endphp

            @foreach ($categories as $value => $label)
                <a
                    href="{{ route('account.notifications.index', array_filter(['category' => $value, 'status' => $status])) }}"
                    class="{{ $category === $value ? 'is-active' : '' }}"
                >
                    {{ $label }}
                </a>
            @endforeach

            <span class="client-notifications-page__filter-separator"></span>

            <a href="{{ route('account.notifications.index', array_filter(['category' => $category])) }}" class="{{ $status === '' ? 'is-active' : '' }}">Tất cả trạng thái</a>
            <a href="{{ route('account.notifications.index', array_filter(['category' => $category, 'status' => 'unread'])) }}" class="{{ $status === 'unread' ? 'is-active' : '' }}">Chưa đọc</a>
            <a href="{{ route('account.notifications.index', array_filter(['category' => $category, 'status' => 'read'])) }}" class="{{ $status === 'read' ? 'is-active' : '' }}">Đã đọc</a>
        </div>

        @if ($notifications->isEmpty())
            <div class="client-notifications-empty">
                <span>🔔</span>
                <h2>Chưa có thông báo</h2>
                <p>Các cập nhật quan trọng sẽ xuất hiện tại đây.</p>
            </div>
        @else
            <div class="client-notifications-list">
                @foreach ($notifications as $notification)
                    <article class="client-notification-item {{ $notification->isUnread() ? 'is-unread' : '' }}">
                        <a href="{{ route('account.notifications.open', $notification) }}" class="client-notification-item__main">
                            <span class="client-notification-item__icon">
                                @switch($notification->category)
                                    @case('order') 📦 @break
                                    @case('shipping') 🚚 @break
                                    @case('return') ↩️ @break
                                    @case('loyalty') ⭐ @break
                                    @case('promotion') 🎟️ @break
                                    @default 🔔
                                @endswitch
                            </span>

                            <span class="client-notification-item__content">
                                <span class="client-notification-item__title-row">
                                    <strong>{{ $notification->title }}</strong>
                                    @if ($notification->isUnread())
                                        <i aria-label="Chưa đọc"></i>
                                    @endif
                                </span>
                                @if ($notification->message)
                                    <span class="client-notification-item__message">{{ $notification->message }}</span>
                                @endif
                                <small>{{ $notification->created_at?->diffForHumans() }}</small>
                            </span>
                        </a>

                        <div class="client-notification-item__actions">
                            @if ($notification->isUnread())
                                <form action="{{ route('account.notifications.read', $notification) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit">Đã đọc</button>
                                </form>
                            @else
                                <form action="{{ route('account.notifications.unread', $notification) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit">Chưa đọc</button>
                                </form>
                            @endif

                            <form action="{{ route('account.notifications.destroy', $notification) }}" method="POST" onsubmit="return confirm('Xóa thông báo này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="is-danger">Xóa</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="client-notifications-pagination">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
