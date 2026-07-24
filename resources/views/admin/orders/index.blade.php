@extends('admin.layouts.master')

@section('title', 'Quản lý đơn hàng')

@section('page-title', 'Quản lý đơn hàng')

@section('page-description', 'Theo dõi, tìm kiếm và xử lý toàn bộ đơn hàng trong hệ thống.')

@section('content')

    {{-- =========================
        Thống kê trạng thái đơn
    ========================== --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-9">

        {{-- Tất cả --}}
        <a
            href="{{ route('admin.orders.index') }}"
            class="admin-card p-4 transition hover:border-pink-300 hover:shadow-sm
                {{ !request('order_status') ? 'border-pink-300 bg-pink-50' : '' }}"
        >
            <p class="text-xs font-medium text-gray-500">
                Tất cả
            </p>

            <p class="mt-2 text-2xl font-bold text-gray-900">
                {{ number_format($statistics['all']) }}
            </p>
        </a>

        {{-- Chờ xác nhận --}}
        <a
            href="{{ route('admin.orders.index', ['order_status' => 'pending']) }}"
            class="admin-card p-4 transition hover:border-amber-300 hover:shadow-sm
                {{ request('order_status') === 'pending' ? 'border-amber-300 bg-amber-50' : '' }}"
        >
            <p class="text-xs font-medium text-gray-500">
                Chờ xác nhận
            </p>

            <p class="mt-2 text-2xl font-bold text-amber-600">
                {{ number_format($statistics['pending']) }}
            </p>
        </a>

        {{-- Đã xác nhận --}}
        <a
            href="{{ route('admin.orders.index', ['order_status' => 'confirmed']) }}"
            class="admin-card p-4 transition hover:border-blue-300 hover:shadow-sm
                {{ request('order_status') === 'confirmed' ? 'border-blue-300 bg-blue-50' : '' }}"
        >
            <p class="text-xs font-medium text-gray-500">
                Đã xác nhận
            </p>

            <p class="mt-2 text-2xl font-bold text-blue-600">
                {{ number_format($statistics['confirmed']) }}
            </p>
        </a>

        {{-- Đang xử lý --}}
        <a
            href="{{ route('admin.orders.index', ['order_status' => 'processing']) }}"
            class="admin-card p-4 transition hover:border-indigo-300 hover:shadow-sm
                {{ request('order_status') === 'processing' ? 'border-indigo-300 bg-indigo-50' : '' }}"
        >
            <p class="text-xs font-medium text-gray-500">
                Đang xử lý
            </p>

            <p class="mt-2 text-2xl font-bold text-indigo-600">
                {{ number_format($statistics['processing']) }}
            </p>
        </a>

        {{-- Đã đóng gói --}}
        <a
            href="{{ route('admin.orders.index', ['order_status' => 'packed']) }}"
            class="admin-card p-4 transition hover:border-purple-300 hover:shadow-sm
                {{ request('order_status') === 'packed' ? 'border-purple-300 bg-purple-50' : '' }}"
        >
            <p class="text-xs font-medium text-gray-500">
                Đã đóng gói
            </p>

            <p class="mt-2 text-2xl font-bold text-purple-600">
                {{ number_format($statistics['packed']) }}
            </p>
        </a>

        {{-- Đang giao --}}
        <a
            href="{{ route('admin.orders.index', ['order_status' => 'shipping']) }}"
            class="admin-card p-4 transition hover:border-cyan-300 hover:shadow-sm
                {{ request('order_status') === 'shipping' ? 'border-cyan-300 bg-cyan-50' : '' }}"
        >
            <p class="text-xs font-medium text-gray-500">
                Đang giao
            </p>

            <p class="mt-2 text-2xl font-bold text-cyan-600">
                {{ number_format($statistics['shipping']) }}
            </p>
        </a>

        {{-- Hoàn thành --}}
        <a
            href="{{ route('admin.orders.index', ['order_status' => 'completed']) }}"
            class="admin-card p-4 transition hover:border-green-300 hover:shadow-sm
                {{ request('order_status') === 'completed' ? 'border-green-300 bg-green-50' : '' }}"
        >
            <p class="text-xs font-medium text-gray-500">
                Hoàn thành
            </p>

            <p class="mt-2 text-2xl font-bold text-green-600">
                {{ number_format($statistics['completed']) }}
            </p>
        </a>

        {{-- Đã hủy --}}
        <a
            href="{{ route('admin.orders.index', ['order_status' => 'cancelled']) }}"
            class="admin-card p-4 transition hover:border-red-300 hover:shadow-sm
                {{ request('order_status') === 'cancelled' ? 'border-red-300 bg-red-50' : '' }}"
        >
            <p class="text-xs font-medium text-gray-500">
                Đã hủy
            </p>

            <p class="mt-2 text-2xl font-bold text-red-600">
                {{ number_format($statistics['cancelled']) }}
            </p>
        </a>

        {{-- Trả hàng --}}
        <a
            href="{{ route('admin.orders.index', ['order_status' => 'returned']) }}"
            class="admin-card p-4 transition hover:border-gray-400 hover:shadow-sm
                {{ request('order_status') === 'returned' ? 'border-gray-400 bg-gray-100' : '' }}"
        >
            <p class="text-xs font-medium text-gray-500">
                Trả hàng
            </p>

            <p class="mt-2 text-2xl font-bold text-gray-700">
                {{ number_format($statistics['returned']) }}
            </p>
        </a>

    </div>

    {{-- =========================
        Bộ lọc
    ========================== --}}
    <div class="admin-card mt-6 p-5">

        <div class="mb-5">
            <h2 class="text-base font-semibold text-gray-900">
                Tìm kiếm và lọc đơn hàng
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Tìm theo mã đơn, tên khách hàng, email hoặc số điện thoại.
            </p>
        </div>

        <form
            action="{{ route('admin.orders.index') }}"
            method="GET"
        >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

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
                        placeholder="Mã đơn, tên, email hoặc số điện thoại"
                    >

                </div>

                {{-- Trạng thái đơn --}}
                <div>

                    <label
                        for="order_status"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Trạng thái đơn
                    </label>

                    <select
                        id="order_status"
                        name="order_status"
                        class="admin-select"
                    >
                        <option value="">
                            Tất cả trạng thái
                        </option>

                        @foreach ($orderStatuses as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(request('order_status') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                </div>

                {{-- Thanh toán --}}
                <div>

                    <label
                        for="payment_status"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Trạng thái thanh toán
                    </label>

                    <select
                        id="payment_status"
                        name="payment_status"
                        class="admin-select"
                    >
                        <option value="">
                            Tất cả trạng thái
                        </option>

                        @foreach ($paymentStatuses as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(request('payment_status') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                </div>

                {{-- Vận chuyển --}}
                <div>

                    <label
                        for="shipping_status"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Trạng thái vận chuyển
                    </label>

                    <select
                        id="shipping_status"
                        name="shipping_status"
                        class="admin-select"
                    >
                        <option value="">
                            Tất cả trạng thái
                        </option>

                        @foreach ($shippingStatuses as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(request('shipping_status') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                </div>

                {{-- Phương thức thanh toán --}}
                <div>

                    <label
                        for="payment_method"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Phương thức thanh toán
                    </label>

                    <select
                        id="payment_method"
                        name="payment_method"
                        class="admin-select"
                    >
                        <option value="">
                            Tất cả phương thức
                        </option>

                        @foreach ($paymentMethods as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(request('payment_method') === $value)
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

            </div>

            <div class="mt-5 flex flex-col gap-3 sm:flex-row">

                <button
                    type="submit"
                    class="admin-btn admin-btn-primary"
                    data-loading-text="Đang lọc..."
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
                            d="M10.5 6h9m-9 6h6m-6 6h3M3.75 5.25h3v3h-3v-3zm0 6h3v3h-3v-3zm0 6h3v3h-3v-3z"
                        />
                    </svg>

                    Áp dụng bộ lọc
                </button>

                <a
                    href="{{ route('admin.orders.index') }}"
                    class="admin-btn admin-btn-secondary"
                >
                    Xóa bộ lọc
                </a>

            </div>
        </form>

    </div>

    {{-- =========================
        Danh sách đơn hàng
    ========================== --}}
    <div class="admin-card mt-6 overflow-hidden">

        <div
            class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-base font-semibold text-gray-900">
                    Danh sách đơn hàng
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Tìm thấy {{ number_format($orders->total()) }} đơn hàng.
                </p>
            </div>

            <div class="text-sm text-gray-500">
                Trang {{ $orders->currentPage() }}/{{ $orders->lastPage() }}
            </div>
        </div>

        <div class="overflow-x-auto">

            <table class="admin-table">

                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Kho xử lý</th>
                        <th>Trạng thái đơn</th>
                        <th>Thanh toán</th>
                        <th>Vận chuyển</th>
                        <th>Tổng tiền</th>
                        <th>Ngày đặt</th>
                        <th class="text-right">Thao tác</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($orders as $order)

                        <tr>

                            {{-- Mã đơn --}}
                            <td>
                                <div>
                                    <p class="font-semibold text-gray-900">
                                        {{ $order->order_code }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        ID: {{ $order->id }}
                                    </p>
                                </div>
                            </td>

                            {{-- Khách hàng --}}
                            <td>
                                <div class="max-w-52">

                                    <p class="truncate font-medium text-gray-900">
                                        {{ $order->user?->name ?? $order->customer_name }}
                                    </p>

                                    <p class="mt-1 truncate text-xs text-gray-500">
                                        {{ $order->user?->email ?? $order->customer_email }}
                                    </p>

                                    @if ($order->customer_phone)
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $order->customer_phone }}
                                        </p>
                                    @endif

                                </div>
                            </td>

                            {{-- Kho --}}
                            <td>
                                @if ($order->warehouse)
                                    <span class="text-sm text-gray-700">
                                        {{ $order->warehouse->name }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">
                                        Chưa xác định
                                    </span>
                                @endif
                            </td>

                            {{-- Trạng thái đơn --}}
                            <td>
                                @switch($order->order_status)
                                    @case('pending')
                                        <span class="admin-badge admin-badge-warning">
                                            Chờ xác nhận
                                        </span>
                                        @break

                                    @case('confirmed')
                                        <span class="admin-badge admin-badge-info">
                                            Đã xác nhận
                                        </span>
                                        @break

                                    @case('processing')
                                        <span class="admin-badge admin-badge-info">
                                            Đang xử lý
                                        </span>
                                        @break

                                    @case('packed')
                                        <span class="admin-badge bg-purple-100 text-purple-700">
                                            Đã đóng gói
                                        </span>
                                        @break

                                    @case('shipping')
                                        <span class="admin-badge bg-cyan-100 text-cyan-700">
                                            Đang giao
                                        </span>
                                        @break

                                    @case('completed')
                                        <span class="admin-badge admin-badge-success">
                                            Hoàn thành
                                        </span>
                                        @break

                                    @case('cancelled')
                                        <span class="admin-badge admin-badge-danger">
                                            Đã hủy
                                        </span>
                                        @break

                                    @case('returned')
                                        <span class="admin-badge admin-badge-neutral">
                                            Đã trả hàng
                                        </span>
                                        @break

                                    @default
                                        <span class="admin-badge admin-badge-neutral">
                                            {{ $order->order_status }}
                                        </span>
                                @endswitch
                            </td>

                            {{-- Thanh toán --}}
                            <td>
                                @switch($order->payment_status)
                                    @case('paid')
                                        <span class="admin-badge admin-badge-success">
                                            Đã thanh toán
                                        </span>
                                        @break

                                    @case('pending')
                                        <span class="admin-badge admin-badge-warning">
                                            Chờ thanh toán
                                        </span>
                                        @break

                                    @case('unpaid')
                                        <span class="admin-badge admin-badge-warning">
                                            Chưa thanh toán
                                        </span>
                                        @break

                                    @case('failed')
                                        <span class="admin-badge admin-badge-danger">
                                            Thất bại
                                        </span>
                                        @break

                                    @case('cancelled')
                                        <span class="admin-badge admin-badge-danger">
                                            Đã hủy
                                        </span>
                                        @break

                                    @case('refunded')
                                        <span class="admin-badge admin-badge-info">
                                            Đã hoàn tiền
                                        </span>
                                        @break

                                    @case('partially_refunded')
                                        <span class="admin-badge admin-badge-info">
                                            Hoàn một phần
                                        </span>
                                        @break

                                    @default
                                        <span class="admin-badge admin-badge-neutral">
                                            {{ $order->payment_status }}
                                        </span>
                                @endswitch
                            </td>

                           {{-- Vận chuyển --}}
<td>
    @php
        $shipment = $order->latestShipment;

        $carrierName = $shipment?->carrier_name
            ?: $shipment?->shippingMethod?->name;
    @endphp

    <div class="space-y-1">
        @if ($carrierName)
            <p class="text-sm font-semibold text-gray-900">
                {{ $carrierName }}
            </p>
        @endif

        @switch($order->shipping_status)
            @case('pending')
                <span class="admin-badge admin-badge-warning">
                    Chờ xử lý
                </span>
                @break

            @case('ready_to_ship')
                <span class="admin-badge admin-badge-info">
                    Sẵn sàng giao
                </span>
                @break

            @case('picked_up')
                <span class="admin-badge admin-badge-info">
                    Đã lấy hàng
                </span>
                @break

            @case('in_transit')
                <span class="admin-badge bg-cyan-100 text-cyan-700">
                    Đang vận chuyển
                </span>
                @break

            @case('out_for_delivery')
                <span class="admin-badge bg-cyan-100 text-cyan-700">
                    Đang giao hàng
                </span>
                @break

            @case('delivered')
                <span class="admin-badge admin-badge-success">
                    Đã giao hàng
                </span>
                @break

            @case('failed')
            @case('delivery_failed')
                <span class="admin-badge admin-badge-danger">
                    Giao hàng thất bại
                </span>
                @break

            @case('returned')
                <span class="admin-badge admin-badge-neutral">
                    Đã hoàn hàng
                </span>
                @break

            @case('cancelled')
                <span class="admin-badge admin-badge-danger">
                    Đã hủy
                </span>
                @break

            @default
                <span class="admin-badge admin-badge-neutral">
                    {{ $order->shipping_status ?: 'Chưa tạo kiện' }}
                </span>
        @endswitch

        @if ($shipment?->tracking_code)
            <p class="text-xs text-gray-500">
                Mã: {{ $shipment->tracking_code }}
            </p>
        @endif
    </div>
</td>

                            {{-- Tổng tiền --}}
                            <td>
                                <span class="font-semibold text-gray-900">
                                    {{ number_format((float) $order->total_amount, 0, ',', '.') }}₫
                                </span>
                            </td>

                            {{-- Ngày đặt --}}
                            <td>
                                <p class="text-sm text-gray-700">
                                    {{ $order->created_at?->format('d/m/Y') }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $order->created_at?->format('H:i') }}
                                </p>
                            </td>

                            {{-- Thao tác --}}
                            <td>
                                <div class="flex items-center justify-end gap-2">

                                    <button
                                        type="button"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-600 transition hover:border-pink-300 hover:bg-pink-50 hover:text-pink-600"
                                        data-copy-text="{{ $order->order_code }}"
                                        title="Sao chép mã đơn"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.8"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M15.75 17.25v2.625A1.125 1.125 0 0114.625 21H4.125A1.125 1.125 0 013 19.875V9.375A1.125 1.125 0 014.125 8.25H6.75m9-5.25h4.125A1.125 1.125 0 0121 4.125v10.5a1.125 1.125 0 01-1.125 1.125h-10.5a1.125 1.125 0 01-1.125-1.125v-10.5A1.125 1.125 0 019.375 3h6.375z"
                                            />
                                        </svg>
                                    </button>

                                    {{-- Tạm thời chưa có route show --}}
                                    <a
                                        href="{{ route('admin.orders.show', $order) }}"
                                        class="inline-flex h-9 items-center justify-center rounded-lg bg-pink-600 px-3 text-xs font-semibold text-white transition hover:bg-pink-700"
                                        >
                                        Xem
                                    </a>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="9"
                                class="py-16 text-center"
                            >
                                <div class="flex flex-col items-center">

                                    <svg
                                        class="h-14 w-14 text-gray-300"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M3.75 4.5h16.5v15H3.75v-15zM7.5 8.25h9M7.5 12h9M7.5 15.75h5.25"
                                        />
                                    </svg>

                                    <p class="mt-4 font-semibold text-gray-700">
                                        Không tìm thấy đơn hàng
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        Hãy thay đổi từ khóa hoặc bộ lọc tìm kiếm.
                                    </p>

                                    @if (request()->hasAny([
                                        'keyword',
                                        'order_status',
                                        'payment_status',
                                        'shipping_status',
                                        'payment_method',
                                        'date_from',
                                        'date_to',
                                    ]))
                                        <a
                                            href="{{ route('admin.orders.index') }}"
                                            class="admin-btn admin-btn-secondary mt-5"
                                        >
                                            Xóa bộ lọc
                                        </a>
                                    @endif

                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Phân trang --}}
        @if ($orders->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $orders->links() }}
            </div>
        @endif

    </div>

@endsection
