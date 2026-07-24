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

<div
    id="admin-notification-config"
    data-latest-url="{{ route('admin.notifications.latest') }}"
    data-read-all-url="{{ route('admin.notifications.read-all') }}"
    hidden
></div>
