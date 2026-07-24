@extends('admin.layouts.master')

@section('title', 'Chi tiết đơn hàng ' . $order->order_code)

@section('page-title', 'Chi tiết đơn hàng')

@section('page-description', 'Theo dõi toàn bộ thông tin và lịch sử xử lý của đơn hàng.')

@section('content')

    {{-- =========================
        Thanh điều hướng đầu trang
    ========================== --}}
    <div
        class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="flex items-center gap-3">

            <a
                href="{{ route('admin.orders.index') }}"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 transition hover:bg-gray-50 hover:text-pink-600"
                title="Quay lại danh sách"
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
                        d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"
                    />
                </svg>
            </a>

            <div>
                <div class="flex flex-wrap items-center gap-2">

                    <h2 class="text-xl font-bold text-gray-900">
                        {{ $order->order_code }}
                    </h2>

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

                </div>

                <p class="mt-1 text-sm text-gray-500">
                    Đặt lúc {{ $order->created_at?->format('H:i d/m/Y') }}
                </p>
            </div>

        </div>

        <div class="flex flex-wrap gap-3">

            <button
                type="button"
                class="admin-btn admin-btn-secondary"
                data-copy-text="{{ $order->order_code }}"
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
                        d="M15.75 17.25v2.625A1.125 1.125 0 0114.625 21H4.125A1.125 1.125 0 013 19.875V9.375A1.125 1.125 0 014.125 8.25H6.75m9-5.25h4.125A1.125 1.125 0 0121 4.125v10.5a1.125 1.125 0 01-1.125 1.125h-10.5a1.125 1.125 0 01-1.125-1.125v-10.5A1.125 1.125 0 019.375 3h6.375z"
                    />
                </svg>

                Sao chép mã đơn
            </button>

        </div>
    </div>

    {{-- =========================
        Trạng thái tổng quan
    ========================== --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            {{-- =========================
    Cập nhật trạng thái đơn hàng
========================== --}}
<div class="admin-card mt-6 p-5">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h3 class="text-base font-semibold text-gray-900">
                Cập nhật trạng thái đơn hàng
            </h3>

            <p class="mt-1 text-sm text-gray-500">
                Chuyển đơn hàng sang bước xử lý tiếp theo.
            </p>
        </div>

        <span class="admin-badge admin-badge-info">
            Hiện tại:
            {{ $orderStatuses[$order->order_status] ?? $order->order_status }}
        </span>

    </div>



    @if (! empty($nextOrderStatuses))

        <form
            action="{{ route('admin.orders.update-status', $order) }}"
            method="POST"
            class="mt-5"
            data-confirm="Bạn có chắc muốn cập nhật trạng thái đơn hàng này?"
        >
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

                {{-- Trạng thái tiếp theo --}}
                <div>

                    <label
                        for="order_status"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Trạng thái tiếp theo
                    </label>

                    <select
                        id="order_status"
                        name="order_status"
                        class="admin-select"
                        required
                    >
                        <option value="" disabled>
    Chọn trạng thái
</option>

                        @foreach ($nextOrderStatuses as $status => $label)
                            <option
    value="{{ $status }}"
    @selected(
        old('order_status', array_key_first($nextOrderStatuses)) === $status
    )
>
    {{ $label }}
</option>
                        @endforeach
                    </select>

                    @error('order_status')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- Ghi chú xử lý --}}
                <div class="lg:col-span-2">

                    <label
                        for="note"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Ghi chú xử lý
                    </label>

                    <textarea
                        id="note"
                        name="note"
                        rows="3"
                        class="admin-textarea"
                        placeholder="Ví dụ: Đã kiểm tra tồn kho và xác nhận đơn hàng..."
                    >{{ old('note') }}</textarea>

                    @error('note')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </div>

            <div class="mt-5">

                <button
                    type="submit"
                    class="admin-btn admin-btn-primary"
                    data-loading-text="Đang cập nhật..."
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
                            d="M4.5 12.75l4.5 4.5 10.5-10.5"
                        />
                    </svg>

                    Cập nhật trạng thái
                </button>

            </div>

        </form>

    @else

        <div
            class="mt-5 rounded-xl border border-gray-200 bg-gray-50 px-4 py-4 text-sm text-gray-600"
        >
            @switch($order->order_status)

                @case('completed')
                    Đơn hàng đã hoàn thành, không còn bước xử lý tiếp theo.
                    @break

                @case('cancelled')
                    Đơn hàng đã bị hủy.
                    @break

                @case('returned')
                    Đơn hàng đã được trả hàng.
                    @break

                @default
                    Không có trạng thái tiếp theo phù hợp.
            @endswitch
        </div>

    @endif

</div>
        {{-- Trạng thái đơn --}}
