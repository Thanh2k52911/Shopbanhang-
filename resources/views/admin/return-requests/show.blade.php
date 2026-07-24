@extends('admin.layouts.master')

@section('title', 'Chi tiết yêu cầu ' . $returnRequest->return_code)

@section('page-title', 'Chi tiết yêu cầu trả hàng')

@section(
    'page-description',
    'Theo dõi thông tin, sản phẩm, bằng chứng và quá trình xử lý yêu cầu.'
)

@section('content')

    @php
        $statusBadgeClass = match ($returnRequest->status) {
            'pending' => 'bg-amber-100 text-amber-700',
            'approved' => 'bg-blue-100 text-blue-700',
            'waiting_for_return' => 'bg-purple-100 text-purple-700',
            'returning' => 'bg-indigo-100 text-indigo-700',
            'received' => 'bg-cyan-100 text-cyan-700',
            'inspecting' => 'bg-orange-100 text-orange-700',
            'processing' => 'bg-pink-100 text-pink-700',
            'completed' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            'cancelled' => 'bg-gray-200 text-gray-700',
            default => 'bg-gray-100 text-gray-700',
        };

        $typeBadgeClass = match ($returnRequest->request_type) {
            'return' => 'bg-blue-50 text-blue-700',
            'refund' => 'bg-green-50 text-green-700',
            'return_refund' => 'bg-purple-50 text-purple-700',
            'exchange' => 'bg-cyan-50 text-cyan-700',
            default => 'bg-gray-100 text-gray-700',
        };
    @endphp

    {{-- Điều hướng --}}
    <div
        class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="flex items-center gap-3">

            <a
                href="{{ route('admin.return-requests.index') }}"
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
                        {{ $returnRequest->return_code }}
                    </h2>

                    <span class="admin-badge {{ $statusBadgeClass }}">
                        {{ $statuses[$returnRequest->status]
                            ?? $returnRequest->status }}
                    </span>

                    <span
                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $typeBadgeClass }}"
                    >
                        {{ $requestTypes[$returnRequest->request_type]
                            ?? $returnRequest->request_type }}
                    </span>

                </div>

                <p class="mt-1 text-sm text-gray-500">
                    Tạo lúc
                    {{ $returnRequest->created_at?->format('H:i d/m/Y') }}
                </p>

            </div>

        </div>

        <div class="flex flex-wrap gap-3">

            @if ($returnRequest->order)
                <a
                    href="{{ route(
                        'admin.orders.show',
                        $returnRequest->order
                    ) }}"
                    class="admin-btn admin-btn-secondary"
                >
                    Xem đơn hàng
                </a>
            @endif

            <button
                type="button"
                class="admin-btn admin-btn-secondary"
                data-copy-text="{{ $returnRequest->return_code }}"
            >
                Sao chép mã yêu cầu
            </button>

        </div>
    </div>

    {{-- Thống kê tổng quan --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

        <div class="admin-card p-5">

            <p class="text-sm font-medium text-gray-500">
                Trạng thái
            </p>

            <p class="mt-3 text-lg font-bold text-gray-900">
                {{ $statuses[$returnRequest->status]
                    ?? $returnRequest->status }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Cập nhật lúc
                {{ $returnRequest->updated_at?->format('H:i d/m/Y') }}
            </p>

        </div>

        <div class="admin-card p-5">

            <p class="text-sm font-medium text-gray-500">
                Số tiền yêu cầu
            </p>

            <p class="mt-3 text-lg font-bold text-pink-600">
                {{ number_format(
                    (float) $returnRequest->requested_amount,
                    0,
                    ',',
                    '.'
                ) }}₫
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Khách hàng đề nghị
            </p>

        </div>

        <div class="admin-card p-5">

            <p class="text-sm font-medium text-gray-500">
                Số tiền được duyệt
            </p>

            <p class="mt-3 text-lg font-bold text-green-600">
                @if (! is_null($returnRequest->approved_amount))
                    {{ number_format(
                        (float) $returnRequest->approved_amount,
                        0,
                        ',',
                        '.'
                    ) }}₫
                @else
                    Chưa duyệt
                @endif
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Phí gửi trả:
                {{ number_format(
                    (float) $returnRequest->return_shipping_fee,
                    0,
                    ',',
                    '.'
                ) }}₫
            </p>

        </div>

        <div class="admin-card p-5">

            <p class="text-sm font-medium text-gray-500">
                Sản phẩm yêu cầu
            </p>

            <p class="mt-3 text-lg font-bold text-gray-900">
                {{ number_format($returnRequest->items->count()) }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                {{ number_format($returnRequest->images->count()) }}
                ảnh bằng chứng
            </p>

        </div>

    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Cột trái --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- Nội dung yêu cầu --}}
            <div class="admin-card overflow-hidden">

                <div class="border-b border-gray-200 px-5 py-4">

                    <h3 class="text-base font-semibold text-gray-900">
                        Nội dung yêu cầu
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Lý do và thông tin khách hàng cung cấp.
                    </p>

                </div>

                <div class="space-y-5 p-5">

                    <div>

                        <p class="text-sm text-gray-500">
                            Lý do
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $returnRequest->reason }}
                        </p>

                    </div>

                    @if ($returnRequest->description)
                        <div>

                            <p class="text-sm text-gray-500">
                                Mô tả chi tiết
                            </p>

                            <div
                                class="mt-2 whitespace-pre-line rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm leading-6 text-gray-700"
                            >
                                {{ $returnRequest->description }}
                            </div>

                        </div>
                    @endif

                    @if ($returnRequest->customer_note)
                        <div>

                            <p class="text-sm text-gray-500">
                                Ghi chú của khách hàng
                            </p>

                            <div
                                class="mt-2 whitespace-pre-line rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm leading-6 text-blue-700"
                            >
                                {{ $returnRequest->customer_note }}
                            </div>

                        </div>
                    @endif

                    @if ($returnRequest->admin_note)
                        <div>

                            <p class="text-sm text-gray-500">
                                Ghi chú nội bộ
                            </p>

                            <div
                                class="mt-2 whitespace-pre-line rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm leading-6 text-gray-700"
                            >
                                {{ $returnRequest->admin_note }}
                            </div>

                        </div>
                    @endif

                    @if ($returnRequest->rejection_reason)
                        <div>

                            <p class="text-sm text-gray-500">
                                Lý do từ chối
                            </p>

                            <div
                                class="mt-2 whitespace-pre-line rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm leading-6 text-red-700"
                            >
                                {{ $returnRequest->rejection_reason }}
                            </div>

                        </div>
                    @endif

                </div>

            </div>

            {{-- Sản phẩm yêu cầu trả --}}
            <div class="admin-card overflow-hidden">

                <div class="border-b border-gray-200 px-5 py-4">

                    <h3 class="text-base font-semibold text-gray-900">
                        Sản phẩm trong yêu cầu
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Danh sách sản phẩm khách hàng yêu cầu xử lý.
                    </p>

                </div>

                <div class="divide-y divide-gray-200">

                    @forelse ($returnRequest->items as $returnItem)

                        @php
                            $orderItem = $returnItem->orderItem;
                        @endphp

                        <div class="p-5">

                            <div
                                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                            >

                                <div class="min-w-0 flex-1">

                                    <p class="font-semibold text-gray-900">
                                        {{ $orderItem?->product_name
                                            ?? 'Sản phẩm không còn tồn tại' }}
                                    </p>

                                    <div
                                        class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-gray-500"
                                    >

                                        @if ($orderItem?->sku_code)
                                            <span>
                                                SKU:
                                                {{ $orderItem->sku_code }}
                                            </span>
                                        @endif

                                        @if ($orderItem?->variant_name)
                                            <span>
                                                Phân loại:
                                                {{ $orderItem->variant_name }}
                                            </span>
                                        @endif

                                    </div>

                                </div>

                                <div class="shrink-0 text-left sm:text-right">

                                    <p class="text-sm text-gray-500">
                                        Số lượng trả
                                    </p>

                                    <p class="mt-1 text-xl font-bold text-gray-900">
                                        {{ number_format($returnItem->quantity) }}
                                    </p>

                                </div>

                            </div>

                            <div
                                class="mt-5 grid grid-cols-1 gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4 sm:grid-cols-2 lg:grid-cols-4"
                            >

                                <div>

                                    <p class="text-xs uppercase tracking-wide text-gray-500">
                                        Tình trạng
                                    </p>

                                    <p class="mt-1 text-sm font-medium text-gray-900">
                                        {{ $productConditions[
                                            $returnItem->product_condition
                                        ] ?? $returnItem->product_condition ?? 'Chưa cập nhật' }}
                                    </p>

                                </div>

                                <div>

                                    <p class="text-xs uppercase tracking-wide text-gray-500">
                                        Kết quả kiểm tra
                                    </p>

                                    <p class="mt-1 text-sm font-medium text-gray-900">
                                        {{ $inspectionResults[
                                            $returnItem->inspection_result
                                        ] ?? $returnItem->inspection_result ?? 'Chưa kiểm tra' }}
                                    </p>

                                </div>

                                <div>

                                    <p class="text-xs uppercase tracking-wide text-gray-500">
                                        Xử lý tồn kho
                                    </p>

                                    <p class="mt-1 text-sm font-medium text-gray-900">
                                        {{ $inventoryActions[
                                            $returnItem->inventory_action
                                        ] ?? $returnItem->inventory_action ?? 'Chưa xác định' }}
                                    </p>

                                </div>

                                <div>

                                    <p class="text-xs uppercase tracking-wide text-gray-500">
                                        Tiền hoàn được duyệt
                                    </p>

                                    <p class="mt-1 text-sm font-bold text-pink-600">
                                        @if (! is_null(
                                            $returnItem->approved_refund_amount
                                        ))
                                            {{ number_format(
                                                (float) $returnItem->approved_refund_amount,
                                                0,
                                                ',',
                                                '.'
                                            ) }}₫
                                        @else
                                            Chưa duyệt
                                        @endif
                                    </p>

                                </div>

                            </div>

                            @if ($returnItem->inspection_note)
                                <div
                                    class="mt-4 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm leading-6 text-gray-700"
                                >
                                    <span class="font-medium">
                                        Ghi chú kiểm tra:
                                    </span>

                                    {{ $returnItem->inspection_note }}
                                </div>
                            @endif

                        </div>

                    @empty

                        <div class="p-10 text-center text-sm text-gray-500">
                            Yêu cầu chưa có sản phẩm.
                        </div>

                    @endforelse

                </div>

            </div>

            {{-- Ảnh bằng chứng --}}
            <div class="admin-card overflow-hidden">

                <div class="border-b border-gray-200 px-5 py-4">

                    <h3 class="text-base font-semibold text-gray-900">
                        Ảnh bằng chứng
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Hình ảnh khách hàng hoặc nhân viên cung cấp.
                    </p>

                </div>

                @if ($returnRequest->images->isNotEmpty())

                    <div class="grid grid-cols-2 gap-4 p-5 md:grid-cols-3">

                        @foreach ($returnRequest->images as $image)

                            <a
                                href="{{ asset('storage/' . $image->image_path) }}"
                                target="_blank"
                                class="group block overflow-hidden rounded-xl border border-gray-200 bg-gray-50"
                            >

                                <img
                                    src="{{ asset('storage/' . $image->image_path) }}"
                                    alt="Ảnh yêu cầu trả hàng"
                                    class="aspect-square w-full object-cover transition duration-200 group-hover:scale-105"
                                >

                                @if ($image->caption)
                                    <p
                                        class="border-t border-gray-200 px-3 py-2 text-xs text-gray-600"
                                    >
                                        {{ $image->caption }}
                                    </p>
                                @endif

                            </a>

                        @endforeach

                    </div>

                @else

                    <div class="p-10 text-center text-sm text-gray-500">
                        Yêu cầu chưa có ảnh bằng chứng.
                    </div>

                @endif

            </div>

            {{-- Lịch sử trạng thái --}}
            <div class="admin-card overflow-hidden">

                <div class="border-b border-gray-200 px-5 py-4">

                    <h3 class="text-base font-semibold text-gray-900">
                        Lịch sử xử lý
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Toàn bộ thay đổi trạng thái của yêu cầu.
                    </p>

                </div>

                <div class="p-5">

                    @forelse (
                        $returnRequest->statusHistories->sortByDesc('created_at')
                        as $history
                    )

                        <div class="relative flex gap-4 pb-7 last:pb-0">

                            @if (! $loop->last)
                                <div
                                    class="absolute left-[7px] top-5 h-full w-px bg-gray-200"
                                ></div>
                            @endif

                            <div
                                class="relative mt-1.5 h-4 w-4 shrink-0 rounded-full border-4 border-white bg-pink-500 shadow"
                            ></div>

                            <div class="min-w-0 flex-1">

                                <div
                                    class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"
                                >

                                    <div>

                                        <p class="font-semibold text-gray-900">
                                            {{ $statuses[$history->to_status]
                                                ?? $history->to_status }}
                                        </p>

                                        @if ($history->from_status)
                                            <p class="mt-1 text-sm text-gray-500">
                                                Chuyển từ:
                                                <span class="font-medium text-gray-700">
                                                    {{ $statuses[$history->from_status]
                                                        ?? $history->from_status }}
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
                                        class="mt-3 whitespace-pre-line rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm leading-6 text-gray-700"
                                    >
                                        {{ $history->note }}
                                    </div>
                                @endif

                                <div
                                    class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-xs text-gray-500"
                                >

                                    <span>
                                        Người thực hiện:
                                        <span class="font-medium text-gray-700">
                                            {{ $history->creator?->name
                                                ?? 'Hệ thống' }}
                                        </span>
                                    </span>

                                    @if ($history->source)
                                        <span>
                                            Nguồn:
                                            <span class="font-medium text-gray-700">
                                                {{ $history->source }}
                                            </span>
                                        </span>
                                    @endif

                                </div>

                            </div>

                        </div>

                    @empty

                        <div class="py-10 text-center text-sm text-gray-500">
                            Chưa có lịch sử xử lý.
                        </div>

                    @endforelse

                </div>

            </div>

            {{-- Refund liên quan --}}
            <div class="admin-card overflow-hidden">

                <div class="border-b border-gray-200 px-5 py-4">

                    <h3 class="text-base font-semibold text-gray-900">
                        Hoàn tiền liên quan
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Các Refund được tạo từ yêu cầu này.
                    </p>

                </div>

                <div class="divide-y divide-gray-200">

                    @forelse ($returnRequest->refunds as $refund)

                        <div
                            class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between"
                        >

                            <div>

                                <p class="font-semibold text-gray-900">
                                    {{ $refund->refund_code }}
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    Trạng thái:
                                    {{ $refund->status }}
                                </p>

                            </div>

                            <div class="shrink-0 text-left sm:text-right">

                                <p class="font-bold text-pink-600">
                                    {{ number_format(
                                        (float) $refund->amount,
                                        0,
                                        ',',
                                        '.'
                                    ) }}₫
                                </p>

                                @if ($refund->payment)
                                    <a
                                        href="{{ route(
                                            'admin.payments.show',
                                            $refund->payment
                                        ) }}"
                                        class="mt-2 inline-block text-sm font-medium text-blue-600 hover:text-blue-700"
                                    >
                                        Xem Payment
                                    </a>
                                @endif

                            </div>

                        </div>

                    @empty

                        <div class="p-10 text-center text-sm text-gray-500">
                            Chưa có Refund liên quan.
                        </div>

                    @endforelse

                </div>

            </div>

        </div>

        {{-- Cột phải --}}
        <div class="space-y-6">

            {{-- Đơn hàng --}}
            <div class="admin-card p-5">

                <h3 class="text-base font-semibold text-gray-900">
                    Đơn hàng
                </h3>

                @if ($returnRequest->order)

                    <div class="mt-4 space-y-4 text-sm">

                        <div>

                            <p class="text-gray-500">
                                Mã đơn
                            </p>

                            <a
                                href="{{ route(
                                    'admin.orders.show',
                                    $returnRequest->order
                                ) }}"
                                class="mt-1 block font-semibold text-pink-600 hover:text-pink-700"
                            >
                                {{ $returnRequest->order->order_code }}
                            </a>

                        </div>

                        <div>

                            <p class="text-gray-500">
                                Trạng thái đơn
                            </p>

                            <p class="mt-1 font-medium text-gray-900">
                                {{ $returnRequest->order->order_status }}
                            </p>

                        </div>

                        <div>

                            <p class="text-gray-500">
                                Trạng thái thanh toán
                            </p>

                            <p class="mt-1 font-medium text-gray-900">
                                {{ $returnRequest->order->payment_status }}
                            </p>

                        </div>

                        <div>

                            <p class="text-gray-500">
                                Tổng tiền
                            </p>

                            <p class="mt-1 font-bold text-pink-600">
                                {{ number_format(
                                    (float) $returnRequest->order->total_amount,
                                    0,
                                    ',',
                                    '.'
                                ) }}₫
                            </p>

                        </div>

                    </div>

                @else

                    <p class="mt-4 text-sm text-gray-500">
                        Không tìm thấy đơn hàng liên quan.
                    </p>

                @endif

            </div>

            {{-- Khách hàng --}}
            <div class="admin-card p-5">

                <h3 class="text-base font-semibold text-gray-900">
                    Khách hàng
                </h3>

                <div class="mt-4 space-y-4 text-sm">

                    <div>

                        <p class="text-gray-500">
                            Họ tên
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            {{ $returnRequest->user?->name
                                ?? $returnRequest->order?->customer_name
                                ?? 'Chưa có' }}
                        </p>

                    </div>

                    <div>

                        <p class="text-gray-500">
                            Email
                        </p>

                        <p class="mt-1 break-all font-medium text-gray-900">
                            {{ $returnRequest->user?->email
                                ?? $returnRequest->order?->customer_email
                                ?? 'Chưa có' }}
                        </p>

                    </div>

                    <div>

                        <p class="text-gray-500">
                            Số điện thoại
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            {{ $returnRequest->order?->customer_phone
                                ?? 'Chưa cập nhật' }}
                        </p>

                    </div>

                </div>

            </div>

            {{-- Thông tin xử lý --}}
            <div class="admin-card p-5">

                <h3 class="text-base font-semibold text-gray-900">
                    Thông tin xử lý
                </h3>

                <div class="mt-4 space-y-4 text-sm">

                    <div class="flex justify-between gap-4">

                        <span class="text-gray-500">
                            Người xử lý
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ $returnRequest->processor?->name
                                ?? 'Chưa phân công' }}
                        </span>

                    </div>

                    <div class="flex justify-between gap-4">

                        <span class="text-gray-500">
                            Người trả phí gửi
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ match ($returnRequest->shipping_fee_payer) {
                                'customer' => 'Khách hàng',
                                'shop' => 'Cửa hàng',
                                default => $returnRequest->shipping_fee_payer
                                    ?: 'Chưa xác định',
                            } }}
                        </span>

                    </div>

                    <div class="flex justify-between gap-4">

                        <span class="text-gray-500">
                            Đã duyệt lúc
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ $returnRequest->approved_at?->format(
                                'H:i d/m/Y'
                            ) ?? 'Chưa có' }}
                        </span>

                    </div>

                    <div class="flex justify-between gap-4">

                        <span class="text-gray-500">
                            Đã nhận hàng lúc
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ $returnRequest->received_at?->format(
                                'H:i d/m/Y'
                            ) ?? 'Chưa có' }}
                        </span>

                    </div>

                    <div class="flex justify-between gap-4">

                        <span class="text-gray-500">
                            Hoàn tất lúc
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ $returnRequest->completed_at?->format(
                                'H:i d/m/Y'
                            ) ?? 'Chưa có' }}
                        </span>

                    </div>

                    <div class="flex justify-between gap-4">

                        <span class="text-gray-500">
                            Từ chối lúc
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ $returnRequest->rejected_at?->format(
                                'H:i d/m/Y'
                            ) ?? 'Chưa có' }}
                        </span>

                    </div>

                    <div class="flex justify-between gap-4">

                        <span class="text-gray-500">
                            Hủy lúc
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ $returnRequest->cancelled_at?->format(
                                'H:i d/m/Y'
                            ) ?? 'Chưa có' }}
                        </span>

                    </div>

                </div>

            </div>

           <div class="rounded-2xl border border-gray-200 bg-white">

    <div class="border-b border-gray-200 px-6 py-5">
        <h3 class="text-xl font-bold">Xử lý yêu cầu</h3>
        <p class="mt-1 text-sm text-gray-500">
            Cập nhật trạng thái và thông tin xử lý yêu cầu trả hàng.
        </p>
    </div>

    <div class="p-6">
        @if (count($nextReturnStatuses))
            <form
                action="{{ route('admin.return-requests.update-status', $returnRequest) }}"
                method="POST"
                class="space-y-5"
            >
                @csrf
                @method('PATCH')

                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <p class="font-semibold">Vui lòng kiểm tra lại dữ liệu:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="mb-2 block text-sm font-semibold">Trạng thái hiện tại</label>
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                        <div class="text-sm text-blue-600">Hiện tại</div>
                        <div class="mt-1 text-xl font-bold text-blue-700">
                            {{ $statuses[$returnRequest->status] ?? $returnRequest->status }}
                        </div>
                    </div>
                </div>

                <div>
                    <label for="return-status" class="mb-2 block text-sm font-semibold">
                        Trạng thái tiếp theo
                    </label>
                    <select
                        name="status"
                        id="return-status"
                        class="w-full rounded-xl border-gray-300"
                        required
                    >
                        @foreach ($nextReturnStatuses as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(old('status') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="approved-area" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-semibold">Số tiền được duyệt</label>
                        <input
                            type="number"
                            min="0"
                            step="0.01"
                            name="approved_amount"
                            value="{{ old('approved_amount', $returnRequest->approved_amount ?? $returnRequest->requested_amount) }}"
                            class="w-full rounded-xl border-gray-300"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold">Phí gửi trả</label>
                        <input
                            type="number"
                            min="0"
                            step="0.01"
                            name="return_shipping_fee"
                            value="{{ old('return_shipping_fee', $returnRequest->return_shipping_fee ?? 0) }}"
                            class="w-full rounded-xl border-gray-300"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold">Bên chịu phí gửi trả</label>
                        <select name="shipping_fee_payer" class="w-full rounded-xl border-gray-300">
                            <option value="">Chưa xác định</option>
                            @foreach ($shippingFeePayers as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(old('shipping_fee_payer', $returnRequest->shipping_fee_payer) === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="inspection-area" class="space-y-4">
                    <div class="rounded-xl border border-orange-200 bg-orange-50 p-4 text-sm text-orange-800">
                        Nhập kết quả kiểm tra cho từng sản phẩm trước khi chuyển yêu cầu sang bước xử lý.
                    </div>

                    @foreach ($returnRequest->items as $returnItem)
                        @php($orderItem = $returnItem->orderItem)
                        <div class="rounded-xl border border-gray-200 p-4">
                            <p class="font-semibold text-gray-900">
                                {{ $orderItem?->product_name ?? 'Sản phẩm không còn tồn tại' }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                SKU: {{ $orderItem?->sku_code ?? 'Không có' }} · Số lượng trả: {{ number_format($returnItem->quantity) }}
                            </p>

                            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium">Tình trạng sản phẩm</label>
                                    <select
                                        name="items[{{ $returnItem->id }}][product_condition]"
                                        class="w-full rounded-xl border-gray-300"
                                    >
                                        <option value="">Chọn tình trạng</option>
                                        @foreach ($productConditions as $value => $label)
                                            <option
                                                value="{{ $value }}"
                                                @selected(old("items.{$returnItem->id}.product_condition", $returnItem->product_condition) === $value)
                                            >
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Kết quả kiểm tra</label>
                                    <select
                                        name="items[{{ $returnItem->id }}][inspection_result]"
                                        class="w-full rounded-xl border-gray-300"
                                    >
                                        <option value="">Chọn kết quả</option>
                                        @foreach ($inspectionResults as $value => $label)
                                            <option
                                                value="{{ $value }}"
                                                @selected(old("items.{$returnItem->id}.inspection_result", $returnItem->inspection_result) === $value)
                                            >
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Hướng xử lý tồn kho</label>
                                    <select
                                        name="items[{{ $returnItem->id }}][inventory_action]"
                                        class="w-full rounded-xl border-gray-300"
                                    >
                                        <option value="">Chọn hướng xử lý</option>
                                        @foreach ($inventoryActions as $value => $label)
                                            <option
                                                value="{{ $value }}"
                                                @selected(old("items.{$returnItem->id}.inventory_action", $returnItem->inventory_action) === $value)
                                            >
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Tiền hoàn cho sản phẩm</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        name="items[{{ $returnItem->id }}][approved_refund_amount]"
                                        value="{{ old("items.{$returnItem->id}.approved_refund_amount", $returnItem->approved_refund_amount) }}"
                                        class="w-full rounded-xl border-gray-300"
                                    >
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="mb-2 block text-sm font-medium">Ghi chú kiểm tra</label>
                                <textarea
                                    name="items[{{ $returnItem->id }}][inspection_note]"
                                    rows="3"
                                    class="w-full rounded-xl border-gray-300"
                                >{{ old("items.{$returnItem->id}.inspection_note", $returnItem->inspection_note) }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div id="reject-area" class="hidden">
                    <label class="mb-2 block text-sm font-semibold">Lý do từ chối</label>
                    <textarea
                        name="rejection_reason"
                        rows="4"
                        class="w-full rounded-xl border-gray-300"
                    >{{ old('rejection_reason', $returnRequest->rejection_reason) }}</textarea>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold">Ghi chú</label>
                    <textarea
                        name="note"
                        rows="5"
                        class="w-full rounded-xl border-gray-300"
                        placeholder="Ghi chú xử lý..."
                    >{{ old('note') }}</textarea>
                </div>

                <button class="w-full rounded-xl bg-pink-600 py-3 font-semibold text-white hover:bg-pink-700">
                    Cập nhật trạng thái
                </button>
            </form>
        @else
            <div class="rounded-xl border bg-gray-50 p-5 text-gray-600">
                Không còn bước xử lý tiếp theo.
            </div>
        @endif
    </div>
</div>
        </div>

    </div>
    @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const status = document.getElementById('return-status');
    if (!status) return;

    const approved = document.getElementById('approved-area');
    const inspection = document.getElementById('inspection-area');
    const rejected = document.getElementById('reject-area');

    function render() {
        approved.classList.toggle('hidden', status.value !== 'approved');
        inspection.classList.toggle('hidden', status.value !== 'inspecting');
        rejected.classList.toggle('hidden', status.value !== 'rejected');
    }

    render();
    status.addEventListener('change', render);
});
</script>
@endpush
@endsection
