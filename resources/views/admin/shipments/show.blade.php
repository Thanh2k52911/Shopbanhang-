@extends('admin.layouts.master')

@section('title', 'Chi tiết kiện ' . $shipment->shipment_code)

@section('page-title', 'Chi tiết kiện vận chuyển')

@section('page-description', 'Theo dõi thông tin và lịch sử xử lý kiện hàng.')

@section('content')

    @php
        $shipmentStatuses = [
            'ready_to_ship' => 'Sẵn sàng giao',
            'picked_up' => 'Đã lấy hàng',
            'in_transit' => 'Đang vận chuyển',
            'out_for_delivery' => 'Đang giao hàng',
            'delivered' => 'Đã giao hàng',
            'delivery_failed' => 'Giao hàng thất bại',
            'returned' => 'Đã hoàn hàng',
            'cancelled' => 'Đã hủy',
        ];

        $statusBadgeClass = match ($shipment->status) {
            'ready_to_ship' => 'bg-purple-100 text-purple-700',
            'picked_up' => 'bg-blue-100 text-blue-700',
            'in_transit' => 'bg-cyan-100 text-cyan-700',
            'out_for_delivery' => 'bg-amber-100 text-amber-700',
            'delivered' => 'bg-green-100 text-green-700',
            'delivery_failed' => 'bg-red-100 text-red-700',
            'returned' => 'bg-gray-200 text-gray-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    @endphp

    {{-- Thanh điều hướng --}}
    <div
        class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="flex items-center gap-3">

            <a
                href="{{ route('admin.shipments.index') }}"
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
                        {{ $shipment->shipment_code }}
                    </h2>

                    <span class="admin-badge {{ $statusBadgeClass }}">
                        {{ $shipmentStatuses[$shipment->status] ?? $shipment->status }}
                    </span>

                </div>

                <p class="mt-1 text-sm text-gray-500">
                    Tạo lúc {{ $shipment->created_at?->format('H:i d/m/Y') }}
                </p>
            </div>

        </div>

        <div class="flex flex-wrap gap-3">

            @if ($shipment->order)
                <a
                    href="{{ route('admin.orders.show', $shipment->order) }}"
                    class="admin-btn admin-btn-secondary"
                >
                    Xem đơn hàng
                </a>
            @endif

            <button
                type="button"
                class="admin-btn admin-btn-secondary"
                data-copy-text="{{ $shipment->shipment_code }}"
            >
                Sao chép mã kiện
            </button>

        </div>
    </div>

    {{-- Tổng quan --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Trạng thái kiện
            </p>

            <p class="mt-3 text-lg font-bold text-gray-900">
                {{ $shipmentStatuses[$shipment->status] ?? $shipment->status }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Cập nhật lúc {{ $shipment->updated_at?->format('H:i d/m/Y') }}
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Đơn hàng
            </p>

            @if ($shipment->order)
                <a
                    href="{{ route('admin.orders.show', $shipment->order) }}"
                    class="mt-3 block text-lg font-bold text-pink-600 hover:text-pink-700"
                >
                    {{ $shipment->order->order_code }}
                </a>

                <p class="mt-2 text-xs text-gray-500">
                    {{ $shipment->order->customer_name }}
                </p>
            @else
                <p class="mt-3 text-lg font-bold text-gray-400">
                    Không tìm thấy
                </p>
            @endif
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Phí vận chuyển
            </p>

            <p class="mt-3 text-lg font-bold text-gray-900">
                {{ number_format((float) $shipment->shipping_fee, 0, ',', '.') }}₫
            </p>

            <p class="mt-2 text-xs text-gray-500">
                COD:
                {{ number_format((float) $shipment->cod_amount, 0, ',', '.') }}₫
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Giao hàng dự kiến
            </p>

            <p class="mt-3 text-lg font-bold text-gray-900">
                {{ $shipment->estimated_delivery_at?->format('d/m/Y')
                    ?? 'Chưa cập nhật' }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                {{ $shipment->service_name ?: 'Chưa cập nhật dịch vụ' }}
            </p>
        </div>

    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Cột trái --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- Thông tin vận chuyển --}}
            <div class="admin-card overflow-hidden">

                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Thông tin vận chuyển
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Đơn vị vận chuyển, mã vận đơn và thông số kiện hàng.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2">

                    <div>
                        <p class="text-sm text-gray-500">
                            Phương thức vận chuyển
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            {{ $shipment->shippingMethod?->name ?? 'Chưa chọn' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Đơn vị vận chuyển
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            {{ $shipment->carrier_name ?: 'Chưa cập nhật' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Dịch vụ
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            {{ $shipment->service_name ?: 'Chưa cập nhật' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Mã vận đơn
                        </p>

                        <div class="mt-1 flex items-center gap-2">

                            <p class="font-medium text-gray-900">
                                {{ $shipment->tracking_code ?: 'Chưa có' }}
                            </p>

                            @if ($shipment->tracking_code)
                                <button
                                    type="button"
                                    class="text-gray-400 transition hover:text-pink-600"
                                    data-copy-text="{{ $shipment->tracking_code }}"
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

                    <div>
                        <p class="text-sm text-gray-500">
                            Kho xuất hàng
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            {{ $shipment->warehouse?->name ?? 'Chưa xác định' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Trọng lượng
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            {{ $shipment->weight
                                ? number_format($shipment->weight) . ' gram'
                                : 'Chưa cập nhật' }}
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="text-sm text-gray-500">
                            Kích thước
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            @if (
                                $shipment->length
                                && $shipment->width
                                && $shipment->height
                            )
                                {{ $shipment->length }}
                                × {{ $shipment->width }}
                                × {{ $shipment->height }} cm
                            @else
                                Chưa cập nhật đầy đủ
                            @endif
                        </p>
                    </div>

                    @if ($shipment->note)
                        <div class="sm:col-span-2">
                            <p class="text-sm text-gray-500">
                                Ghi chú
                            </p>

                            <div
                                class="mt-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm leading-6 text-gray-700"
                            >
                                {{ $shipment->note }}
                            </div>
                        </div>
                    @endif

                </div>

            </div>

            {{-- Sản phẩm trong kiện --}}
            <div class="admin-card overflow-hidden">

                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Sản phẩm trong kiện
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Danh sách sản phẩm đã được đóng vào kiện hàng.
                    </p>
                </div>

                <div class="divide-y divide-gray-200">

                    @forelse ($shipment->items as $shipmentItem)

                        @php
                            $orderItem = $shipmentItem->orderItem;
                        @endphp

                        <div
                            class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center"
                        >

                            <div class="min-w-0 flex-1">

                                <p class="font-semibold text-gray-900">
                                    {{ $orderItem?->product_name
                                        ?? 'Sản phẩm không còn tồn tại' }}
                                </p>

                                <div
                                    class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-500"
                                >
                                    @if ($orderItem?->sku_code)
                                        <span>
                                            SKU: {{ $orderItem->sku_code }}
                                        </span>
                                    @endif

                                    @if ($orderItem?->variant_name)
                                        <span>
                                            Phân loại: {{ $orderItem->variant_name }}
                                        </span>
                                    @endif
                                </div>

                            </div>

                            <div class="shrink-0 text-left sm:text-right">

                                <p class="text-sm text-gray-500">
                                    Số lượng trong kiện
                                </p>

                                <p class="mt-1 text-lg font-bold text-gray-900">
                                    {{ number_format($shipmentItem->quantity) }}
                                </p>

                            </div>

                        </div>

                    @empty

                        <div class="p-10 text-center text-sm text-gray-500">
                            Kiện hàng chưa có sản phẩm.
                        </div>

                    @endforelse

                </div>

            </div>

            {{-- Lịch sử vận chuyển --}}
            <div class="admin-card overflow-hidden">

                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Lịch sử vận chuyển
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Theo dõi toàn bộ thay đổi trạng thái của kiện hàng.
                    </p>
                </div>

                <div class="p-5">

                    @forelse (
                        $shipment->statusHistories->sortByDesc('occurred_at')
                        as $history
                    )

                        <div class="relative flex gap-4 pb-7 last:pb-0">

                            @if (! $loop->last)
                                <div
                                    class="absolute left-[7px] top-5 h-full w-px bg-gray-200"
                                ></div>
                            @endif

                            <div
                                class="relative mt-1.5 h-4 w-4 shrink-0 rounded-full border-4 border-white bg-blue-500 shadow"
                            ></div>

                            <div class="min-w-0 flex-1">

                                <div
                                    class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"
                                >
                                    <div>
                                        <p class="font-semibold text-gray-900">
                                            {{ $shipmentStatuses[$history->to_status]
                                                ?? $history->to_status }}
                                        </p>

                                        @if ($history->from_status)
                                            <p class="mt-1 text-sm text-gray-500">
                                                Chuyển từ:
                                                <span class="font-medium text-gray-700">
                                                    {{ $shipmentStatuses[$history->from_status]
                                                        ?? $history->from_status }}
                                                </span>
                                            </p>
                                        @endif
                                    </div>

                                    <div class="shrink-0 text-left sm:text-right">
                                        <p class="text-sm font-medium text-gray-700">
                                            {{ $history->occurred_at?->format('d/m/Y') }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $history->occurred_at?->format('H:i:s') }}
                                        </p>
                                    </div>
                                </div>

                                @if ($history->description)
                                    <div
                                        class="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm leading-6 text-gray-700"
                                    >
                                        {{ $history->description }}
                                    </div>
                                @endif

                                <div
                                    class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-xs text-gray-500"
                                >
                                    <span>
                                        Vị trí:
                                        <span class="font-medium text-gray-700">
                                            {{ $history->location ?: 'Chưa cập nhật' }}
                                        </span>
                                    </span>

                                    <span>
                                        Người thực hiện:
                                        <span class="font-medium text-gray-700">
                                            {{ $history->creator?->name ?? 'Hệ thống' }}
                                        </span>
                                    </span>

                                    <span>
                                        Nguồn:
                                        <span class="font-medium text-gray-700">
                                            {{ $history->source ?: 'system' }}
                                        </span>
                                    </span>
                                </div>

                            </div>

                        </div>

                    @empty

                        <div class="py-10 text-center text-sm text-gray-500">
                            Chưa có lịch sử vận chuyển.
                        </div>

                    @endforelse

                </div>

            </div>

        </div>

        {{-- Cột phải --}}
        <div class="space-y-6">

            {{-- Thông tin khách hàng --}}
            <div class="admin-card p-5">

                <h3 class="text-base font-semibold text-gray-900">
                    Người nhận hàng
                </h3>

                @if ($shipment->order)

                    <div class="mt-4 space-y-4 text-sm">

                        <div>
                            <p class="text-gray-500">
                                Họ tên
                            </p>

                            <p class="mt-1 font-medium text-gray-900">
                                {{ $shipment->order->customer_name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500">
                                Số điện thoại
                            </p>

                            <p class="mt-1 font-medium text-gray-900">
                                {{ $shipment->order->customer_phone }}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500">
                                Email
                            </p>

                            <p class="mt-1 break-all font-medium text-gray-900">
                                {{ $shipment->order->customer_email }}
                            </p>
                        </div>

                    </div>

                @else

                    <p class="mt-4 text-sm text-gray-500">
                        Không tìm thấy thông tin đơn hàng.
                    </p>

                @endif

            </div>

            {{-- Mốc thời gian --}}
            <div class="admin-card p-5">

                <h3 class="text-base font-semibold text-gray-900">
                    Các mốc vận chuyển
                </h3>

                <div class="mt-4 space-y-4 text-sm">

                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">
                            Tạo kiện
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ $shipment->created_at?->format('H:i d/m/Y') }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">
                            Đã lấy hàng
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ $shipment->picked_up_at?->format('H:i d/m/Y')
                                ?? 'Chưa có' }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">
                            Đã giao hàng
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ $shipment->delivered_at?->format('H:i d/m/Y')
                                ?? 'Chưa có' }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">
                            Giao thất bại
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ $shipment->failed_at?->format('H:i d/m/Y')
                                ?? 'Chưa có' }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">
                            Hủy kiện
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ $shipment->cancelled_at?->format('H:i d/m/Y')
                                ?? 'Chưa có' }}
                        </span>
                    </div>

                </div>

            </div>

            {{-- Cập nhật trạng thái Shipment --}}
<div class="admin-card p-5">

    <div>
        <h3 class="text-base font-semibold text-gray-900">
            Cập nhật trạng thái
        </h3>

        <p class="mt-1 text-sm text-gray-500">
            Chuyển kiện hàng sang bước vận chuyển tiếp theo.
        </p>
    </div>

    <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4">

        <p class="text-sm text-blue-600">
            Trạng thái hiện tại
        </p>

        <p class="mt-1 font-semibold text-blue-700">
            {{ $shipmentStatuses[$shipment->status] ?? $shipment->status }}
        </p>

    </div>

    @if (! empty($nextShipmentStatuses))

        <form
            action="{{ route('admin.shipments.update-status', $shipment) }}"
            method="POST"
            class="mt-5"
            data-confirm="Bạn có chắc muốn cập nhật trạng thái kiện vận chuyển này?"
        >
            @csrf
            @method('PATCH')

            {{-- Trạng thái tiếp theo --}}
            <div>

                <label
                    for="status"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Trạng thái tiếp theo
                </label>

                <select
                    id="status"
                    name="status"
                    class="admin-select"
                    required
                >
                    @foreach ($nextShipmentStatuses as $status => $label)
                        <option
                            value="{{ $status }}"
                            @selected(
                                old(
                                    'status',
                                    array_key_first($nextShipmentStatuses)
                                ) === $status
                            )
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                @error('status')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            {{-- Vị trí hiện tại --}}
            <div class="mt-4">

                <label
                    for="location"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Vị trí hiện tại
                </label>

                <input
                    id="location"
                    type="text"
                    name="location"
                    value="{{ old('location') }}"
                    class="admin-input"
                    placeholder="Ví dụ: Kho Hà Nội, Bưu cục Cầu Giấy..."
                >

                @error('location')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            {{-- Mô tả --}}
            <div class="mt-4">

                <label
                    for="description"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Mô tả cập nhật
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    class="admin-textarea"
                    placeholder="Ví dụ: Đơn vị vận chuyển đã lấy kiện hàng tại kho..."
                >{{ old('description') }}</textarea>

                @error('description')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            {{-- Cảnh báo trạng thái đặc biệt --}}
            <div class="mt-4 space-y-3">

                <div
                    data-shipment-status-warning="delivered"
                    class="hidden rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"
                >
                    Khi xác nhận đã giao hàng, hệ thống sẽ tự hoàn thành đơn và
                    ghi nhận thanh toán cho đơn COD.
                </div>

                <div
                    data-shipment-status-warning="delivery_failed"
                    class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                >
                    Hãy nhập rõ lý do giao hàng thất bại trong phần mô tả.
                </div>

                <div
                    data-shipment-status-warning="cancelled"
                    class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                >
                    Hủy kiện chỉ hủy luồng vận chuyển, không tự hủy đơn hàng.
                </div>

            </div>

            <button
                type="submit"
                class="admin-btn admin-btn-primary mt-5 w-full"
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

        </form>

    @else

        <div
            class="mt-5 rounded-xl border border-gray-200 bg-gray-50 px-4 py-4 text-sm text-gray-600"
        >
            @switch($shipment->status)

                @case('delivered')
                    Kiện hàng đã giao thành công, không còn bước tiếp theo.
                    @break

                @case('returned')
                    Kiện hàng đã được hoàn trả.
                    @break

                @case('cancelled')
                    Kiện vận chuyển đã bị hủy.
                    @break

                @default
                    Không có trạng thái tiếp theo phù hợp.
            @endswitch
        </div>

    @endif

</div>

        </div>

    </div>
        @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusSelect = document.getElementById('status');

            if (!statusSelect) {
                return;
            }

            const warningElements = document.querySelectorAll(
                '[data-shipment-status-warning]'
            );

            const updateWarnings = function () {
                warningElements.forEach(function (element) {
                    const targetStatus = element.getAttribute(
                        'data-shipment-status-warning'
                    );

                    element.classList.toggle(
                        'hidden',
                        targetStatus !== statusSelect.value
                    );
                });
            };

            statusSelect.addEventListener('change', updateWarnings);

            updateWarnings();
        });
    </script>
@endpush
@endsection