<div class="admin-card p-5">

    <div class="flex items-start justify-between gap-4">

        <div>
            <p class="text-sm font-medium text-gray-500">
                Trạng thái đơn hàng
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-3">

                @php
                    $orderStatusIconClass = match ($order->order_status) {
                        'pending' => 'bg-amber-100 text-amber-600',
                        'confirmed' => 'bg-blue-100 text-blue-600',
                        'processing' => 'bg-indigo-100 text-indigo-600',
                        'packed' => 'bg-purple-100 text-purple-600',
                        'shipping' => 'bg-cyan-100 text-cyan-600',
                        'completed' => 'bg-green-100 text-green-600',
                        'cancelled' => 'bg-red-100 text-red-600',
                        'returned' => 'bg-gray-200 text-gray-600',
                        default => 'bg-gray-100 text-gray-600',
                    };
                @endphp

                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $orderStatusIconClass }}"
                >
                    @switch($order->order_status)

                        @case('pending')
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
                                    d="M12 6v6l4 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            @break

                        @case('confirmed')
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
                                    d="M4.5 12.75l4.5 4.5 10.5-10.5"
                                />
                            </svg>
                            @break

                        @case('processing')
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
                                    d="M4.5 6.75h15M4.5 12h15M4.5 17.25h9"
                                />
                            </svg>
                            @break

                        @case('packed')
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
                                    d="M21 8.25l-9-5.25-9 5.25m18 0l-9 5.25m9-5.25v7.5L12 21m0-7.5L3 8.25m9 5.25V21M3 8.25v7.5L12 21"
                                />
                            </svg>
                            @break

                        @case('shipping')
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
                                    d="M3 6h11.25v9H3V6zm11.25 3H18l3 3v3h-6.75V9zM6.75 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm10.5 0a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"
                                />
                            </svg>
                            @break

                        @case('completed')
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
                                    d="M9 12.75l2.25 2.25L15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            @break

                        @case('cancelled')
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
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                            @break

                        @default
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
                                    d="M12 9v3.75M12 16.5h.008M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>

                    @endswitch
                </div>

                <div>
                    <p class="text-lg font-bold text-gray-900">
                        {{ $orderStatuses[$order->order_status] ?? $order->order_status }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Cập nhật gần nhất:
                        {{ $order->updated_at?->format('H:i d/m/Y') }}
                    </p>
                </div>

            </div>
        </div>

    </div>

    <div class="mt-5 space-y-3 border-t border-gray-200 pt-4 text-sm">

        <div class="flex items-start justify-between gap-4">
            <span class="text-gray-500">
                Ngày tạo đơn
            </span>

            <span class="text-right font-medium text-gray-900">
                {{ $order->created_at?->format('H:i d/m/Y') }}
            </span>
        </div>

        <div class="flex items-start justify-between gap-4">
            <span class="text-gray-500">
                Ngày xác nhận
            </span>

            <span class="text-right font-medium text-gray-900">
                {{ $order->confirmed_at?->format('H:i d/m/Y') ?? 'Chưa xác nhận' }}
            </span>
        </div>

        @if ($order->processing_at)
            <div class="flex items-start justify-between gap-4">
                <span class="text-gray-500">
                    Bắt đầu xử lý
                </span>

                <span class="text-right font-medium text-gray-900">
                    {{ $order->processing_at->format('H:i d/m/Y') }}
                </span>
            </div>
        @endif

        @if ($order->packed_at)
            <div class="flex items-start justify-between gap-4">
                <span class="text-gray-500">
                    Đóng gói lúc
                </span>

                <span class="text-right font-medium text-gray-900">
                    {{ $order->packed_at->format('H:i d/m/Y') }}
                </span>
            </div>
        @endif

        @if ($order->shipping_at)
            <div class="flex items-start justify-between gap-4">
                <span class="text-gray-500">
                    Giao hàng lúc
                </span>

                <span class="text-right font-medium text-gray-900">
                    {{ $order->shipping_at->format('H:i d/m/Y') }}
                </span>
            </div>
        @endif

        @if ($order->completed_at)
            <div class="flex items-start justify-between gap-4">
                <span class="text-gray-500">
                    Hoàn thành lúc
                </span>

                <span class="text-right font-medium text-gray-900">
                    {{ $order->completed_at->format('H:i d/m/Y') }}
                </span>
            </div>
        @endif

        <div class="flex items-start justify-between gap-4">
            <span class="text-gray-500">
                Người xác nhận
            </span>

            <span class="text-right font-medium text-gray-900">
                {{ $order->confirmer?->name ?? 'Chưa có' }}
            </span>
        </div>

    </div>

</div>

        {{-- Trạng thái thanh toán --}}
<div class="admin-card p-5">

    @php
        $latestPayment = $order->payments->first();

        $paymentIconClass = match ($order->payment_status) {
            'unpaid' => 'bg-red-100 text-red-600',
            'pending' => 'bg-amber-100 text-amber-600',
            'paid' => 'bg-green-100 text-green-600',
            'failed' => 'bg-red-100 text-red-600',
            'cancelled' => 'bg-gray-200 text-gray-600',
            'refunded' => 'bg-blue-100 text-blue-600',
            'partially_refunded' => 'bg-cyan-100 text-cyan-600',
            default => 'bg-gray-100 text-gray-600',
        };
    @endphp

    <div class="flex items-start gap-3">

        <div
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $paymentIconClass }}"
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
                    d="M2.25 6.75h19.5v10.5H2.25V6.75zM2.25 10.5h19.5M6 14.25h3"
                />
            </svg>
        </div>

        <div>
            <p class="text-sm font-medium text-gray-500">
                Trạng thái thanh toán
            </p>

            <p class="mt-2 text-lg font-bold text-gray-900">
                {{ $paymentStatuses[$order->payment_status]
                    ?? $order->payment_status }}
            </p>

            <p class="mt-1 text-xs text-gray-500">
                {{ $paymentMethods[$order->payment_method]
                    ?? $order->payment_method }}
            </p>
        </div>

    </div>

    <div class="mt-5 space-y-3 border-t border-gray-200 pt-4 text-sm">

        <div class="flex justify-between gap-4">
            <span class="text-gray-500">
                Hình thức
            </span>

            <span class="font-medium text-gray-900">
                {{ $paymentMethods[$order->payment_method]
                    ?? $order->payment_method }}
            </span>
        </div>

        <div class="flex justify-between gap-4">
            <span class="text-gray-500">
                Số tiền
            </span>

            <span class="font-semibold text-green-600">
                {{ number_format((float) $order->total_amount,0,',','.') }}₫
            </span>
        </div>

        <div class="flex justify-between gap-4">
            <span class="text-gray-500">
                Mã thanh toán
            </span>

            <span class="font-medium text-gray-900">
                {{ $latestPayment?->payment_code ?? 'Chưa có' }}
            </span>
        </div>

        <div class="flex justify-between gap-4">
            <span class="text-gray-500">
                Mã giao dịch
            </span>

            <span class="font-medium text-gray-900">
                {{ $latestPayment?->provider_transaction_id ?? 'Chưa có' }}
            </span>
        </div>

        <div class="flex justify-between gap-4">
            <span class="text-gray-500">
                Thanh toán lúc
            </span>

            <span class="font-medium text-gray-900">
                {{ $latestPayment?->paid_at?->format('H:i d/m/Y')
                    ?? 'Chưa thanh toán' }}
            </span>
        </div>

    </div>

    @if($order->payment_status === 'paid')

        <div
            class="mt-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3"
        >
            <div class="flex items-center gap-2">

                <svg
                    class="h-5 w-5 text-green-600"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.8"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 12.75l2.25 2.25L15 9.75"
                    />
                </svg>

                <span class="font-medium text-green-700">
                    Đơn hàng đã thanh toán thành công.
                </span>

            </div>
        </div>

    @endif

