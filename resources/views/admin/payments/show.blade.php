@extends('admin.layouts.master')

@section('title', 'Chi tiết thanh toán ' . $payment->payment_code)

@section('page-title', 'Chi tiết thanh toán')

@section('page-description', 'Theo dõi thông tin thanh toán, giao dịch và hoàn tiền liên quan.')

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

        $statusBadgeClass = match ($payment->status) {
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

    {{-- Thanh điều hướng --}}
    <div
        class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="flex items-center gap-3">

            <a
                href="{{ route('admin.payments.index') }}"
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
                        {{ $payment->payment_code }}
                    </h2>

                    <span class="admin-badge {{ $statusBadgeClass }}">
                        {{ $paymentStatuses[$payment->status] ?? $payment->status }}
                    </span>

                </div>

                <p class="mt-1 text-sm text-gray-500">
                    Tạo lúc {{ $payment->created_at?->format('H:i d/m/Y') }}
                </p>
            </div>

        </div>

        <div class="flex flex-wrap gap-3">

            @if ($payment->order)
                <a
                    href="{{ route('admin.orders.show', $payment->order) }}"
                    class="admin-btn admin-btn-secondary"
                >
                    Xem đơn hàng
                </a>
            @endif

            <button
                type="button"
                class="admin-btn admin-btn-secondary"
                data-copy-text="{{ $payment->payment_code }}"
            >
                Sao chép mã thanh toán
            </button>

        </div>
    </div>

    {{-- Tổng quan --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Trạng thái
            </p>

            <p class="mt-3 text-lg font-bold text-gray-900">
                {{ $paymentStatuses[$payment->status] ?? $payment->status }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Cập nhật lúc {{ $payment->updated_at?->format('H:i d/m/Y') }}
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Phương thức
            </p>

            <p class="mt-3 text-lg font-bold text-gray-900">
                {{ $paymentMethods[$payment->method] ?? $payment->method }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                {{ $payment->bank_code ?: 'Không có thông tin ngân hàng' }}
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Số tiền
            </p>

            <p class="mt-3 text-lg font-bold text-pink-600">
                {{ number_format((float) $payment->amount, 0, ',', '.') }}
                {{ $payment->currency ?? 'VND' }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Mã giao dịch:
                {{ $payment->provider_transaction_id ?: 'Chưa có' }}
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Thanh toán lúc
            </p>

            <p class="mt-3 text-lg font-bold text-gray-900">
                {{ $payment->paid_at?->format('H:i d/m/Y')
                    ?? 'Chưa thanh toán' }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Hết hạn:
                {{ $payment->expired_at?->format('H:i d/m/Y')
                    ?? 'Không giới hạn' }}
            </p>
        </div>

    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Cột trái --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- Thông tin thanh toán --}}
            <div class="admin-card overflow-hidden">

                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Thông tin thanh toán
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Chi tiết bản ghi Payment trong hệ thống.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2">

                    <div>
                        <p class="text-sm text-gray-500">
                            Mã thanh toán
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            {{ $payment->payment_code }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Mã giao dịch nhà cung cấp
                        </p>

                        <p class="mt-1 break-all font-medium text-gray-900">
                            {{ $payment->provider_transaction_id ?: 'Chưa có' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Phương thức
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            {{ $paymentMethods[$payment->method]
                                ?? $payment->method }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Loại thẻ
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            {{ $payment->card_type ?: 'Chưa có' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Mã ngân hàng
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            {{ $payment->bank_code ?: 'Chưa có' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Loại tiền
                        </p>

                        <p class="mt-1 font-medium text-gray-900">
                            {{ $payment->currency ?? 'VND' }}
                        </p>
                    </div>

                    @if ($payment->payment_url)
                        <div class="sm:col-span-2">
                            <p class="text-sm text-gray-500">
                                Đường dẫn thanh toán
                            </p>

                            <p class="mt-1 break-all text-sm font-medium text-blue-600">
                                {{ $payment->payment_url }}
                            </p>
                        </div>
                    @endif

                    @if ($payment->failure_reason)
                        <div class="sm:col-span-2">

                            <p class="text-sm text-gray-500">
                                Lý do thất bại
                            </p>

                            <div
                                class="mt-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm leading-6 text-red-700"
                            >
                                {{ $payment->failure_reason }}
                            </div>

                        </div>
                    @endif

                </div>

            </div>

            {{-- Giao dịch thanh toán --}}
            <div class="admin-card overflow-hidden">

                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Lịch sử giao dịch
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Các lần khởi tạo, xác nhận, thất bại hoặc hoàn tiền.
                    </p>
                </div>

                <div class="divide-y divide-gray-200">

                    @forelse (
                        $payment->transactions->sortByDesc('processed_at')
                        as $transaction
                    )

                        @php
                            $transactionStatusClass = match ($transaction->status) {
                                'success', 'completed' =>
                                    'bg-green-100 text-green-700',

                                'pending' =>
                                    'bg-amber-100 text-amber-700',

                                'failed' =>
                                    'bg-red-100 text-red-700',

                                'cancelled' =>
                                    'bg-gray-200 text-gray-700',

                                default =>
                                    'bg-gray-100 text-gray-700',
                            };
                        @endphp

                        <div class="p-5">

                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                            >

                                <div>
                                    <div class="flex flex-wrap items-center gap-2">

                                        <p class="font-semibold text-gray-900">
                                            {{ $transaction->transaction_id
                                                ?: 'Giao dịch #' . $transaction->id }}
                                        </p>

                                        <span class="admin-badge {{ $transactionStatusClass }}">
                                            {{ $transaction->status }}
                                        </span>

                                    </div>

                                    <p class="mt-2 text-sm text-gray-500">
                                        Loại giao dịch:
                                        <span class="font-medium text-gray-700">
                                            {{ $transaction->type }}
                                        </span>
                                    </p>
                                </div>

                                <div class="shrink-0 text-left sm:text-right">

                                    <p class="font-bold text-gray-900">
                                        {{ number_format(
                                            (float) $transaction->amount,
                                            0,
                                            ',',
                                            '.'
                                        ) }}₫
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $transaction->processed_at?->format('H:i d/m/Y')
                                            ?? $transaction->created_at?->format('H:i d/m/Y') }}
                                    </p>

                                </div>

                            </div>

                            <div
                                class="mt-4 grid grid-cols-1 gap-3 text-sm text-gray-600 sm:grid-cols-2"
                            >

                                <div>
                                    <span class="text-gray-500">
                                        Mã phản hồi:
                                    </span>

                                    <span class="font-medium text-gray-700">
                                        {{ $transaction->response_code ?: 'Chưa có' }}
                                    </span>
                                </div>

                                <div>
                                    <span class="text-gray-500">
                                        Địa chỉ IP:
                                    </span>

                                    <span class="font-medium text-gray-700">
                                        {{ $transaction->ip_address ?: 'Chưa có' }}
                                    </span>
                                </div>

                                @if ($transaction->message)
                                    <div class="sm:col-span-2">
                                        <span class="text-gray-500">
                                            Nội dung:
                                        </span>

                                        <span class="font-medium text-gray-700">
                                            {{ $transaction->message }}
                                        </span>
                                    </div>
                                @endif

                            </div>

                        </div>

                    @empty

                        <div class="p-10 text-center">

                            <p class="font-medium text-gray-700">
                                Chưa có giao dịch thanh toán
                            </p>

                            <p class="mt-1 text-sm text-gray-500">
                                Các giao dịch liên quan sẽ hiển thị tại đây.
                            </p>

                        </div>

                    @endforelse

                </div>

            </div>

            {{-- Hoàn tiền --}}
<div
    class="admin-card overflow-hidden"
    x-data="{
        refundOpen: {{ $errors->hasAny([
            'amount',
            'method',
            'bank_name',
            'bank_account_number',
            'bank_account_name',
            'reason',
            'admin_note',
            'refund',
        ]) ? 'true' : 'false' }},

        refundMethod: @js(old('method', 'original_payment'))
    }"
>

    <div
        class="flex flex-col gap-4 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <div>
            <h3 class="text-base font-semibold text-gray-900">
                Hoàn tiền
            </h3>

            <p class="mt-1 text-sm text-gray-500">
                Theo dõi và tạo yêu cầu hoàn tiền cho Payment.
            </p>
        </div>

        @if (
            $payment->canBeRefunded()
            && $remainingRefundAmount > 0
        )
            <button
                type="button"
                class="admin-btn admin-btn-primary"
                @click="refundOpen = !refundOpen"
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
                        d="M12 6v12m6-6H6"
                    />
                </svg>

                Tạo yêu cầu hoàn tiền
            </button>
        @endif
    </div>

    {{-- Tổng quan hoàn tiền --}}
    <div class="grid grid-cols-1 gap-4 border-b border-gray-200 p-5 sm:grid-cols-3">

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="text-sm text-gray-500">
                Số tiền Payment
            </p>

            <p class="mt-2 text-lg font-bold text-gray-900">
                {{ number_format(
                    (float) $payment->amount,
                    0,
                    ',',
                    '.'
                ) }}₫
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="text-sm text-gray-500">
                Đã hoàn thành
            </p>

            <p class="mt-2 text-lg font-bold text-blue-600">
                {{ number_format(
                    (float) $payment->refunded_amount,
                    0,
                    ',',
                    '.'
                ) }}₫
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="text-sm text-gray-500">
                Còn có thể tạo yêu cầu
            </p>

            <p class="mt-2 text-lg font-bold text-green-600">
                {{ number_format(
                    (float) $remainingRefundAmount,
                    0,
                    ',',
                    '.'
                ) }}₫
            </p>
        </div>

    </div>

    {{-- Lỗi nghiệp vụ chung --}}
    @if ($errors->has('refund'))
        <div
            class="mx-5 mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        >
            {{ $errors->first('refund') }}
        </div>
    @endif

    {{-- Form tạo Refund --}}
    @if (
        $payment->canBeRefunded()
        && $remainingRefundAmount > 0
    )
        <div
            x-show="refundOpen"
            x-cloak
            class="border-b border-gray-200 bg-gray-50 p-5"
        >
            <form
                action="{{ route(
                    'admin.payments.refunds.store',
                    $payment
                ) }}"
                method="POST"
                data-confirm="Bạn có chắc muốn tạo yêu cầu hoàn tiền này?"
            >
                @csrf

                <div class="mb-5">

                    <div class="flex items-start justify-between gap-4">

                        <div>
                            <h4 class="font-semibold text-gray-900">
                                Tạo yêu cầu hoàn tiền
                            </h4>

                            <p class="mt-1 text-sm text-gray-500">
                                Yêu cầu mới sẽ được tạo ở trạng thái chờ xử lý.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="text-gray-400 transition hover:text-gray-700"
                            @click="refundOpen = false"
                            title="Đóng form"
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
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>

                    </div>

                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- Số tiền hoàn --}}
                    <div>
                        <label
                            for="refund_amount"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Số tiền hoàn
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="refund_amount"
                            type="number"
                            name="amount"
                            value="{{ old(
                                'amount',
                                $remainingRefundAmount
                            ) }}"
                            min="1"
                            max="{{ $remainingRefundAmount }}"
                            step="1"
                            class="admin-input"
                            required
                        >

                        <p class="mt-2 text-xs text-gray-500">
                            Tối đa:
                            {{ number_format(
                                (float) $remainingRefundAmount,
                                0,
                                ',',
                                '.'
                            ) }}₫
                        </p>

                        @error('amount')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Phương thức hoàn --}}
                    <div>
                        <label
                            for="refund_method"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Phương thức hoàn tiền
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="refund_method"
                            name="method"
                            class="admin-select"
                            x-model="refundMethod"
                            required
                        >
                            @foreach ($refundMethods as $method => $label)
                                <option
                                    value="{{ $method }}"
                                    @selected(old('method') === $method)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        @error('method')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Thông tin tài khoản --}}
                    <div
                        x-show="refundMethod === 'bank_transfer'"
                        x-cloak
                        class="md:col-span-2"
                    >
                        <div
                            class="grid grid-cols-1 gap-5 rounded-xl border border-blue-200 bg-blue-50 p-4 md:grid-cols-3"
                        >

                            <div>
                                <label
                                    for="bank_name"
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    Ngân hàng
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="bank_name"
                                    type="text"
                                    name="bank_name"
                                    value="{{ old('bank_name') }}"
                                    class="admin-input"
                                    placeholder="Ví dụ: Vietcombank"
                                    :required="refundMethod === 'bank_transfer'"
                                >

                                @error('bank_name')
                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="bank_account_number"
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    Số tài khoản
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="bank_account_number"
                                    type="text"
                                    name="bank_account_number"
                                    value="{{ old('bank_account_number') }}"
                                    class="admin-input"
                                    placeholder="Nhập số tài khoản"
                                    :required="refundMethod === 'bank_transfer'"
                                >

                                @error('bank_account_number')
                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="bank_account_name"
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    Chủ tài khoản
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="bank_account_name"
                                    type="text"
                                    name="bank_account_name"
                                    value="{{ old('bank_account_name') }}"
                                    class="admin-input uppercase"
                                    placeholder="NGUYEN VAN A"
                                    :required="refundMethod === 'bank_transfer'"
                                >

                                @error('bank_account_name')
                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- Lý do --}}
                    <div class="md:col-span-2">
                        <label
                            for="refund_reason"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Lý do hoàn tiền
                            <span class="text-red-500">*</span>
                        </label>

                        <textarea
                            id="refund_reason"
                            name="reason"
                            rows="4"
                            class="admin-textarea"
                            placeholder="Ví dụ: Khách hàng trả sản phẩm và yêu cầu hoàn tiền..."
                            required
                        >{{ old('reason') }}</textarea>

                        @error('reason')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Ghi chú nội bộ --}}
                    <div class="md:col-span-2">
                        <label
                            for="refund_admin_note"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Ghi chú nội bộ
                        </label>

                        <textarea
                            id="refund_admin_note"
                            name="admin_note"
                            rows="3"
                            class="admin-textarea"
                            placeholder="Thông tin chỉ dành cho nhân viên quản trị..."
                        >{{ old('admin_note') }}</textarea>

                        @error('admin_note')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

                <div class="mt-5 flex flex-col gap-3 sm:flex-row">

                    <button
                        type="submit"
                        class="admin-btn admin-btn-primary"
                        data-loading-text="Đang tạo yêu cầu..."
                    >
                        Tạo yêu cầu hoàn tiền
                    </button>

                    <button
                        type="button"
                        class="admin-btn admin-btn-secondary"
                        @click="refundOpen = false"
                    >
                        Đóng
                    </button>

                </div>

            </form>
        </div>
    @endif

    {{-- Danh sách Refund --}}
    <div class="divide-y divide-gray-200">

        @forelse (
            $payment->refunds->sortByDesc('id')
            as $refund
        )

            @php
                $refundStatusLabels = [
                    'pending' => 'Chờ xử lý',
                    'processing' => 'Đang xử lý',
                    'completed' => 'Đã hoàn tiền',
                    'failed' => 'Hoàn tiền thất bại',
                    'cancelled' => 'Đã hủy',
                ];

                $refundMethodLabels = [
                    'original_payment' => 'Phương thức thanh toán gốc',
                    'bank_transfer' => 'Chuyển khoản ngân hàng',
                    'cash' => 'Tiền mặt',
                    'store_credit' => 'Ví cửa hàng',
                    'coupon' => 'Mã giảm giá',
                ];

                $refundStatusClass = match ($refund->status) {
                    'pending' => 'bg-amber-100 text-amber-700',
                    'processing' => 'bg-blue-100 text-blue-700',
                    'completed' => 'bg-green-100 text-green-700',
                    'failed' => 'bg-red-100 text-red-700',
                    'cancelled' => 'bg-gray-200 text-gray-700',
                    default => 'bg-gray-100 text-gray-700',
                };
            @endphp

            <div class="p-5">

                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                >
                    <div>

                        <div class="flex flex-wrap items-center gap-2">

                            <p class="font-semibold text-gray-900">
                                {{ $refund->refund_code }}
                            </p>

                            <span class="admin-badge {{ $refundStatusClass }}">
                                {{ $refundStatusLabels[$refund->status]
                                    ?? $refund->status }}
                            </span>

                        </div>

                        <p class="mt-2 text-sm text-gray-500">
                            {{ $refundMethodLabels[$refund->method]
                                ?? $refund->method }}
                        </p>

                        <p class="mt-2 text-sm leading-6 text-gray-700">
                            {{ $refund->reason }}
                        </p>

                    </div>

                    <div class="shrink-0 text-left sm:text-right">

                        <p class="text-lg font-bold text-pink-600">
                            {{ number_format(
                                (float) $refund->amount,
                                0,
                                ',',
                                '.'
                            ) }}₫
                        </p>

                        <p class="mt-1 text-xs text-gray-500">
                            {{ $refund->created_at?->format('H:i d/m/Y') }}
                        </p>

                    </div>
                </div>

                @if ($refund->method === 'bank_transfer')
                    <div
                        class="mt-4 grid grid-cols-1 gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm sm:grid-cols-3"
                    >
                        <div>
                            <p class="text-gray-500">
                                Ngân hàng
                            </p>

                            <p class="mt-1 font-medium text-gray-900">
                                {{ $refund->bank_name ?: 'Chưa có' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500">
                                Số tài khoản
                            </p>

                            <p class="mt-1 font-medium text-gray-900">
                                {{ $refund->bank_account_number ?: 'Chưa có' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500">
                                Chủ tài khoản
                            </p>

                            <p class="mt-1 font-medium text-gray-900">
                                {{ $refund->bank_account_name ?: 'Chưa có' }}
                            </p>
                        </div>
                    </div>
                @endif

                @if ($refund->admin_note)
                    <div
                        class="mt-4 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700"
                    >
                        <span class="font-medium">
                            Ghi chú nội bộ:
                        </span>

                        {{ $refund->admin_note }}
                    </div>
                @endif


                @php
                    $nextRefundStatuses = $refundTransitions[$refund->id] ?? [];
                @endphp

                @if (! empty($nextRefundStatuses))
                    <div
                        class="mt-5 border-t border-gray-200 pt-5"
                        x-data="{
                            refundStatus: @js(
                                old(
                                    'refund_status_' . $refund->id,
                                    array_key_first($nextRefundStatuses)
                                )
                            )
                        }"
                    >
                        <h4 class="font-semibold text-gray-900">
                            Xử lý yêu cầu hoàn tiền
                        </h4>

                        <p class="mt-1 text-sm text-gray-500">
                            Chuyển yêu cầu sang bước xử lý tiếp theo.
                        </p>

                        <form
                            action="{{ route(
                                'admin.payments.refunds.update-status',
                                [$payment, $refund]
                            ) }}"
                            method="POST"
                            class="mt-4 space-y-4"
                            data-confirm="Bạn có chắc muốn cập nhật trạng thái hoàn tiền này?"
                        >
                            @csrf
                            @method('PATCH')

                            <div>
                                <label
                                    for="refund_status_{{ $refund->id }}"
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    Trạng thái tiếp theo
                                </label>

                                <select
                                    id="refund_status_{{ $refund->id }}"
                                    name="status"
                                    class="admin-select"
                                    x-model="refundStatus"
                                    required
                                >
                                    @foreach ($nextRefundStatuses as $value => $label)
                                        <option value="{{ $value }}">
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

                            <div>
                                <label
                                    for="refund_provider_transaction_id_{{ $refund->id }}"
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    Mã giao dịch hoàn tiền
                                </label>

                                <input
                                    id="refund_provider_transaction_id_{{ $refund->id }}"
                                    type="text"
                                    name="provider_transaction_id"
                                    value="{{ old('provider_transaction_id', $refund->provider_transaction_id) }}"
                                    class="admin-input"
                                    placeholder="Ví dụ: REFUND123456789"
                                >

                                @error('provider_transaction_id')
                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div
                                x-show="refundStatus === 'failed'"
                                x-cloak
                            >
                                <label
                                    for="refund_failure_reason_{{ $refund->id }}"
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    Lý do hoàn tiền thất bại
                                    <span class="text-red-500">*</span>
                                </label>

                                <textarea
                                    id="refund_failure_reason_{{ $refund->id }}"
                                    name="failure_reason"
                                    rows="3"
                                    class="admin-textarea"
                                    placeholder="Nhập rõ lý do xử lý hoàn tiền thất bại..."
                                    :required="refundStatus === 'failed'"
                                >{{ old('failure_reason') }}</textarea>

                                @error('failure_reason')
                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="refund_status_note_{{ $refund->id }}"
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    Ghi chú nội bộ
                                </label>

                                <textarea
                                    id="refund_status_note_{{ $refund->id }}"
                                    name="admin_note"
                                    rows="3"
                                    class="admin-textarea"
                                    placeholder="Ví dụ: Đã xác minh thông tin và bắt đầu xử lý hoàn tiền..."
                                >{{ old('admin_note') }}</textarea>

                                @error('admin_note')
                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="space-y-3">
                                <div
                                    x-show="refundStatus === 'processing'"
                                    x-cloak
                                    class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700"
                                >
                                    Yêu cầu sẽ chuyển sang đang xử lý và ghi nhận người xử lý hiện tại.
                                </div>

                                <div
                                    x-show="refundStatus === 'completed'"
                                    x-cloak
                                    class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"
                                >
                                    Khi hoàn tất, hệ thống sẽ tự đồng bộ Payment thành hoàn một phần hoặc hoàn toàn bộ.
                                </div>

                                <div
                                    x-show="refundStatus === 'failed'"
                                    x-cloak
                                    class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                                >
                                    Số tiền của yêu cầu thất bại sẽ được giải phóng để có thể tạo yêu cầu hoàn mới.
                                </div>

                                <div
                                    x-show="refundStatus === 'cancelled'"
                                    x-cloak
                                    class="rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-700"
                                >
                                    Hủy yêu cầu không làm thay đổi trạng thái Payment đã thanh toán.
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="admin-btn admin-btn-primary"
                                data-loading-text="Đang cập nhật..."
                            >
                                Cập nhật Refund
                            </button>
                        </form>
                    </div>
                @else
                    <div
                        class="mt-5 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600"
                    >
                        @switch($refund->status)
                            @case('completed')
                                Yêu cầu hoàn tiền đã hoàn tất.
                                @break

                            @case('failed')
                                Yêu cầu hoàn tiền đã xử lý thất bại.
                                @break

                            @case('cancelled')
                                Yêu cầu hoàn tiền đã bị hủy.
                                @break

                            @default
                                Không còn bước xử lý tiếp theo.
                        @endswitch
                    </div>
                @endif

            </div>

        @empty

            <div class="p-10 text-center">

                <p class="font-medium text-gray-700">
                    Chưa có hoàn tiền
                </p>

                <p class="mt-1 text-sm text-gray-500">
                    Payment này chưa phát sinh yêu cầu hoàn tiền.
                </p>

            </div>

        @endforelse

    </div>

