@php
    $headerSiteName = site_name();
    $headerLogo = site_logo();
    $headerInitial = mb_strtoupper(
        mb_substr(
            $headerSiteName,
            0,
            1
        )
    );
@endphp

<header class="client-header">
    <div class="client-container client-header__inner">

        <a
            href="{{ route('home') }}"
            class="client-header__logo"
            aria-label="{{ $headerSiteName }}"
        >
            @if ($headerLogo)
                <span
                    class="client-header__logo-mark"
                    style="
                        overflow: hidden;
                        background: #ffffff;
                    "
                >
                    <img
                        src="{{ $headerLogo }}"
                        alt="{{ $headerSiteName }}"
                        style="
                            width: 100%;
                            height: 100%;
                            object-fit: contain;
                        "
                    >
                </span>
            @else
                <span class="client-header__logo-mark">
                    {{ $headerInitial }}
                </span>
            @endif

            <span class="client-header__logo-text">
                {{ $headerSiteName }}
            </span>
        </a>

        <form
            action="{{ route('products.index') }}"
            method="GET"
            class="client-header__search"
        >
            <input
                type="search"
                name="keyword"
                value="{{ request('keyword') }}"
                placeholder="Tìm kiếm sản phẩm, thương hiệu..."
                aria-label="Tìm kiếm sản phẩm"
            >

            <button type="submit">
                Tìm kiếm
            </button>
        </form>

        <div class="client-header__actions">
            @auth
                <a
                    href="{{ route('account.favorites.index') }}"
                    class="client-header__action"
                >
                    <span class="client-header__action-icon">
                        ♡
                    </span>

                    <span>
                        Yêu thích
                    </span>

                    <span
                        class="client-header__badge"
                        data-favorites-count
                        @if (($favoritesCount ?? 0) < 1)
                            hidden
                        @endif
                    >
                        {{ $favoritesCount ?? 0 }}
                    </span>
                </a>
            @else
                <a
                    href="{{ route('login') }}"
                    class="client-header__action"
                >
                    <span class="client-header__action-icon">
                        ♡
                    </span>

                    <span>
                        Yêu thích
                    </span>
                </a>
            @endauth

            <a
                href="{{ route('cart.index') }}"
                class="client-header__action client-header__cart"
            >
                <span class="client-header__action-icon">
                    🛒
                </span>

                <span>
                    Giỏ hàng
                </span>

                <span
                    class="client-header__badge"
                    data-cart-count
                >
                    {{ $cartCount ?? 0 }}
                </span>
            </a>
                <a
                    href="{{ route('account.support-chat.show') }}"
                    class="client-header__action"
                >
                    <span class="client-header__action-icon">💬</span>
                    <span>Trò chuyện với cửa hàng</span>
                </a>

            @auth
                <div class="client-notification-menu" data-client-notification-menu>
                    <button
                        type="button"
                        class="client-header__action client-notification-menu__toggle"
                        data-client-notification-toggle
                        aria-expanded="false"
                        aria-label="Mở thông báo"
                    >
                        <span class="client-header__action-icon">🔔</span>
                        <span>Thông báo</span>
                        <span
                            class="client-header__badge"
                            data-client-notification-badge
                            @if (($headerUnreadNotificationCount ?? 0) < 1) hidden @endif
                        >
                            {{ $headerUnreadNotificationCount ?? 0 }}
                        </span>
                    </button>

                    <div class="client-notification-menu__dropdown" data-client-notification-dropdown hidden>
                        <div class="client-notification-menu__header">
                            <strong>Thông báo</strong>
                            @if (($headerUnreadNotificationCount ?? 0) > 0)
                                <form action="{{ route('account.notifications.read-all') }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit">Đọc tất cả</button>
                                </form>
                            @endif
                        </div>

                        <div class="client-notification-menu__list">
                            @forelse (($headerNotifications ?? collect()) as $headerNotification)
                                <a
                                    href="{{ route('account.notifications.open', $headerNotification) }}"
                                    class="client-notification-menu__item {{ $headerNotification->isUnread() ? 'is-unread' : '' }}"
                                >
                                    <span class="client-notification-menu__icon">
                                        @switch($headerNotification->category)
                                            @case('order') 📦 @break
                                            @case('shipping') 🚚 @break
                                            @case('return') ↩️ @break
                                            @case('loyalty') ⭐ @break
                                            @case('promotion') 🎟️ @break
                                            @default 🔔
                                        @endswitch
                                    </span>
                                    <span>
                                        <strong>{{ $headerNotification->title }}</strong>
                                        @if ($headerNotification->message)
                                            <small>{{ Str::limit($headerNotification->message, 82) }}</small>
                                        @endif
                                        <time>{{ $headerNotification->created_at?->diffForHumans() }}</time>
                                    </span>
                                </a>
                            @empty
                                <div class="client-notification-menu__empty">Chưa có thông báo mới.</div>
                            @endforelse
                        </div>

                        <a href="{{ route('account.notifications.index') }}" class="client-notification-menu__all">
                            Xem tất cả thông báo
                        </a>
                    </div>
                </div>

                <div class="client-header__account">
                    <a
                        href="{{ route('account.index') }}"
                        class="client-header__user"
                    >
                        <span class="client-header__avatar">
                            @if (auth()->user()->avatar)
                                <img
                                    src="{{ asset(
                                        'storage/'
                                        . ltrim(
                                            auth()->user()->avatar,
                                            '/'
                                        )
                                    ) }}"
                                    alt="{{ auth()->user()->name }}"
                                >
                            @else
                                <span>
                                    {{ mb_strtoupper(
                                        mb_substr(
                                            auth()->user()->name,
                                            0,
                                            1
                                        )
                                    ) }}
                                </span>
                            @endif
                        </span>

                        <div>
                            <span class="client-header__user-label">
                                Tài khoản
                            </span>

                            <strong>
                                {{ auth()->user()->name }}
                            </strong>
                        </div>

                    </a>

                    <form
                        action="{{ route('logout') }}"
                        method="POST"
                    >
                        @csrf

                        <button
    type="submit"
    class="client-account__logout"
>
    Đăng xuất
</button>
                    </form>
                </div>
            @else
                <a
                    href="{{ route('login') }}"
                    class="client-header__action"
                >
                    <span class="client-header__action-icon">
                        👤
                    </span>

                    <span>
                        Đăng nhập
                    </span>
                </a>

                <a
                    href="{{ route('register') }}"
                    class="
                        client-header__action
                        client-header__action--register
                    "
                >
                    <span>
                        Đăng ký
                    </span>
                </a>
            @endauth
        </div>

    </div>
</header>