</div>

        {{-- Trạng thái vận chuyển --}}
<div class="admin-card p-5">

    @php
        $latestShipment = $order->shipments->first();

        $shippingIconClass = match ($order->shipping_status) {
            'pending' => 'bg-amber-100 text-amber-600',
            'ready_to_ship' => 'bg-purple-100 text-purple-600',
            'picked_up' => 'bg-blue-100 text-blue-600',
            'in_transit' => 'bg-cyan-100 text-cyan-600',
            'delivered' => 'bg-green-100 text-green-600',
            'failed' => 'bg-red-100 text-red-600',
            'returned' => 'bg-gray-200 text-gray-600',
            'cancelled' => 'bg-red-100 text-red-600',
            default => 'bg-gray-100 text-gray-600',
        };
    @endphp

    <div class="flex items-start justify-between gap-4">

        <div>
            <p class="text-sm font-medium text-gray-500">
                Trạng thái vận chuyển
            </p>

            <div class="mt-3 flex items-center gap-3">

                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $shippingIconClass }}"
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
                            d="M3 6h11.25v9H3V6zm11.25 3H18l3 3v3h-6.75V9zM6.75 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm10.5 0a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"
                        />
                    </svg>
                </div>

                <div>
                    <p class="text-lg font-bold text-gray-900">
                        {{ $shippingStatuses[$order->shipping_status]
                            ?? $order->shipping_status }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Cập nhật theo quá trình xử lý đơn hàng
                    </p>
                </div>

            </div>
        </div>

    </div>

    <div class="mt-5 space-y-3 border-t border-gray-200 pt-4 text-sm">

        {{-- Kho xử lý --}}
        <div class="flex items-start justify-between gap-4">
            <span class="text-gray-500">
                Kho xử lý
            </span>

            <span class="text-right font-medium text-gray-900">
                {{ $order->warehouse?->name ?? 'Chưa gán kho' }}
            </span>
        </div>

        {{-- Đơn vị vận chuyển --}}
        <div class="flex items-start justify-between gap-4">
            <span class="text-gray-500">
                Đơn vị vận chuyển
            </span>

            <span class="text-right font-medium text-gray-900">
                {{ $latestShipment?->shippingMethod?->name
                    ?? $latestShipment?->carrier_name
                    ?? 'Chưa tạo kiện hàng' }}
            </span>
        </div>

        {{-- Mã kiện hàng --}}
        <div class="flex items-start justify-between gap-4">
            <span class="text-gray-500">
                Mã kiện hàng
            </span>

            <span class="text-right font-medium text-gray-900">
                {{ $latestShipment?->shipment_code ?? 'Chưa có' }}
            </span>
        </div>

        {{-- Mã vận đơn --}}
        <div class="flex items-start justify-between gap-4">
            <span class="text-gray-500">
                Mã vận đơn
            </span>

            <div class="flex items-center justify-end gap-2">

                <span class="text-right font-medium text-gray-900">
                    {{ $latestShipment?->tracking_code ?? 'Chưa có' }}
                </span>

                @if ($latestShipment?->tracking_code)
                    <button
                        type="button"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-md text-gray-500 transition hover:bg-gray-100 hover:text-pink-600"
                        data-copy-text="{{ $latestShipment->tracking_code }}"
                        title="Sao chép mã vận đơn"
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
                @endif

            </div>
        </div>

        {{-- Phí giao hàng --}}
        <div class="flex items-start justify-between gap-4">
            <span class="text-gray-500">
                Phí giao hàng
            </span>

            <span class="text-right font-medium text-gray-900">
                {{ number_format(
                    (float) ($latestShipment?->shipping_fee ?? $order->shipping_fee),
                    0,
                    ',',
                    '.'
                ) }}₫
            </span>
        </div>

        {{-- Ngày giao dự kiến --}}
        <div class="flex items-start justify-between gap-4">
            <span class="text-gray-500">
                Ngày giao dự kiến
            </span>

            <span class="text-right font-medium text-gray-900">
                {{ $latestShipment?->estimated_delivery_at?->format('d/m/Y')
                    ?? 'Chưa cập nhật' }}
            </span>
        </div>

    </div>

    @if (! $latestShipment)

    <div
        class="mt-5 rounded-lg border border-dashed border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-700"
    >
        Đơn hàng chưa được tạo kiện vận chuyển.
    </div>

    @if ($order->order_status === 'packed')
        <a
            href="{{ route('admin.shipments.create', $order) }}"
            class="admin-btn admin-btn-primary mt-4 w-full"
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
                    d="M3 6h11.25v9H3V6zm11.25 3H18l3 3v3h-6.75V9z"
                />
            </svg>

            Tạo kiện vận chuyển
        </a>
    @endif

@else

    <div
        class="mt-5 rounded-xl border border-green-200 bg-green-50 p-4"
    >
        <div class="flex items-center gap-3">

            <div
                class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-green-600"
            >
                ✓
            </div>

            <div>
                <p class="font-semibold text-green-700">
                    Đã tạo kiện vận chuyển
                </p>

                <p class="mt-1 text-sm text-green-600">
                    Mã kiện: {{ $latestShipment->shipment_code }}
                </p>
            </div>

        </div>
    </div>

@endif

</div>

    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- =========================
            Cột trái
        ========================== --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- Sản phẩm trong đơn --}}
            <div class="admin-card overflow-hidden">

                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Sản phẩm trong đơn
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Tổng cộng {{ number_format($order->total_quantity) }} sản phẩm.
                    </p>
                </div>

                <div class="divide-y divide-gray-200">

                    @forelse ($order->items as $item)

                        <div class="flex flex-col gap-4 p-5 sm:flex-row">

                            {{-- Ảnh sản phẩm --}}
                            <div
                                class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-gray-50"
                            >
                                @if ($item->image_path)
                                    <img
                                        src="{{ asset('storage/' . $item->image_path) }}"
                                        alt="{{ $item->product_name }}"
                                        class="h-full w-full object-cover"
                                    >
                                @else
                                    <div
                                        class="flex h-full w-full items-center justify-center text-gray-300"
                                    >
                                        <svg
                                            class="h-8 w-8"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M3 16.5l5.25-5.25 4.5 4.5 2.25-2.25L21 19.5M3 5.25h18v13.5H3V5.25z"
                                            />
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Thông tin --}}
                            <div class="min-w-0 flex-1">

                                <p class="font-semibold text-gray-900">
                                    {{ $item->product_name }}
                                </p>

                                @if ($item->variant_name)
                                    <p class="mt-1 text-sm text-gray-500">
                                        Phân loại: {{ $item->variant_name }}
                                    </p>
                                @endif

                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">

                                    @if ($item->sku_code)
                                        <span>
                                            SKU: {{ $item->sku_code }}
                                        </span>
                                    @endif

                                    @if ($item->barcode)
                                        <span>
                                            Barcode: {{ $item->barcode }}
                                        </span>
                                    @endif

                                    <span>
                                        Số lượng: {{ number_format($item->quantity) }}
                                    </span>

                                </div>

                            </div>

                            {{-- Giá --}}
                            <div class="shrink-0 text-left sm:text-right">

                                <p class="text-sm text-gray-500">
                                    {{ number_format((float) $item->unit_price, 0, ',', '.') }}₫
                                    × {{ number_format($item->quantity) }}
                                </p>

                                <p class="mt-2 font-bold text-gray-900">
                                    {{ number_format((float) $item->total_price, 0, ',', '.') }}₫
                                </p>

                                @if ((float) $item->discount_amount > 0)
                                    <p class="mt-1 text-xs text-green-600">
                                        Giảm
                                        {{ number_format((float) $item->discount_amount, 0, ',', '.') }}₫
                                    </p>
                                @endif

                            </div>

                        </div>

                    @empty

                        <div class="p-10 text-center text-sm text-gray-500">
                            Đơn hàng chưa có sản phẩm.
                        </div>

                    @endforelse

                </div>
            </div>

            {{-- Thanh toán --}}
            <div class="admin-card overflow-hidden">

                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Thanh toán
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Các bản ghi thanh toán liên quan đến đơn hàng.
                    </p>
                </div>

                <div class="divide-y divide-gray-200">

                    @forelse ($order->payments as $payment)

                        <div class="p-5">

                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <div>
                                    <p class="font-semibold text-gray-900">
                                        {{ $payment->payment_code }}
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $paymentMethods[$payment->method] ?? $payment->method }}
                                    </p>
                                </div>

                                <div class="text-left sm:text-right">

                                    @switch($payment->status)
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
                                                Hoàn tiền một phần
                                            </span>
                                            @break

                                        @default
                                            <span class="admin-badge admin-badge-neutral">
                                                {{ $payment->status }}
                                            </span>
                                    @endswitch

                                    <p class="mt-2 font-bold text-gray-900">
                                        {{ number_format((float) $payment->amount, 0, ',', '.') }}
                                        {{ $payment->currency ?? 'VND' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-3 text-sm text-gray-600 sm:grid-cols-2">

                                <div>
                                    <span class="text-gray-500">
                                        Mã giao dịch:
                                    </span>

                                    <span class="font-medium text-gray-700">
                                        {{ $payment->provider_transaction_id ?: 'Chưa có' }}
                                    </span>
                                </div>

                                <div>
                                    <span class="text-gray-500">
                                        Thanh toán lúc:
                                    </span>

                                    <span class="font-medium text-gray-700">
                                        {{ $payment->paid_at?->format('H:i d/m/Y') ?? 'Chưa thanh toán' }}
                                    </span>
                                </div>

                                @if ($payment->failure_reason)
                                    <div class="sm:col-span-2">
                                        <span class="text-gray-500">
                                            Lý do thất bại:
                                        </span>

                                        <span class="font-medium text-red-600">
                                            {{ $payment->failure_reason }}
                                        </span>
                                    </div>
                                @endif

                            </div>

                        </div>

                    @empty

                        <div class="p-10 text-center text-sm text-gray-500">
                            Chưa có bản ghi thanh toán.
                        </div>

                    @endforelse

                </div>
            </div>

            {{-- Vận chuyển --}}
            <div class="admin-card overflow-hidden">

                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Vận chuyển
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Thông tin kiện hàng và mã vận đơn.
                    </p>
                </div>

                <div class="divide-y divide-gray-200">

                    @forelse ($order->shipments as $shipment)

                        <div class="p-5">

                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <div>
                                    <p class="font-semibold text-gray-900">
                                        {{ $shipment->shipment_code }}
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $shipment->shippingMethod?->name ?? $shipment->carrier_name ?? 'Chưa xác định đơn vị giao hàng' }}
                                    </p>
                                </div>

                                <div>
                                    <span class="admin-badge admin-badge-info">
                                        {{ $shippingStatuses[$shipment->status] ?? $shipment->status }}
                                    </span>
                                </div>
                            </div>

                            <div
                                class="mt-4 grid grid-cols-1 gap-3 text-sm text-gray-600 sm:grid-cols-2"
                            >
                                <div>
                                    <span class="text-gray-500">
                                        Mã vận đơn:
                                    </span>

                                    <span class="font-medium text-gray-700">
                                        {{ $shipment->tracking_code ?: 'Chưa có' }}
                                    </span>
                                </div>

                                <div>
                                    <span class="text-gray-500">
                                        Dịch vụ:
                                    </span>

                                    <span class="font-medium text-gray-700">
                                        {{ $shipment->service_name ?: 'Chưa cập nhật' }}
                                    </span>
                                </div>

                                <div>
                                    <span class="text-gray-500">
                                        Phí vận chuyển:
                                    </span>

                                    <span class="font-medium text-gray-700">
                                        {{ number_format((float) $shipment->shipping_fee, 0, ',', '.') }}₫
                                    </span>
                                </div>

                                <div>
                                    <span class="text-gray-500">
                                        Giao dự kiến:
                                    </span>

                                    <span class="font-medium text-gray-700">
                                        {{ $shipment->estimated_delivery_at?->format('d/m/Y') ?? 'Chưa cập nhật' }}
                                    </span>
                                </div>

                                @if ($shipment->tracking_code)
                                    <div class="sm:col-span-2">
                                        <button
                                            type="button"
                                            class="text-sm font-medium text-pink-600 hover:text-pink-700"
                                            data-copy-text="{{ $shipment->tracking_code }}"
                                        >
                                            Sao chép mã vận đơn
                                        </button>
                                    </div>
                                @endif

                            </div>

                        </div>

                    @empty

                        <div class="p-10 text-center text-sm text-gray-500">
                            Đơn hàng chưa tạo kiện vận chuyển.
                        </div>

                    @endforelse

                </div>
            </div>

            {{-- Lịch sử trạng thái --}}