</div>


        </div>

        {{-- Cột phải --}}
        <div class="space-y-6">

            {{-- Thông tin đơn --}}
            <div class="admin-card p-5">

                <h3 class="text-base font-semibold text-gray-900">
                    Đơn hàng
                </h3>

                @if ($payment->order)

                    <div class="mt-4 space-y-4 text-sm">

                        <div>
                            <p class="text-gray-500">
                                Mã đơn
                            </p>

                            <a
                                href="{{ route('admin.orders.show', $payment->order) }}"
                                class="mt-1 block font-semibold text-pink-600 hover:text-pink-700"
                            >
                                {{ $payment->order->order_code }}
                            </a>
                        </div>

                        <div>
                            <p class="text-gray-500">
                                Trạng thái đơn
                            </p>

                            <p class="mt-1 font-medium text-gray-900">
                                {{ $payment->order->order_status }}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500">
                                Trạng thái thanh toán
                            </p>

                            <p class="mt-1 font-medium text-gray-900">
                                {{ $payment->order->payment_status }}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500">
                                Tổng tiền đơn
                            </p>

                            <p class="mt-1 font-bold text-pink-600">
                                {{ number_format(
                                    (float) $payment->order->total_amount,
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

            {{-- Thông tin khách hàng --}}
            <div class="admin-card p-5">

                <h3 class="text-base font-semibold text-gray-900">
                    Khách hàng
                </h3>

                @if ($payment->order)

                    <div class="mt-4 space-y-4 text-sm">

                        <div>
                            <p class="text-gray-500">
                                Họ tên
                            </p>

                            <p class="mt-1 font-medium text-gray-900">
                                {{ $payment->order->user?->name
                                    ?? $payment->order->customer_name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500">
                                Email
                            </p>

                            <p class="mt-1 break-all font-medium text-gray-900">
                                {{ $payment->order->user?->email
                                    ?? $payment->order->customer_email }}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500">
                                Số điện thoại
                            </p>

                            <p class="mt-1 font-medium text-gray-900">
                                {{ $payment->order->customer_phone
                                    ?: 'Chưa cập nhật' }}
                            </p>
                        </div>

                    </div>

                @else

                    <p class="mt-4 text-sm text-gray-500">
                        Chưa có thông tin khách hàng.
                    </p>

                @endif

            </div>

            {{-- Các mốc thời gian --}}
            <div class="admin-card p-5">

                <h3 class="text-base font-semibold text-gray-900">
                    Các mốc thanh toán
                </h3>

                <div class="mt-4 space-y-4 text-sm">

                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">
                            Ngày tạo
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ $payment->created_at?->format('H:i d/m/Y') }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">
                            Thanh toán lúc
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ $payment->paid_at?->format('H:i d/m/Y')
                                ?? 'Chưa có' }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">
                            Hết hạn
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ $payment->expired_at?->format('H:i d/m/Y')
                                ?? 'Chưa có' }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">
                            Đã hủy lúc
                        </span>

                        <span class="text-right font-medium text-gray-900">
                            {{ $payment->cancelled_at?->format('H:i d/m/Y')
                                ?? 'Chưa có' }}
                        </span>
                    </div>

                </div>

            </div>

            {{-- Thao tác thanh toán --}}
<div class="admin-card p-5">

    <div>
        <h3 class="text-base font-semibold text-gray-900">
            Thao tác thanh toán
        </h3>

        <p class="mt-1 text-sm text-gray-500">
            Cập nhật trạng thái Payment và đồng bộ với đơn hàng.
        </p>
    </div>

    <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4">

        <p class="text-sm text-blue-600">
            Trạng thái hiện tại
        </p>

        <p class="mt-1 font-semibold text-blue-700">
            {{ $paymentStatuses[$payment->status] ?? $payment->status }}
        </p>

    </div>

    @if (! empty($nextPaymentStatuses))

        <form
            action="{{ route('admin.payments.update-status', $payment) }}"
            method="POST"
            class="mt-5"
            data-confirm="Bạn có chắc muốn cập nhật trạng thái thanh toán này?"
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
                    @foreach ($nextPaymentStatuses as $status => $label)
                        <option
                            value="{{ $status }}"
                            @selected(
                                old(
                                    'status',
                                    array_key_first($nextPaymentStatuses)
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

            {{-- Mã giao dịch nhà cung cấp --}}
            <div class="mt-4">

                <label
                    for="provider_transaction_id"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Mã giao dịch nhà cung cấp
                </label>

                <input
                    id="provider_transaction_id"
                    type="text"
                    name="provider_transaction_id"
                    value="{{ old(
                        'provider_transaction_id',
                        $payment->provider_transaction_id
                    ) }}"
                    class="admin-input"
                    placeholder="Ví dụ: VNPAY123456789"
                >

                @error('provider_transaction_id')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            {{-- Lý do thất bại --}}
            <div
                id="payment-failure-reason-wrapper"
                class="mt-4 hidden"
            >

                <label
                    for="failure_reason"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Lý do thanh toán thất bại
                    <span class="text-red-500">*</span>
                </label>

                <textarea
                    id="failure_reason"
                    name="failure_reason"
                    rows="4"
                    class="admin-textarea"
                    placeholder="Ví dụ: Giao dịch bị ngân hàng từ chối..."
                >{{ old('failure_reason') }}</textarea>

                @error('failure_reason')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            {{-- Ghi chú --}}
            <div class="mt-4">

                <label
                    for="note"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Ghi chú
                </label>

                <textarea
                    id="note"
                    name="note"
                    rows="4"
                    class="admin-textarea"
                    placeholder="Ghi chú nội bộ cho lần cập nhật này..."
                >{{ old('note') }}</textarea>

                @error('note')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            {{-- Cảnh báo --}}
            <div class="mt-4 space-y-3">

                <div
                    data-payment-status-warning="processing"
                    class="hidden rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700"
                >
                    Payment sẽ chuyển sang trạng thái đang xử lý và trạng thái
                    thanh toán của Order cũng được đồng bộ tương ứng.
                </div>

                <div
                    data-payment-status-warning="paid"
                    class="hidden rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"
                >
                    Khi xác nhận đã thanh toán, Payment và trạng thái thanh toán
                    của Order sẽ cùng chuyển sang đã thanh toán.
                </div>

                <div
                    data-payment-status-warning="failed"
                    class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                >
                    Vui lòng nhập rõ lý do giao dịch thất bại.
                </div>

                <div
                    data-payment-status-warning="cancelled"
                    class="hidden rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-700"
                >
                    Hủy Payment không tự hủy đơn hàng. Chỉ trạng thái thanh toán
                    của đơn được đồng bộ sang đã hủy.
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

                Cập nhật thanh toán
            </button>

        </form>

    @else

        <div
            class="mt-5 rounded-xl border border-gray-200 bg-gray-50 px-4 py-4 text-sm text-gray-600"
        >
            @switch($payment->status)

                @case('paid')
                    Payment đã thanh toán thành công, không còn thao tác trạng thái.
                    @break

                @case('failed')
                    Payment đã được đánh dấu thất bại.
                    @break

                @case('cancelled')
                    Payment đã bị hủy.
                    @break

                @case('refunded')
                    Payment đã được hoàn tiền toàn bộ.
                    @break

                @case('partially_refunded')
                    Payment đã được hoàn tiền một phần.
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
            const failureReasonWrapper = document.getElementById(
                'payment-failure-reason-wrapper'
            );
            const failureReasonInput = document.getElementById(
                'failure_reason'
            );
            const warningElements = document.querySelectorAll(
                '[data-payment-status-warning]'
            );

            if (!statusSelect) {
                return;
            }

            const updatePaymentForm = function () {
                const selectedStatus = statusSelect.value;
                const isFailed = selectedStatus === 'failed';

                if (failureReasonWrapper) {
                    failureReasonWrapper.classList.toggle(
                        'hidden',
                        !isFailed
                    );
                }

                if (failureReasonInput) {
                    failureReasonInput.required = isFailed;
                }

                warningElements.forEach(function (element) {
                    const targetStatus = element.getAttribute(
                        'data-payment-status-warning'
                    );

                    element.classList.toggle(
                        'hidden',
                        targetStatus !== selectedStatus
                    );
                });
            };

            statusSelect.addEventListener(
                'change',
                updatePaymentForm
            );

            updatePaymentForm();
        });
    </script>
@endpush
@endsection
