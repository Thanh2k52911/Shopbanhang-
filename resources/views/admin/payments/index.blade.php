@extends('admin.layouts.master')

@section('title', 'Quản lý thanh toán')

@section('page-title', 'Quản lý thanh toán')

@section('page-description', 'Theo dõi toàn bộ giao dịch thanh toán của khách hàng.')

@section('content')

    @php
        $paymentStatuses = [
            'pending' => 'Chờ thanh toán',
            'processing' => 'Đang xử lý',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền',
            'partially_refunded' => 'Hoàn tiền một phần',
        ];

        $paymentMethods = [
            'cod' => 'Thanh toán khi nhận hàng',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'vnpay' => 'VNPay',
            'momo' => 'MoMo',
            'zalopay' => 'ZaloPay',
        ];
    @endphp

    {{-- Thống kê nhanh --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Tổng thanh toán
            </p>

            <p class="mt-2 text-2xl font-bold text-gray-900">
                {{ number_format($statistics['total']) }}
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Chờ thanh toán
            </p>

            <p class="mt-2 text-2xl font-bold text-amber-600">
                {{ number_format($statistics['pending']) }}
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Đang xử lý
            </p>

            <p class="mt-2 text-2xl font-bold text-blue-600">
                {{ number_format($statistics['processing'] ?? 0) }}
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Đã thanh toán
            </p>

            <p class="mt-2 text-2xl font-bold text-green-600">
                {{ number_format($statistics['paid']) }}
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Thất bại
            </p>

            <p class="mt-2 text-2xl font-bold text-red-600">
                {{ number_format($statistics['failed']) }}
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Đã hủy
            </p>

            <p class="mt-2 text-2xl font-bold text-gray-600">
                {{ number_format($statistics['cancelled']) }}
            </p>
        </div>

    </div>

    {{-- Bộ lọc --}}
    <div class="admin-card mt-6 p-5">

        <div class="mb-5">
            <h2 class="text-base font-semibold text-gray-900">
                Tìm kiếm và lọc thanh toán
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Tìm theo mã thanh toán, mã giao dịch, mã đơn hàng hoặc khách hàng.
            </p>
        </div>

        <form
            action="{{ route('admin.payments.index') }}"
            method="GET"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4"
        >

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
                    placeholder="Mã thanh toán, đơn hàng..."
                >
            </div>

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

                    @foreach ($paymentStatuses as $status => $label)
                        <option
                            value="{{ $status }}"
                            @selected(request('status') === $status)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label
                    for="method"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Phương thức
                </label>

                <select
                    id="method"
                    name="method"
                    class="admin-select"
                >
                    <option value="">
                        Tất cả phương thức
                    </option>

                    @foreach ($paymentMethods as $method => $label)
                        <option
                            value="{{ $method }}"
                            @selected(request('method') === $method)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-3">

                <button
                    type="submit"
                    class="admin-btn admin-btn-primary"
                >
                    Áp dụng
                </button>

                <a
                    href="{{ route('admin.payments.index') }}"
                    class="admin-btn admin-btn-secondary"
                >
                    Xóa lọc
                </a>

            </div>

        </form>

    </div>

    {{-- Danh sách thanh toán --}}
    <div class="admin-card mt-6 overflow-hidden">

        <div
            class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-base font-semibold text-gray-900">
                    Danh sách thanh toán
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Tìm thấy {{ number_format($payments->total()) }} bản ghi.
                </p>
            </div>

            <p class="text-sm text-gray-500">
                Trang {{ $payments->currentPage() }}/{{ $payments->lastPage() }}
            </p>
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Mã thanh toán
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Đơn hàng
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Khách hàng
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Phương thức
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Số tiền
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Trạng thái
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Thời gian
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Thao tác
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">

                    @forelse ($payments as $payment)

                        @php
                            $statusClass = match ($payment->status) {
                                'pending' => 'bg-amber-100 text-amber-700',
                                'processing' => 'bg-blue-100 text-blue-700',
                                'paid' => 'bg-green-100 text-green-700',
                                'failed' => 'bg-red-100 text-red-700',
                                'cancelled' => 'bg-gray-200 text-gray-700',
                                'refunded' => 'bg-blue-100 text-blue-700',
                                'partially_refunded' => 'bg-cyan-100 text-cyan-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp

                        <tr class="transition hover:bg-gray-50">

                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="font-semibold text-gray-900">
                                    {{ $payment->payment_code }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    ID: {{ $payment->id }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                @if ($payment->order)
                                    <a
                                        href="{{ route('admin.orders.show', $payment->order) }}"
                                        class="font-medium text-gray-900 hover:text-pink-600"
                                    >
                                        {{ $payment->order->order_code }}
                                    </a>
                                @else
                                    <span class="text-sm text-gray-500">
                                        Không tìm thấy đơn
                                    </span>
                                @endif
                            </td>

                            <td class="min-w-[180px] px-5 py-4">
                                <p class="font-medium text-gray-900">
                                    {{ $payment->order?->customer_name ?? 'Chưa có' }}
                                </p>
                            </td>

                            <td class="min-w-[180px] px-5 py-4">
                                <p class="font-medium text-gray-900">
                                    {{ $paymentMethods[$payment->method]
                                        ?? $payment->method }}
                                </p>

                                @if ($payment->bank_code)
                                    <p class="mt-1 text-xs text-gray-500">
                                        Ngân hàng: {{ $payment->bank_code }}
                                    </p>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="font-bold text-gray-900">
                                    {{ number_format(
                                        (float) $payment->amount,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                    {{ $payment->currency ?? 'VND' }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="admin-badge {{ $statusClass }}">
                                    {{ $paymentStatuses[$payment->status]
                                        ?? $payment->status }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                @if ($payment->paid_at)
                                    <p class="text-sm font-medium text-gray-700">
                                        {{ $payment->paid_at->format('d/m/Y') }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $payment->paid_at->format('H:i') }}
                                    </p>
                                @else
                                    <p class="text-sm font-medium text-gray-700">
                                        {{ $payment->created_at?->format('d/m/Y') }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $payment->created_at?->format('H:i') }}
                                    </p>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="flex justify-end gap-2">

    <a
        href="{{ route('admin.payments.show', $payment) }}"
        class="admin-btn admin-btn-primary"
    >
        Chi tiết
    </a>

    @if ($payment->order)
        <a
            href="{{ route('admin.orders.show', $payment->order) }}"
            class="admin-btn admin-btn-secondary"
        >
            Xem đơn
        </a>
    @endif

</div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="8"
                                class="px-5 py-12 text-center"
                            >
                                <p class="font-medium text-gray-700">
                                    Chưa có bản ghi thanh toán
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    Các thanh toán phát sinh sẽ hiển thị tại đây.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if ($payments->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $payments->links() }}
            </div>
        @endif

    </div>

@endsection