<div class="admin-card overflow-hidden">

    <div class="border-b border-gray-200 px-5 py-4">
        <h3 class="text-base font-semibold text-gray-900">
            Lịch sử xử lý đơn hàng
        </h3>

        <p class="mt-1 text-sm text-gray-500">
            Theo dõi các thay đổi của đơn hàng, vận chuyển và thanh toán.
        </p>
    </div>

    <div class="p-5">

        @forelse ($order->statusHistories as $history)

            @php
                $historyLabel = match ($history->status_type) {
                    'order' => $orderStatuses[$history->to_status] ?? $history->to_status,
                    'shipping' => $shippingStatuses[$history->to_status] ?? $history->to_status,
                    'payment' => $paymentStatuses[$history->to_status] ?? $history->to_status,
                    default => $history->to_status,
                };

                $historyTypeLabel = match ($history->status_type) {
                    'order' => 'Đơn hàng',
                    'shipping' => 'Vận chuyển',
                    'payment' => 'Thanh toán',
                    default => ucfirst((string) $history->status_type),
                };

                $historyDotClass = match ($history->status_type) {

    'order' => 'bg-pink-500',

    'shipping' => 'bg-blue-500',

    'payment' => 'bg-green-500',

    default => 'bg-gray-400',

};
            $historyBadgeClass = match ($history->status_type) {
    'order' => 'bg-pink-100 text-pink-700',
    'shipping' => 'bg-blue-100 text-blue-700',
    'payment' => 'bg-green-100 text-green-700',
    default => 'bg-gray-100 text-gray-700',
};
            @endphp

            <div class="relative flex gap-4 pb-7 last:pb-0">

                @if (! $loop->last)
                    <div
                        class="absolute left-[7px] top-5 h-full w-px bg-gray-200"
                    ></div>
                @endif

                <div
                    class="relative mt-1.5 h-4 w-4 shrink-0 rounded-full border-4 border-white shadow {{ $historyDotClass }}"
                ></div>

                <div class="min-w-0 flex-1">

                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"
                    >

                        <div>
                            <div class="flex flex-wrap items-center gap-2">

                                <p class="font-semibold text-gray-900">
                                    {{ $historyLabel }}
                                </p>
                                <div class="mt-1 flex items-center gap-1.5 text-xs uppercase tracking-wider text-gray-400">

    @switch($history->status_type)

        @case('order')
            <svg
                class="h-4 w-4 text-pink-500"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3.75 4.5h16.5v15H3.75v-15zM7.5 8.25h9M7.5 12h9M7.5 15.75h5.25"
                />
            </svg>

            <span>Luồng xử lý đơn hàng</span>
            @break

        @case('shipping')
            <svg
                class="h-4 w-4 text-blue-500"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3 6h11.25v9H3V6zm11.25 3H18l3 3v3h-6.75V9zM6.75 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm10.5 0a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"
                />
            </svg>

            <span>Luồng vận chuyển</span>
            @break

        @case('payment')
            <svg
                class="h-4 w-4 text-green-500"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M2.25 6.75h19.5v10.5H2.25V6.75zM2.25 10.5h19.5M6 14.25h3"
                />
            </svg>

            <span>Luồng thanh toán</span>
            @break

    @endswitch

