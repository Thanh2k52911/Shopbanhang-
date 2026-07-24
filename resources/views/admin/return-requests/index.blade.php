@extends('admin.layouts.master')

@section('title', 'Quản lý yêu cầu trả hàng')

@section('page-title', 'Quản lý yêu cầu trả hàng')

@section(
    'page-description',
    'Theo dõi, xét duyệt và xử lý toàn bộ yêu cầu trả hàng, đổi hàng và hoàn tiền.'
)

@section('content')

    {{-- Thống kê nhanh --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-7">

        <div class="admin-card p-5">

            <p class="text-sm font-medium text-gray-500">
                Tổng yêu cầu
            </p>

            <p class="mt-2 text-2xl font-bold text-gray-900">
                {{ number_format($statistics['total']) }}
            </p>

        </div>

        <div class="admin-card p-5">

            <p class="text-sm font-medium text-gray-500">
                Chờ xử lý
            </p>

            <p class="mt-2 text-2xl font-bold text-amber-600">
                {{ number_format($statistics['pending']) }}
            </p>

        </div>

        <div class="admin-card p-5">

            <p class="text-sm font-medium text-gray-500">
                Đã chấp thuận
            </p>

            <p class="mt-2 text-2xl font-bold text-blue-600">
                {{ number_format($statistics['approved']) }}
            </p>

        </div>

        <div class="admin-card p-5">

            <p class="text-sm font-medium text-gray-500">
                Đã nhận hàng
            </p>

            <p class="mt-2 text-2xl font-bold text-cyan-600">
                {{ number_format($statistics['received']) }}
            </p>

        </div>

        <div class="admin-card p-5">

            <p class="text-sm font-medium text-gray-500">
                Đang xử lý
            </p>

            <p class="mt-2 text-2xl font-bold text-indigo-600">
                {{ number_format($statistics['processing'] ?? 0) }}
            </p>

        </div>

        <div class="admin-card p-5">

            <p class="text-sm font-medium text-gray-500">
                Đã hoàn tất
            </p>

            <p class="mt-2 text-2xl font-bold text-green-600">
                {{ number_format($statistics['completed']) }}
            </p>

        </div>

        <div class="admin-card p-5">

            <p class="text-sm font-medium text-gray-500">
                Đã từ chối
            </p>

            <p class="mt-2 text-2xl font-bold text-red-600">
                {{ number_format($statistics['rejected']) }}
            </p>

        </div>

    </div>

    {{-- Bộ lọc --}}
    <div class="admin-card mt-6 p-5">

        <div class="mb-5">

            <h2 class="text-base font-semibold text-gray-900">
                Tìm kiếm và lọc yêu cầu
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Tìm theo mã yêu cầu, mã đơn hàng, khách hàng, số điện thoại hoặc lý do trả hàng.
            </p>

        </div>

        <form
            action="{{ route('admin.return-requests.index') }}"
            method="GET"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6"
        >

            {{-- Từ khóa --}}
            <div class="xl:col-span-2">

                <label
                    for="keyword"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Từ khóa
                </label>

                <input
                    id="keyword"
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    class="admin-input"
                    placeholder="Mã yêu cầu, mã đơn, khách hàng..."
                >

            </div>

            {{-- Trạng thái --}}
            <div>

                <label
                    for="status"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Trạng thái
                </label>

                <select
                    id="status"
                    name="status"
                    class="admin-select"
                >
                    <option value="">
                        Tất cả trạng thái
                    </option>

                    @foreach ($statuses as $status => $label)
                        <option
                            value="{{ $status }}"
                            @selected(request('status') === $status)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

            </div>

            {{-- Loại yêu cầu --}}
            <div>

                <label
                    for="request_type"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Loại yêu cầu
                </label>

                <select
                    id="request_type"
                    name="request_type"
                    class="admin-select"
                >
                    <option value="">
                        Tất cả loại
                    </option>

                    @foreach ($requestTypes as $type => $label)
                        <option
                            value="{{ $type }}"
                            @selected(request('request_type') === $type)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

            </div>

            {{-- Từ ngày --}}
            <div>

                <label
                    for="date_from"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Từ ngày
                </label>

                <input
                    id="date_from"
                    type="date"
                    name="date_from"
                    value="{{ request('date_from') }}"
                    class="admin-input"
                >

            </div>

            {{-- Đến ngày --}}
            <div>

                <label
                    for="date_to"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Đến ngày
                </label>

                <input
                    id="date_to"
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="admin-input"
                >

            </div>

            {{-- Nút --}}
            <div class="flex items-end gap-3 md:col-span-2 xl:col-span-6">

                <button
                    type="submit"
                    class="admin-btn admin-btn-primary"
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
                            d="M3.75 4.5h16.5l-6.75 7.5v5.25l-3 1.5V12L3.75 4.5z"
                        />
                    </svg>

                    Áp dụng bộ lọc
                </button>

                <a
                    href="{{ route('admin.return-requests.index') }}"
                    class="admin-btn admin-btn-secondary"
                >
                    Xóa lọc
                </a>

            </div>

        </form>

    </div>

    {{-- Danh sách --}}
    <div class="admin-card mt-6 overflow-hidden">

        <div
            class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >

            <div>

                <h2 class="text-base font-semibold text-gray-900">
                    Danh sách yêu cầu trả hàng
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Tìm thấy {{ number_format($returnRequests->total()) }} yêu cầu.
                </p>

            </div>

            <p class="text-sm text-gray-500">
                Trang {{ $returnRequests->currentPage() }}/{{ $returnRequests->lastPage() }}
            </p>

        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>

                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Mã yêu cầu
                        </th>

                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Đơn hàng
                        </th>

                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Khách hàng
                        </th>

                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Loại yêu cầu
                        </th>

                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Sản phẩm
                        </th>

                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Số tiền yêu cầu
                        </th>

                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Trạng thái
                        </th>

                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Ngày tạo
                        </th>

                        <th
                            class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Thao tác
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">

                    @forelse ($returnRequests as $returnRequest)

                        @php
                            $statusClass = match ($returnRequest->status) {
                                'pending' =>
                                    'bg-amber-100 text-amber-700',

                                'approved' =>
                                    'bg-blue-100 text-blue-700',

                                'waiting_for_return' =>
                                    'bg-purple-100 text-purple-700',

                                'received' =>
                                    'bg-cyan-100 text-cyan-700',

                                'processing' =>
                                    'bg-indigo-100 text-indigo-700',

                                'completed' =>
                                    'bg-green-100 text-green-700',

                                'rejected' =>
                                    'bg-red-100 text-red-700',

                                'cancelled' =>
                                    'bg-gray-200 text-gray-700',

                                default =>
                                    'bg-gray-100 text-gray-700',
                            };

                            $typeClass = match ($returnRequest->request_type) {
                                'return' =>
                                    'bg-blue-50 text-blue-700',

                                'refund' =>
                                    'bg-green-50 text-green-700',

                                'return_refund' =>
                                    'bg-purple-50 text-purple-700',

                                'exchange' =>
                                    'bg-cyan-50 text-cyan-700',

                                default =>
                                    'bg-gray-100 text-gray-700',
                            };
                        @endphp

                        <tr class="transition hover:bg-gray-50">

                            {{-- Mã yêu cầu --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                <p class="font-semibold text-gray-900">
                                    {{ $returnRequest->return_code }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    ID: {{ $returnRequest->id }}
                                </p>

                                @if ($returnRequest->images_count > 0)
                                    <p class="mt-1 text-xs text-blue-600">
                                        {{ $returnRequest->images_count }} hình ảnh
                                    </p>
                                @endif

                            </td>

                            {{-- Đơn hàng --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                @if ($returnRequest->order)

                                    <a
                                        href="{{ route(
                                            'admin.orders.show',
                                            $returnRequest->order
                                        ) }}"
                                        class="font-medium text-gray-900 hover:text-pink-600"
                                    >
                                        {{ $returnRequest->order->order_code }}
                                    </a>

                                    <p class="mt-1 text-xs text-gray-500">
                                        Đơn:
                                        {{ $returnRequest->order->order_status }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        Thanh toán:
                                        {{ $returnRequest->order->payment_status }}
                                    </p>

                                @else

                                    <span class="text-sm text-gray-500">
                                        Không tìm thấy đơn
                                    </span>

                                @endif

                            </td>

                            {{-- Khách hàng --}}
                            <td class="min-w-[190px] px-5 py-4">

                                <p class="font-medium text-gray-900">
                                    {{ $returnRequest->user?->name
                                        ?? $returnRequest->order?->customer_name
                                        ?? 'Chưa có' }}
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $returnRequest->order?->customer_phone
                                        ?? $returnRequest->user?->email
                                        ?? 'Chưa có thông tin' }}
                                </p>

                            </td>

                            {{-- Loại yêu cầu --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                <span
                                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $typeClass }}"
                                >
                                    {{ $requestTypes[$returnRequest->request_type]
                                        ?? $returnRequest->request_type }}
                                </span>

                                <p
                                    class="mt-2 max-w-[220px] truncate text-sm text-gray-500"
                                    title="{{ $returnRequest->reason }}"
                                >
                                    {{ $returnRequest->reason }}
                                </p>

                            </td>

                            {{-- Sản phẩm --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                <p class="font-semibold text-gray-900">
                                    {{ number_format($returnRequest->items_count) }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    dòng sản phẩm
                                </p>

                            </td>

                            {{-- Số tiền --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                <p class="font-bold text-pink-600">
                                    {{ number_format(
                                        (float) $returnRequest->requested_amount,
                                        0,
                                        ',',
                                        '.'
                                    ) }}₫
                                </p>

                                @if (! is_null($returnRequest->approved_amount))
                                    <p class="mt-1 text-xs text-green-600">
                                        Duyệt:
                                        {{ number_format(
                                            (float) $returnRequest->approved_amount,
                                            0,
                                            ',',
                                            '.'
                                        ) }}₫
                                    </p>
                                @endif

                            </td>

                            {{-- Trạng thái --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                <span class="admin-badge {{ $statusClass }}">
                                    {{ $statuses[$returnRequest->status]
                                        ?? $returnRequest->status }}
                                </span>

                                @if ($returnRequest->processor)
                                    <p class="mt-2 text-xs text-gray-500">
                                        Xử lý bởi:
                                        {{ $returnRequest->processor->name }}
                                    </p>
                                @endif

                            </td>

                            {{-- Ngày tạo --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                <p class="text-sm font-medium text-gray-700">
                                    {{ $returnRequest->created_at?->format('d/m/Y') }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $returnRequest->created_at?->format('H:i') }}
                                </p>

                            </td>

                            {{-- Thao tác --}}
                            <td class="whitespace-nowrap px-5 py-4 text-right">

                                <a
    href="{{ route(
        'admin.return-requests.show',
        $returnRequest
    ) }}"
    class="admin-btn admin-btn-primary"
>
    Xem chi tiết
</a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="9"
                                class="px-5 py-12 text-center"
                            >

                                <svg
                                    class="mx-auto h-12 w-12 text-gray-300"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M7.5 3.75h9A2.25 2.25 0 0118.75 6v12A2.25 2.25 0 0116.5 20.25h-9A2.25 2.25 0 015.25 18V6A2.25 2.25 0 017.5 3.75z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 8.25h6M9 12h6M9 15.75h3"
                                    />
                                </svg>

                                <p class="mt-3 font-medium text-gray-700">
                                    Chưa có yêu cầu trả hàng
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    Các yêu cầu trả hàng của khách sẽ hiển thị tại đây.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if ($returnRequests->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $returnRequests->links() }}
            </div>
        @endif

    </div>

@endsection
