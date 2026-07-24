@extends('admin.layouts.master')

@section('title', 'Thông báo quản trị')
@section('page-title', 'Thông báo quản trị')
@section('page-description', 'Theo dõi đơn hàng, đánh giá, hỏi đáp, tồn kho và các sự kiện mới trong hệ thống.')

@section('content')
@php
    $categoryLabels = [
        'order' => 'Đơn hàng',
        'payment' => 'Thanh toán',
        'shipping' => 'Vận chuyển',
        'promotion' => 'Khuyến mãi',
        'review' => 'Đánh giá',
        'question' => 'Hỏi đáp',
        'inventory' => 'Tồn kho',
        'return' => 'Đổi trả',
        'contact' => 'Liên hệ',
        'system' => 'Hệ thống',
    ];

    $categoryIcons = [
        'order' => '🛍️',
        'payment' => '💳',
        'shipping' => '🚚',
        'promotion' => '🎁',
        'review' => '⭐',
        'question' => '❓',
        'inventory' => '📦',
        'return' => '↩️',
        'contact' => '📩',
        'system' => '🔔',
    ];

    $priorityClasses = [
        'low' => 'bg-gray-100 text-gray-700',
        'normal' => 'bg-blue-100 text-blue-700',
        'high' => 'bg-orange-100 text-orange-700',
        'urgent' => 'bg-red-100 text-red-700',
    ];

    $priorityLabels = [
        'low' => 'Thấp',
        'normal' => 'Bình thường',
        'high' => 'Cao',
        'urgent' => 'Khẩn cấp',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Trung tâm thông báo
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Quản lý toàn bộ thông báo thuộc tài khoản quản trị hiện tại.
            </p>
        </div>

        @if (($statistics['unread'] ?? 0) > 0)
            <form
                method="POST"
                action="{{ route('admin.notifications.read-all') }}"
            >
                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-pink-700"
                >
                    Đánh dấu tất cả đã đọc
                </button>
            </form>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['Tổng thông báo', $statistics['total'] ?? 0, 'text-gray-900'],
            ['Chưa đọc', $statistics['unread'] ?? 0, 'text-red-600'],
            ['Đã đọc', $statistics['read'] ?? 0, 'text-green-600'],
            ['Ưu tiên cao', $statistics['high'] ?? 0, 'text-orange-600'],
            ['Khẩn cấp', $statistics['urgent'] ?? 0, 'text-red-700'],
        ] as [$label, $value, $class])
            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    {{ $label }}
                </p>

                <strong class="mt-2 block text-2xl {{ $class }}">
                    {{ number_format((int) $value) }}
                </strong>
            </article>
        @endforeach
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form
            method="GET"
            action="{{ route('admin.notifications.index') }}"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4"
        >
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Trạng thái
                </label>

                <select
                    name="status"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="all" @selected(request('status', 'all') === 'all')>
                        Tất cả
                    </option>

                    <option value="unread" @selected(request('status') === 'unread')>
                        Chưa đọc
                    </option>

                    <option value="read" @selected(request('status') === 'read')>
                        Đã đọc
                    </option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Danh mục
                </label>

                <select
                    name="category"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">
                        Tất cả
                    </option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category }}"
                            @selected(request('category') === $category)
                        >
                            {{ $categoryLabels[$category] ?? ucfirst($category) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Số bản ghi
                </label>

                <select
                    name="per_page"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    @foreach ([10, 20, 30, 50, 100] as $perPage)
                        <option
                            value="{{ $perPage }}"
                            @selected((int) request('per_page', 20) === $perPage)
                        >
                            {{ $perPage }} thông báo
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-3">
                <button
                    type="submit"
                    class="flex-1 rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white"
                >
                    Lọc dữ liệu
                </button>

                <a
                    href="{{ route('admin.notifications.index') }}"
                    class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700"
                >
                    Đặt lại
                </a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-lg font-bold text-gray-900">
                Danh sách thông báo
            </h3>

            <p class="mt-1 text-sm text-gray-500">
                Hiển thị {{ $notifications->count() }} / {{ $notifications->total() }} kết quả.
            </p>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse ($notifications as $notification)
                @php
                    $isUnread = $notification->isUnread();
                    $category = $notification->category ?: 'system';
                    $priority = $notification->priority ?: 'normal';
                @endphp

                <article
    data-notification-item
    data-notification-id="{{ $notification->id }}"
    data-notification-status="{{ $isUnread ? 'unread' : 'read' }}"
    class="
        flex flex-col gap-4 p-5 transition
        md:flex-row md:items-start
        {{ $isUnread ? 'bg-pink-50/60' : 'bg-white' }}
        hover:bg-gray-50
    "
>
                    <div
                        class="
                            flex h-12 w-12 shrink-0 items-center justify-center
                            rounded-xl text-xl
                            {{ $isUnread ? 'bg-pink-100' : 'bg-gray-100' }}
                        "
                    >
                        {{ $categoryIcons[$category] ?? '🔔' }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <a
                                href="{{ route('admin.notifications.open', $notification->id) }}"
                                class="font-semibold text-gray-900 hover:text-pink-600"
                            >
                                {{ $notification->title ?: 'Thông báo hệ thống' }}
                            </a>

                            <span
    data-notification-unread-dot
    class="
        h-2.5 w-2.5 rounded-full bg-red-500
        {{ $isUnread ? '' : 'hidden' }}
    "
></span>

                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                {{ $categoryLabels[$category] ?? ucfirst($category) }}
                            </span>

                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $priorityClasses[$priority] ?? $priorityClasses['normal'] }}">
                                {{ $priorityLabels[$priority] ?? ucfirst($priority) }}
                            </span>
                        </div>

                        @if ($notification->message)
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-600">
                                {{ $notification->message }}
                            </p>
                        @endif

                        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-400">
                            <span>
                                {{ optional($notification->created_at)->diffForHumans() }}
                            </span>

                            <span>
                                {{ optional($notification->created_at)->format('d/m/Y H:i:s') }}
                            </span>

                            @if ($notification->type)
                                <span class="font-mono">
                                    {{ $notification->type }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-wrap gap-2 md:justify-end">
                        @if ($notification->action_url)
                            <a
                                href="{{ route('admin.notifications.open', $notification->id) }}"
                                class="rounded-lg bg-pink-600 px-3 py-2 text-xs font-semibold text-white hover:bg-pink-700"
                            >
                                Mở
                            </a>
                        @endif

                        <button
    type="button"
    data-notification-toggle
    data-read-url="{{ route(
        'admin.notifications.read',
        $notification->id
    ) }}"
    data-unread-url="{{ route(
        'admin.notifications.unread',
        $notification->id
    ) }}"
    data-next-status="{{ $isUnread ? 'read' : 'unread' }}"
    class="
        rounded-lg border px-3 py-2
        text-xs font-semibold transition
        {{ $isUnread
            ? 'border-green-200 bg-green-50 text-green-700'
            : 'border-blue-200 bg-blue-50 text-blue-700'
        }}
    "
>
    {{ $isUnread ? 'Đã đọc' : 'Chưa đọc' }}
</button>

                        <button
    type="button"
    data-notification-delete
    data-delete-url="{{ route(
        'admin.notifications.destroy',
        $notification->id
    ) }}"
    class="
        rounded-lg border border-red-200
        bg-red-50 px-3 py-2
        text-xs font-semibold text-red-700
        transition hover:bg-red-100
    "
>
    Xóa
</button>
                    </div>
                </article>
            @empty
                <div class="px-5 py-16 text-center">
                    <div class="text-4xl">
                        🔔
                    </div>

                    <p class="mt-3 font-semibold text-gray-900">
                        Chưa có thông báo
                    </p>

                    <p class="mt-1 text-sm text-gray-500">
                        Các sự kiện mới của hệ thống sẽ xuất hiện tại đây.
                    </p>
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