</div>

                                <span class="admin-badge {{ $historyBadgeClass }}">
    {{ $historyTypeLabel }}
</span>

                            </div>

                            @if ($history->from_status)
                                <p class="mt-2 text-sm text-gray-500">
                                    Chuyển từ:

                                    <span class="font-medium text-gray-700">
                                        {{ match ($history->status_type) {
                                            'order' => $orderStatuses[$history->from_status] ?? $history->from_status,
                                            'shipping' => $shippingStatuses[$history->from_status] ?? $history->from_status,
                                            'payment' => $paymentStatuses[$history->from_status] ?? $history->from_status,
                                            default => $history->from_status,
                                        } }}
                                    </span>
                                </p>
                            @endif
                        </div>

                        <div class="shrink-0 text-left sm:text-right">
                            <p class="text-sm font-medium text-gray-700">
                                {{ $history->created_at?->format('d/m/Y') }}
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                {{ $history->created_at?->format('H:i:s') }}
                            </p>
                        </div>

                    </div>

                    @if ($history->note)
                        <div
                            class="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3"
                        >
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Ghi chú
                            </p>

                            <p class="mt-1 whitespace-pre-line text-sm leading-6 text-gray-700">
                                {{ $history->note }}
                            </p>
                        </div>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center gap-4">

                    <div class="flex items-center gap-3">

    <div
    class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-pink-100 font-semibold text-pink-600"
