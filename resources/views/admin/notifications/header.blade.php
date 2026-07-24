<header class="sticky top-0 z-30 border-b border-gray-200 bg-white">

    <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">

        {{-- Bên trái --}}
        <div class="flex items-center gap-3">

            {{-- Nút mở sidebar trên mobile --}}
            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 lg:hidden"
                @click="sidebarOpen = true"
                aria-label="Mở menu"
            >
                <svg
                    class="h-6 w-6"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.8"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"
                    />
                </svg>
            </button>

            {{-- Tiêu đề --}}
            <div>
                <h1 class="text-lg font-semibold text-gray-900">
                    @yield('page-title', 'Trang quản trị')
                </h1>

                @hasSection('page-description')
                    <p class="hidden text-sm text-gray-500 sm:block">
                        @yield('page-description')
                    </p>
                @endif
            </div>

        </div>

        {{-- Bên phải --}}
        <div class="flex items-center gap-2 sm:gap-4">

            {{-- Link trở về website --}}
            <a
                href="{{ route('home') }}"
                target="_blank"
                class="hidden items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 sm:flex"
            >
                <svg
                    class="h-5 w-5"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.8"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M13.5 4.5L21 12m0 0-7.5 7.5M21 12H3"
                    />
                </svg>

                Xem website
            </a>

            {{-- Chuông thông báo --}}
            <div
                class="relative"
                x-data="{ notificationOpen: false }"
                @click.outside="notificationOpen = false"
            >
                <button
                    type="button"
                    class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
                    aria-label="Thông báo"
                    @click="
                        notificationOpen = !notificationOpen;
                        if (notificationOpen) {
                            window.dispatchEvent(
                                new CustomEvent('admin-notifications-opened')
                            );
                        }
                    "
                >
                    <svg
                        class="h-6 w-6"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9a6 6 0 00-12 0v.75a8.967 8.967 0 01-2.312 6.022 23.848 23.848 0 005.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"
                        />
                    </svg>

                    <span
                        data-admin-notification-badge
                        class="absolute -right-1 -top-1 hidden min-w-5 rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[10px] font-bold leading-4 text-white"
                    >
                        0
                    </span>
                </button>

                <div
                    x-show="notificationOpen"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-[min(92vw,400px)] origin-top-right overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl"
                    style="display: none;"
                >
                    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                        <div>
                            <h2 class="font-semibold text-gray-900">
                                Thông báo
                            </h2>

                            <p
                                data-admin-notification-summary
                                class="mt-0.5 text-xs text-gray-500"
                            >
                                Đang tải dữ liệu...
                            </p>
                        </div>

                        <button
                            type="button"
                            data-admin-notification-read-all
                            class="text-xs font-semibold text-pink-600 hover:underline"
                        >
                            Đọc tất cả
                        </button>
                    </div>

                    <div
                        data-admin-notification-loading
                        class="px-4 py-10 text-center text-sm text-gray-500"
                    >
                        Đang tải thông báo...
                    </div>

                    <div
                        data-admin-notification-list
                        class="hidden max-h-[430px] divide-y divide-gray-100 overflow-y-auto"
                    ></div>

                    <div
                        data-admin-notification-empty
                        class="hidden px-4 py-10 text-center"
                    >
                        <div class="text-3xl">
                            🔔
                        </div>

                        <p class="mt-2 text-sm font-semibold text-gray-900">
                            Chưa có thông báo
                        </p>
                    </div>

                    <div class="border-t border-gray-100 p-3">
                        <a
                            href="{{ route('admin.notifications.index') }}"
                            class="flex w-full items-center justify-center rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800"
                        >
                            Xem tất cả thông báo
                        </a>
                    </div>
                </div>
            </div>

            {{-- Tài khoản Admin --}}
            <div
                class="relative"
                @click.outside="profileOpen = false"
            >

                <button
                    type="button"
                    class="flex items-center gap-3 rounded-lg px-2 py-1.5 transition hover:bg-gray-100"
                    @click="profileOpen = !profileOpen"
                >
                    <div
                        class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-pink-100 text-sm font-semibold text-pink-600"
                    >
                        @if (auth()->user()?->avatar)
                            <img
                                src="{{ asset('storage/' . auth()->user()->avatar) }}"
                                alt="{{ auth()->user()->name }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            {{ strtoupper(mb_substr(auth()->user()?->name ?? 'A', 0, 1)) }}
                        @endif
                    </div>

                    <div class="hidden text-left md:block">
                        <p class="max-w-36 truncate text-sm font-semibold text-gray-900">
                            {{ auth()->user()?->name ?? 'Quản trị viên' }}
                        </p>

                        <p class="max-w-36 truncate text-xs text-gray-500">
                            {{ auth()->user()?->email ?? '' }}
                        </p>
                    </div>

                    <svg
                        class="hidden h-4 w-4 text-gray-500 md:block"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19.5 9.75L12 17.25 4.5 9.75"
                        />
                    </svg>
                </button>

                <div
                    x-show="profileOpen"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-56 origin-top-right overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg"
                    style="display: none;"
                >
                    <div class="border-b border-gray-100 px-4 py-3">
                        <p class="truncate text-sm font-semibold text-gray-900">
                            {{ auth()->user()?->name ?? 'Quản trị viên' }}
                        </p>

                        <p class="truncate text-xs text-gray-500">
                            {{ auth()->user()?->email ?? '' }}
                        </p>
                    </div>

                    <div class="p-2">
                        <a
                            href="#"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-100"
                        >
                            <svg
                                class="h-5 w-5"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.118a7.5 7.5 0 0115 0A17.933 17.933 0 0112 21.75a17.933 17.933 0 01-7.5-1.632z"
                                />
                            </svg>

                            Hồ sơ cá nhân
                        </a>

                        <form
                            action="{{ route('logout') }}"
                            method="POST"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm text-red-600 transition hover:bg-red-50"
                            >
                                <svg
                                    class="h-5 w-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.8"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"
                                    />
                                </svg>

                                Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</header>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const latestUrl = @json(route('admin.notifications.latest'));
    const readAllUrl = @json(route('admin.notifications.read-all'));
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const badge = document.querySelector(
        '[data-admin-notification-badge]'
    );

    const summary = document.querySelector(
        '[data-admin-notification-summary]'
    );

    const loading = document.querySelector(
        '[data-admin-notification-loading]'
    );

    const list = document.querySelector(
        '[data-admin-notification-list]'
    );

    const empty = document.querySelector(
        '[data-admin-notification-empty]'
    );

    const readAllButton = document.querySelector(
        '[data-admin-notification-read-all]'
    );

    let loadedOnce = false;
    let loadingRequest = false;

    const escapeHtml = function (value) {
        const element = document.createElement('div');
        element.textContent = value ?? '';

        return element.innerHTML;
    };

    const iconForCategory = function (category) {
        const icons = {
            order: '🛍️',
            payment: '💳',
            shipping: '🚚',
            promotion: '🎁',
            review: '⭐',
            question: '❓',
            inventory: '📦',
            return: '↩️',
            contact: '📩',
            system: '🔔'
        };

        return icons[category] ?? '🔔';
    };

    const updateBadge = function (count) {
        if (!badge) {
            return;
        }

        const number = Number(count || 0);

        badge.textContent = number > 99
            ? '99+'
            : String(number);

        badge.classList.toggle(
            'hidden',
            number < 1
        );

        if (summary) {
            summary.textContent = number > 0
                ? number + ' thông báo chưa đọc'
                : 'Không có thông báo chưa đọc';
        }

        if (readAllButton) {
            readAllButton.classList.toggle(
                'hidden',
                number < 1
            );
        }
    };

    const renderNotifications = function (notifications) {
        if (!list || !empty || !loading) {
            return;
        }

        loading.classList.add('hidden');

        if (!Array.isArray(notifications) || notifications.length === 0) {
            list.classList.add('hidden');
            empty.classList.remove('hidden');

            return;
        }

        empty.classList.add('hidden');
        list.classList.remove('hidden');

        list.innerHTML = notifications.map(function (notification) {
            const unreadClass = notification.is_unread
                ? 'bg-pink-50'
                : 'bg-white';

            const dot = notification.is_unread
                ? '<span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-red-500"></span>'
                : '';

            return `
                <a
                    href="${escapeHtml(notification.open_url)}"
                    class="flex gap-3 px-4 py-3 transition hover:bg-gray-50 ${unreadClass}"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-lg">
                        ${iconForCategory(notification.category)}
                    </span>

                    <span class="min-w-0 flex-1">
                        <span class="flex items-start gap-2">
                            <strong class="line-clamp-2 flex-1 text-sm text-gray-900">
                                ${escapeHtml(notification.title || 'Thông báo')}
                            </strong>

                            ${dot}
                        </span>

                        <span class="mt-1 line-clamp-2 block text-xs leading-5 text-gray-500">
                            ${escapeHtml(notification.message || '')}
                        </span>

                        <span class="mt-1.5 block text-[11px] text-gray-400">
                            ${escapeHtml(notification.created_at_human || '')}
                        </span>
                    </span>
                </a>
            `;
        }).join('');
    };

    const loadNotifications = async function () {
        if (loadingRequest) {
            return;
        }

        loadingRequest = true;

        try {
            const response = await fetch(
                latestUrl + '?limit=10',
                {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                }
            );

            if (!response.ok) {
                throw new Error('Không thể tải thông báo.');
            }

            const payload = await response.json();

            updateBadge(payload.unread_count);
            renderNotifications(payload.notifications);

            loadedOnce = true;
        } catch (error) {
            console.error(error);

            if (loading) {
                loading.textContent = 'Không thể tải thông báo.';
            }
        } finally {
            loadingRequest = false;
        }
    };

    window.addEventListener(
        'admin-notifications-opened',
        function () {
            if (!loadedOnce) {
                loadNotifications();
            }
        }
    );

    readAllButton?.addEventListener(
        'click',
        async function () {
            try {
                const response = await fetch(
                    readAllUrl,
                    {
                        method: 'PATCH',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken ?? '',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({})
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        'Không thể đánh dấu tất cả đã đọc.'
                    );
                }

                updateBadge(0);
                loadedOnce = false;
                await loadNotifications();
            } catch (error) {
                console.error(error);
            }
        }
    );

    loadNotifications();

    window.setInterval(
        loadNotifications,
        30000
    );
});
</script>
@endpush
