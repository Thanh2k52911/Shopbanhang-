@extends('admin.layouts.master')

@section('title', 'Quản lý vận chuyển')

@section('page-title', 'Quản lý vận chuyển')

@section('page-description', 'Theo dõi, tìm kiếm và quản lý toàn bộ kiện vận chuyển trong hệ thống.')

@section('content')

    {{-- Thống kê nhanh --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Tổng kiện hàng
            </p>

            <p class="mt-2 text-2xl font-bold text-gray-900">
                {{ number_format($shipments->total()) }}
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Sẵn sàng giao
            </p>

            <p class="mt-2 text-2xl font-bold text-purple-600">
                {{ number_format(
                    \App\Models\Shipment::where('status', 'ready_to_ship')->count()
                ) }}
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Đang vận chuyển
            </p>

            <p class="mt-2 text-2xl font-bold text-cyan-600">
                {{ number_format(
                    \App\Models\Shipment::whereIn('status', [
                        'picked_up',
                        'in_transit',
                        'out_for_delivery',
                    ])->count()
                ) }}
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Đã giao hàng
            </p>

            <p class="mt-2 text-2xl font-bold text-green-600">
                {{ number_format(
                    \App\Models\Shipment::where('status', 'delivered')->count()
                ) }}
            </p>
        </div>

    </div>


    @if ($ordersAwaitingShipment->isNotEmpty())
        <div class="admin-card mt-6 overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-semibold text-gray-900">
                    Đơn đã đóng gói đang chờ tạo kiện
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Hệ thống đã sẵn sàng tự lấy phương thức giao hàng, địa chỉ, COD, trọng lượng và sinh mã vận đơn mô phỏng.
                </p>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach ($ordersAwaitingShipment as $pendingOrder)
                    <div class="flex flex-col gap-4 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-4">
                            <div>
                                <p class="text-xs text-gray-500">Mã đơn</p>
                                <p class="font-semibold text-gray-900">{{ $pendingOrder->order_code }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Khách hàng</p>
                                <p class="font-medium text-gray-900">{{ $pendingOrder->customer_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Phương thức</p>
                                <p class="font-medium text-gray-900">{{ $pendingOrder->shippingMethod?->name ?: 'Tự động xác định' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Kho xử lý</p>
                                <p class="font-medium text-gray-900">{{ $pendingOrder->warehouse?->name }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.shipments.create', $pendingOrder) }}" class="admin-btn admin-btn-secondary">
                                Kiểm tra trước
                            </a>
                            <form action="{{ route('admin.shipments.store-automatic', $pendingOrder) }}" method="POST">
                                @csrf
                                <button type="submit" class="admin-btn admin-btn-primary">
                                    Tạo kiện tự động
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Bộ lọc --}}
    <div class="admin-card mt-6 p-5">

        <div class="mb-5">
            <h2 class="text-base font-semibold text-gray-900">
                Tìm kiếm và lọc kiện hàng
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Tìm theo mã kiện, mã vận đơn, mã đơn hàng, khách hàng hoặc đơn vị vận chuyển.
            </p>
        </div>

        <form
            action="{{ route('admin.shipments.index') }}"
            method="GET"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4"
        >

            {{-- Từ khóa --}}
            <div>
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
                    placeholder="Mã kiện, vận đơn, đơn hàng..."
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

            {{-- Đơn vị vận chuyển --}}
            <div>
                <label
                    for="carrier_name"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Đơn vị vận chuyển
                </label>

                <select
                    id="carrier_name"
                    name="carrier_name"
                    class="admin-select"
                >
                    <option value="">
                        Tất cả đơn vị
                    </option>

                    @foreach ($carriers as $carrier)
                        <option
                            value="{{ $carrier }}"
                            @selected(request('carrier_name') === $carrier)
                        >
                            {{ $carrier }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Nút lọc --}}
            <div class="flex items-end gap-3">

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
                    href="{{ route('admin.shipments.index') }}"
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
                    Danh sách kiện vận chuyển
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Tìm thấy {{ number_format($shipments->total()) }} kiện hàng.
                </p>
            </div>

            <p class="text-sm text-gray-500">
                Trang {{ $shipments->currentPage() }}/{{ $shipments->lastPage() }}
            </p>
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Mã kiện
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
                            Đơn vị vận chuyển
                        </th>

                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Mã vận đơn
                        </th>

                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Trạng thái
                        </th>

                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Phí giao hàng
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

                    @forelse ($shipments as $shipment)

                        @php
                            $statusClass = match ($shipment->status) {
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

                        <tr class="transition hover:bg-gray-50">

                            {{-- Mã kiện --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                <a
                                    href="{{ route('admin.shipments.show', $shipment) }}"
                                    class="font-semibold text-gray-900 hover:text-pink-600"
                                >
                                    {{ $shipment->shipment_code }}
                                </a>

                                <p class="mt-1 text-xs text-gray-500">
                                    ID: {{ $shipment->id }}
                                </p>

                            </td>

                            {{-- Đơn hàng --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                @if ($shipment->order)
                                    <a
                                        href="{{ route('admin.orders.show', $shipment->order) }}"
                                        class="font-medium text-gray-900 hover:text-pink-600"
                                    >
                                        {{ $shipment->order->order_code }}
                                    </a>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $shipment->order->order_status }}
                                    </p>
                                @else
                                    <span class="text-sm text-gray-500">
                                        Không tìm thấy đơn hàng
                                    </span>
                                @endif

                            </td>

                            {{-- Khách hàng --}}
                            <td class="min-w-[190px] px-5 py-4">

                                <p class="font-medium text-gray-900">
                                    {{ $shipment->order?->customer_name ?? 'Chưa có' }}
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $shipment->order?->customer_phone ?? 'Chưa có số điện thoại' }}
                                </p>

                            </td>

                            {{-- Đơn vị vận chuyển --}}
                            <td class="min-w-[170px] px-5 py-4">

                                <p class="font-medium text-gray-900">
                                    {{ $shipment->shippingMethod?->name
                                        ?? $shipment->carrier_name
                                        ?? 'Chưa xác định' }}
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $shipment->service_name ?: 'Chưa cập nhật dịch vụ' }}
                                </p>

                            </td>

                            {{-- Mã vận đơn --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                <div class="flex items-center gap-2">

                                    <span class="font-medium text-gray-900">
                                        {{ $shipment->tracking_code ?: 'Chưa có' }}
                                    </span>

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

                            </td>

                            {{-- Trạng thái --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                <span class="admin-badge {{ $statusClass }}">
                                    {{ $statuses[$shipment->status] ?? $shipment->status }}
                                </span>

                            </td>

                            {{-- Phí giao hàng --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                <p class="font-semibold text-gray-900">
                                    {{ number_format(
                                        (float) $shipment->shipping_fee,
                                        0,
                                        ',',
                                        '.'
                                    ) }}₫
                                </p>

                                @if ((float) $shipment->cod_amount > 0)
                                    <p class="mt-1 text-xs text-green-600">
                                        COD:
                                        {{ number_format(
                                            (float) $shipment->cod_amount,
                                            0,
                                            ',',
                                            '.'
                                        ) }}₫
                                    </p>
                                @endif

                            </td>

                            {{-- Ngày tạo --}}
                            <td class="whitespace-nowrap px-5 py-4">

                                <p class="text-sm font-medium text-gray-700">
                                    {{ $shipment->created_at?->format('d/m/Y') }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $shipment->created_at?->format('H:i') }}
                                </p>

                            </td>

                            {{-- Thao tác --}}
<td class="min-w-[260px] px-5 py-4">

    @php
        $nextStatuses = $shipmentTransitions[$shipment->id] ?? [];
    @endphp

    <div class="flex flex-col gap-3">

        {{-- Cập nhật nhanh --}}
        @if (! empty($nextStatuses))

            <form
                action="{{ route('admin.shipments.update-status', $shipment) }}"
                method="POST"
                class="flex items-center gap-2"
                data-confirm="Bạn có chắc muốn cập nhật trạng thái kiện hàng này?"
            >
                @csrf
                @method('PATCH')

                <select
                    name="status"
                    class="admin-select min-w-[145px]"
                    required
                    aria-label="Trạng thái tiếp theo"
                >
                    @foreach ($nextStatuses as $nextStatus => $nextLabel)
                        <option value="{{ $nextStatus }}">
                            {{ $nextLabel }}
                        </option>
                    @endforeach
                </select>

                <button
                    type="submit"
                    class="admin-btn admin-btn-primary shrink-0"
                    data-loading-text="Đang lưu..."
                >
                    Cập nhật
                </button>

            </form>

        @else

            <div
                class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-center text-xs font-medium text-gray-500"
            >
                @switch($shipment->status)

                    @case('delivered')
                        Đã giao hoàn tất
                        @break

                    @case('returned')
                        Đã hoàn hàng
                        @break

                    @case('cancelled')
                        Kiện đã hủy
                        @break

                    @default
                        Không có bước tiếp theo
                @endswitch
            </div>

        @endif

        {{-- Xem chi tiết --}}
        <a
            href="{{ route('admin.shipments.show', $shipment) }}"
            class="admin-btn admin-btn-secondary w-full"
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
                    d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12z"
                />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
            </svg>

            Xem chi tiết
        </a>

    </div>

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
                                        d="M3 6h11.25v9H3V6zm11.25 3H18l3 3v3h-6.75V9z"
                                    />
                                </svg>

                                <p class="mt-3 font-medium text-gray-700">
                                    Chưa có kiện vận chuyển
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    Các kiện hàng được tạo sẽ hiển thị tại đây.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if ($shipments->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $shipments->links() }}
            </div>
        @endif

    </div>

@endsection