>
    @if ($history->creator?->avatar)
        <img
            src="{{ asset('storage/' . $history->creator->avatar) }}"
            alt="{{ $history->creator->name }}"
            class="h-full w-full object-cover"
        >
    @else
        {{ strtoupper(
            mb_substr(
                $history->creator?->name ?? 'Hệ thống',
                0,
                1
            )
        ) }}
    @endif
</div>

    <div>

        <p class="text-xs text-gray-500">
            Thực hiện bởi
        </p>

        <p class="font-medium text-gray-900">
            {{ $history->creator?->name ?? 'Hệ thống' }}
        </p>

    </div>

</div>

                        @if ($history->source)
                            <div>

    <p class="text-xs text-gray-500">
        Nguồn thao tác
    </p>

    <span
        class="mt-1 inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700"
    >
        {{ match ($history->source) {
            'admin' => '🛠 Trang quản trị',
            'customer' => '👤 Khách hàng',
            'system' => '⚙ Hệ thống',
            default => $history->source,
        } }}
    </span>

</div>
                        @endif

                    </div>

                </div>

            </div>

        @empty

            <div class="py-10 text-center">

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
                        d="M12 6v6l4 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>

                <p class="mt-3 font-medium text-gray-700">
                    Chưa có lịch sử xử lý
                </p>

                <p class="mt-1 text-sm text-gray-500">
                    Các thay đổi trạng thái sẽ được hiển thị tại đây.
                </p>

            </div>

        @endforelse

    </div>

</div>

        </div>

        {{-- =========================
            Cột phải
        ========================== --}}
        <div class="space-y-6">

            {{-- Thông tin khách hàng --}}
            <div class="admin-card p-5">

                <h3 class="text-base font-semibold text-gray-900">
                    Khách hàng
                </h3>

                <div class="mt-4 space-y-3 text-sm">

                    <div>
                        <p class="text-gray-500">
                            Họ và tên
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            {{ $order->user?->name ?? $order->customer_name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">
                            Email
                        </p>

                        <p class="mt-1 break-all font-medium text-gray-900">
                            {{ $order->user?->email ?? $order->customer_email }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">
                            Số điện thoại
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            {{ $order->customer_phone ?: 'Chưa cập nhật' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">
                            Loại khách hàng
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            {{ $order->user_id ? 'Khách hàng có tài khoản' : 'Khách vãng lai' }}
                        </p>
                    </div>

                </div>

            </div>

            {{-- Địa chỉ giao hàng --}}
            <div class="admin-card p-5">

                <h3 class="text-base font-semibold text-gray-900">
                    Địa chỉ giao hàng
                </h3>

                @if ($order->shippingAddress)

                    <div class="mt-4 space-y-3 text-sm">

                        <div>
                            <p class="font-semibold text-gray-900">
                                {{ $order->shippingAddress->receiver_name }}
                            </p>

                            <p class="mt-1 text-gray-600">
                                {{ $order->shippingAddress->phone }}
                            </p>
                        </div>

                        @if ($order->shippingAddress->email)
                            <p class="break-all text-gray-600">
                                {{ $order->shippingAddress->email }}
                            </p>
                        @endif

                        <p class="leading-6 text-gray-600">
                            {{ $order->shippingAddress->formatted_address }}
                        </p>

                        @if ($order->shippingAddress->note)
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs font-medium uppercase text-gray-500">
                                    Ghi chú giao hàng
                                </p>

                                <p class="mt-1 text-gray-700">
                                    {{ $order->shippingAddress->note }}
                                </p>
                            </div>
                        @endif

                    </div>

                @else

                    <p class="mt-4 text-sm text-gray-500">
                        Chưa có địa chỉ giao hàng.
                    </p>

                @endif

            </div>

            {{-- Kho xử lý --}}
            <div class="admin-card p-5">

                <h3 class="text-base font-semibold text-gray-900">
                    Kho xử lý
                </h3>

                @if ($order->warehouse)

                    <div class="mt-4 space-y-3 text-sm">

                        <div>
                            <p class="font-medium text-gray-900">
                                {{ $order->warehouse->name }}
                            </p>

                            <p class="mt-1 leading-6 text-gray-600">
                                {{ $order->warehouse->address }}
                            </p>
                        </div>

                        <span
                            class="admin-badge {{ $order->warehouse->status
                                ? 'admin-badge-success'
                                : 'admin-badge-danger' }}"
                        >
                            {{ $order->warehouse->status
                                ? 'Đang hoạt động'
                                : 'Ngừng hoạt động' }}
                        </span>

                    </div>

                @else

                    <p class="mt-4 text-sm text-gray-500">
                        Đơn hàng chưa được gán kho.
                    </p>

                @endif

            </div>

            {{-- Coupon --}}
            <div class="admin-card p-5">

                <h3 class="text-base font-semibold text-gray-900">
                    Mã giảm giá
                </h3>

                @if ($order->coupon)

                    <div class="mt-4">

                        <div
                            class="rounded-xl border border-dashed border-pink-300 bg-pink-50 p-4"
                        >
                            <p class="font-bold text-pink-700">
                                {{ $order->coupon->code }}
                            </p>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ $order->coupon->name }}
                            </p>
                        </div>

                        <p class="mt-3 text-sm text-gray-600">
                            Đơn hàng đã giảm:

                            <span class="font-semibold text-green-600">
                                {{ number_format((float) $order->coupon_discount, 0, ',', '.') }}₫
                            </span>
                        </p>

                    </div>

                @else

                    <p class="mt-4 text-sm text-gray-500">
                        Đơn hàng không sử dụng coupon.
                    </p>

                @endif

            </div>

            {{-- Ghi chú --}}
            <div class="admin-card p-5">

                <h3 class="text-base font-semibold text-gray-900">
                    Ghi chú
                </h3>

                <div class="mt-4 space-y-4 text-sm">

                    <div>
                        <p class="font-medium text-gray-500">
                            Ghi chú của khách hàng
                        </p>

                        <p class="mt-1 whitespace-pre-line text-gray-700">
                            {{ $order->customer_note ?: 'Không có ghi chú.' }}
                        </p>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <p class="font-medium text-gray-500">
                            Ghi chú nội bộ
                        </p>

                        <p class="mt-1 whitespace-pre-line text-gray-700">
                            {{ $order->admin_note ?: 'Chưa có ghi chú nội bộ.' }}
                        </p>
                    </div>

                </div>

            </div>

            {{-- Chi tiết thanh toán --}}
            <div class="admin-card p-5">

                <h3 class="text-base font-semibold text-gray-900">
                    Tổng thanh toán
                </h3>

                <div class="mt-4 space-y-3 text-sm">

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-gray-500">
                            Tạm tính
                        </span>

                        <span class="font-medium text-gray-900">
                            {{ number_format((float) $order->subtotal, 0, ',', '.') }}₫
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-gray-500">
                            Giảm sản phẩm
                        </span>

                        <span class="font-medium text-green-600">
                            -{{ number_format((float) $order->product_discount, 0, ',', '.') }}₫
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-gray-500">
                            Giảm coupon
                        </span>

                        <span class="font-medium text-green-600">
                            -{{ number_format((float) $order->coupon_discount, 0, ',', '.') }}₫
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-gray-500">
                            Giảm điểm
                        </span>

                        <span class="font-medium text-green-600">
                            -{{ number_format((float) $order->point_discount, 0, ',', '.') }}₫
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-gray-500">
                            Phí vận chuyển
                        </span>

                        <span class="font-medium text-gray-900">
                            {{ number_format((float) $order->shipping_fee, 0, ',', '.') }}₫
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-gray-500">
                            Thuế
                        </span>

                        <span class="font-medium text-gray-900">
                            {{ number_format((float) $order->tax_amount, 0, ',', '.') }}₫
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between gap-4 border-t border-gray-200 pt-4"
                    >
                        <span class="font-semibold text-gray-900">
                            Tổng cộng
                        </span>

                        <span class="text-xl font-bold text-pink-600">
                            {{ number_format((float) $order->total_amount, 0, ',', '.') }}₫
                        </span>
                    </div>

                </div>

            </div>

            {{-- Hủy đơn hàng --}}
            @if ($order->canBeCancelled())

                <div
                    x-data="{
                        cancelOpen: {{ $errors->has('cancel_reason') || $errors->has('note') ? 'true' : 'false' }}
                    }"
                    class="admin-card overflow-hidden border-red-200"
                >

                    <div class="border-b border-red-100 bg-red-50 px-5 py-4">
                        <div class="flex items-start gap-3">

                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600"
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
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h3 class="font-semibold text-red-700">
                                    Hủy đơn hàng
                                </h3>

                                <p class="mt-1 text-sm leading-6 text-red-600">
                                    Thao tác này sẽ hoàn số lượng tồn kho đang giữ,
                                    hoàn lượt sử dụng coupon và hủy các thanh toán
                                    chưa hoàn tất.
                                </p>
                            </div>

                        </div>
                    </div>

                    <div class="p-5">

                        <button
                            type="button"
                            class="admin-btn admin-btn-danger w-full"
                            @click="cancelOpen = ! cancelOpen"
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
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>

                            Hủy đơn hàng
                        </button>

                        <div
                            x-show="cancelOpen"
                            x-cloak
                            x-transition
                            class="mt-5 border-t border-gray-200 pt-5"
                        >

                            <form
                                action="{{ route('admin.orders.cancel', $order) }}"
                                method="POST"
                                data-confirm="Bạn có chắc muốn hủy đơn hàng này? Thao tác sẽ thay đổi tồn kho, coupon và thanh toán."
                            >
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label
                                        for="cancel_reason"
                                        class="mb-2 block text-sm font-medium text-gray-700"
                                    >
                                        Lý do hủy đơn
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <textarea
                                        id="cancel_reason"
                                        name="cancel_reason"
                                        rows="4"
                                        class="admin-textarea"
                                        placeholder="Ví dụ: Khách hàng yêu cầu hủy đơn, thông tin giao hàng không hợp lệ..."
                                        required
                                    >{{ old('cancel_reason') }}</textarea>

                                    <div class="mt-2 flex items-center justify-between gap-3">
                                        <p class="text-xs text-gray-500">
                                            Tối thiểu 5 ký tự, tối đa 2.000 ký tự.
                                        </p>

                                        <span
                                            class="text-xs text-gray-400"
                                            data-character-counter
                                            data-character-target="cancel_reason"
                                            data-character-max="2000"
                                        >
                                            0/2000
                                        </span>
                                    </div>

                                    @error('cancel_reason')
                                        <p class="mt-2 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="mt-4">
                                    <label
                                        for="cancel_note"
                                        class="mb-2 block text-sm font-medium text-gray-700"
                                    >
                                        Ghi chú nội bộ
                                    </label>

                                    <textarea
                                        id="cancel_note"
                                        name="note"
                                        rows="3"
                                        class="admin-textarea"
                                        placeholder="Ghi chú chỉ dành cho nhân viên quản trị..."
                                    >{{ old('note') }}</textarea>

                                    @error('note')
                                        <p class="mt-2 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div
                                    class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"
                                >
                                    <p class="font-semibold">
                                        Lưu ý trước khi hủy:
                                    </p>

                                    <ul class="mt-2 list-inside list-disc space-y-1">
                                        <li>Đơn đã thanh toán sẽ bị chặn và phải hoàn tiền riêng.</li>
                                        <li>Số lượng đang giữ trong kho sẽ được giải phóng.</li>
                                        <li>Coupon đã dùng sẽ được hoàn lượt sử dụng.</li>
                                        <li>Trạng thái đơn, vận chuyển và thanh toán sẽ chuyển sang đã hủy.</li>
                                    </ul>
                                </div>

                                <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                    <button
                                        type="submit"
                                        class="admin-btn admin-btn-danger"
                                        data-loading-text="Đang hủy đơn..."
                                    >
                                        Xác nhận hủy đơn
                                    </button>

                                    <button
                                        type="button"
                                        class="admin-btn admin-btn-secondary"
                                        @click="cancelOpen = false"
                                    >
                                        Đóng
                                    </button>
                                </div>

                            </form>
                        </div>

                    </div>
                </div>

            @endif

            {{-- Thông tin đơn đã hủy --}}
            @if ($order->order_status === 'cancelled')

                <div class="rounded-xl border border-red-200 bg-red-50 p-5">
                    <h3 class="font-semibold text-red-700">
                        Đơn hàng đã bị hủy
                    </h3>

                    <div class="mt-3 space-y-2 text-sm text-red-700">
                        <p>
                            <span class="font-medium">Lý do:</span>
                            {{ $order->cancel_reason ?: 'Không có lý do.' }}
                        </p>

                        <p>
                            <span class="font-medium">Người hủy:</span>
                            {{ $order->canceller?->name ?? 'Khách hàng/Hệ thống' }}
                        </p>

                        <p>
                            <span class="font-medium">Thời gian:</span>
                            {{ $order->cancelled_at?->format('H:i d/m/Y') ?? 'Chưa xác định' }}
                        </p>
                    </div>
                </div>

            @endif
        </div>

    </div>

@endsection
