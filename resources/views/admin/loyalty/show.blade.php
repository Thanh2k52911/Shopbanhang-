@extends('admin.layouts.master')

@section('title', 'Chi tiết Loyalty')
@section('page-title', 'Chi tiết Loyalty')
@section('page-description', 'Theo dõi điểm thưởng, hạng thành viên và lịch sử giao dịch.')

@section('content')
@php
    $customerStatusClass = match ($account->customer_status) {
        'active' => 'bg-green-100 text-green-700',
        'inactive' => 'bg-orange-100 text-orange-700',
        'blocked' => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-700',
    };

    $customerStatusLabel = match ($account->customer_status) {
        'active' => 'Đang hoạt động',
        'inactive' => 'Tạm ngừng',
        'blocked' => 'Đã khóa',
        default => $account->customer_status,
    };

    $tierExpired = $account->tier_expires_at
        && \Carbon\Carbon::parse($account->tier_expires_at)->isPast();

    $tierExpiringSoon = $account->tier_expires_at
        && ! $tierExpired
        && \Carbon\Carbon::parse($account->tier_expires_at)->lte(now()->addDays(30));
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            @if ($account->customer_avatar)
                <img
                    src="{{ asset('storage/' . ltrim($account->customer_avatar, '/')) }}"
                    alt="{{ $account->customer_name }}"
                    class="h-16 w-16 rounded-full border border-gray-200 object-cover"
                >
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-pink-100 text-2xl font-bold text-pink-600">
                    {{ mb_strtoupper(mb_substr($account->customer_name, 0, 1)) }}
                </div>
            @endif

            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ $account->customer_name }}
                    </h2>

                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $customerStatusClass }}">
                        {{ $customerStatusLabel }}
                    </span>
                </div>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $account->customer_email }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            @if (str_contains($account->customer_email, 'admin'))
    <span
        class="rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-500"
    >
        Tài khoản quản trị
    </span>
@else
    <a
        href="{{ route('admin.customers.show', $account->user_id) }}"
        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700"
    >
        Xem khách hàng
    </a>
@endif

            <a
                href="{{ route('admin.loyalty.index') }}"
                class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white"
            >
                Quay lại
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <ul class="list-disc space-y-1 pl-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ([
            ['Điểm khả dụng', $account->available_points, 'text-pink-600'],
            ['Điểm đang chờ', $account->pending_points, 'text-orange-600'],
            ['Tổng điểm đã nhận', $account->lifetime_earned_points, 'text-green-600'],
            ['Tổng điểm đã dùng', $account->lifetime_redeemed_points, 'text-red-600'],
            ['Tổng giao dịch', $transactionStatistics->total_transactions ?? 0, 'text-blue-600'],
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

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Chi tiêu trọn đời
            </p>

            <strong class="mt-2 block text-xl text-purple-600">
                {{ number_format(
                    (float) $account->lifetime_spending,
                    0,
                    ',',
                    '.'
                ) }}đ
            </strong>
        </article>
    </div>

    <div class="admin-responsive-sidebar-layout grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_380px]">
        <div class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            Hạng thành viên hiện tại
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Thông tin quyền lợi và thời gian hiệu lực của hạng.
                        </p>
                    </div>

                    @if ($account->tier_id)
                        <div class="flex items-center gap-3">
                            @if ($account->tier_icon)
                                <span class="text-3xl">
                                    {{ $account->tier_icon }}
                                </span>
                            @endif

                            <div>
                                <strong
                                    class="block text-xl"
                                    style="color: {{ $account->tier_color ?: '#111827' }}"
                                >
                                    {{ $account->tier_name }}
                                </strong>

                                <span class="text-xs uppercase tracking-wide text-gray-500">
                                    {{ $account->tier_code }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($account->tier_id)
                    <dl class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <dt class="text-sm text-gray-500">
                                Mức chi tiêu tối thiểu
                            </dt>

                            <dd class="mt-1 font-semibold text-gray-900">
                                {{ number_format(
                                    (float) $account->tier_minimum_spending,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Điểm tối thiểu
                            </dt>

                            <dd class="mt-1 font-semibold text-gray-900">
                                {{ number_format((int) $account->tier_minimum_points) }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Hệ số nhân điểm
                            </dt>

                            <dd class="mt-1 font-semibold text-green-700">
                                x{{ number_format((float) $account->tier_point_multiplier, 2) }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Giảm giá thành viên
                            </dt>

                            <dd class="mt-1 font-semibold text-pink-600">
                                {{ number_format((float) $account->tier_discount_percent, 2) }}%
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Bắt đầu hạng
                            </dt>

                            <dd class="mt-1 font-semibold text-gray-900">
                                {{ $account->tier_started_at
                                    ? \Carbon\Carbon::parse($account->tier_started_at)->format('d/m/Y H:i')
                                    : 'Chưa xác định' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Hết hạn hạng
                            </dt>

                            <dd class="mt-1">
                                @if (! $account->tier_expires_at)
                                    <span class="font-semibold text-gray-900">
                                        Không giới hạn
                                    </span>
                                @elseif ($tierExpired)
                                    <span class="font-semibold text-red-700">
                                        {{ \Carbon\Carbon::parse($account->tier_expires_at)->format('d/m/Y H:i') }}
                                        — Đã hết hạn
                                    </span>
                                @elseif ($tierExpiringSoon)
                                    <span class="font-semibold text-blue-700">
                                        {{ \Carbon\Carbon::parse($account->tier_expires_at)->format('d/m/Y H:i') }}
                                        — Sắp hết hạn
                                    </span>
                                @else
                                    <span class="font-semibold text-green-700">
                                        {{ \Carbon\Carbon::parse($account->tier_expires_at)->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            </dd>
                        </div>

                        @if ($account->tier_description)
                            <div class="md:col-span-2">
                                <dt class="text-sm text-gray-500">
                                    Mô tả
                                </dt>

                                <dd class="mt-2 whitespace-pre-line text-gray-700">
                                    {{ $account->tier_description }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <div class="mt-6 rounded-xl border border-dashed border-gray-300 p-8 text-center">
                        <p class="text-sm text-gray-500">
                            Tài khoản chưa được gán hạng Loyalty.
                        </p>
                    </div>
                @endif
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <form
                    method="GET"
                    action="{{ route('admin.loyalty.show', $account->id) }}"
                    class="grid grid-cols-1 gap-4 md:grid-cols-3"
                >
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Loại giao dịch
                        </label>

                        <select
                            name="transaction_type"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                            <option value="">
                                Tất cả
                            </option>

                            @foreach ($transactionTypes as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(request('transaction_type') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Trạng thái giao dịch
                        </label>

                        <select
                            name="transaction_status"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                            <option value="">
                                Tất cả
                            </option>

                            @foreach ($transactionStatuses as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(request('transaction_status') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-3">
                        <button
                            type="submit"
                            class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white"
                        >
                            Lọc
                        </button>

                        <a
                            href="{{ route('admin.loyalty.show', $account->id) }}"
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
                        Lịch sử giao dịch điểm
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Hiển thị {{ $transactions->count() }} / {{ $transactions->total() }} giao dịch.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                    Thời gian
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                    Loại
                                </th>

                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                    Điểm
                                </th>

                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                    Số dư
                                </th>

                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                                    Trạng thái
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                    Tham chiếu
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                    Mô tả
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($transactions as $transaction)
                                @php
                                    $typeClass = match ($transaction->type) {
                                        'earn' => 'bg-green-100 text-green-700',
                                        'redeem' => 'bg-purple-100 text-purple-700',
                                        'refund' => 'bg-blue-100 text-blue-700',
                                        'expire' => 'bg-red-100 text-red-700',
                                        'adjust' => 'bg-orange-100 text-orange-700',
                                        'cancel' => 'bg-gray-100 text-gray-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };

                                    $statusClass = match ($transaction->status) {
                                        'completed' => 'bg-green-100 text-green-700',
                                        'pending' => 'bg-orange-100 text-orange-700',
                                        'cancelled' => 'bg-gray-100 text-gray-700',
                                        'expired' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp

                                <tr class="align-top hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                        {{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $typeClass }}">
                                            {{ $transactionTypes[$transaction->type] ?? $transaction->type }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        <span class="font-bold {{ (int) $transaction->points >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ (int) $transaction->points > 0 ? '+' : '' }}
                                            {{ number_format((int) $transaction->points) }}
                                        </span>

                                        @if ((float) $transaction->monetary_value > 0)
                                            <p class="mt-1 text-xs text-gray-400">
                                                {{ number_format(
                                                    (float) $transaction->monetary_value,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}đ
                                            </p>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-right text-sm text-gray-700">
                                        <p>
                                            {{ number_format((int) $transaction->balance_before) }}
                                        </p>

                                        <p class="mt-1 font-semibold text-gray-900">
                                            → {{ number_format((int) $transaction->balance_after) }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                            {{ $transactionStatuses[$transaction->status] ?? $transaction->status }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 text-sm text-gray-600">
                                        @if ($transaction->order_id && $transaction->order_code)
                                            <a
                                                href="{{ route('admin.orders.show', $transaction->order_id) }}"
                                                class="font-semibold text-blue-600 hover:underline"
                                            >
                                                {{ $transaction->order_code }}
                                            </a>
                                        @elseif ($transaction->reference_type)
                                            <p>
                                                {{ $transaction->reference_type }}
                                            </p>

                                            @if ($transaction->reference_id)
                                                <p class="mt-1 text-xs text-gray-400">
                                                    ID: {{ $transaction->reference_id }}
                                                </p>
                                            @endif
                                        @else
                                            <span class="text-gray-400">
                                                Không có
                                            </span>
                                        @endif

                                        @if ($transaction->creator_name)
                                            <p class="mt-2 text-xs text-gray-400">
                                                Bởi: {{ $transaction->creator_name }}
                                            </p>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-sm text-gray-600">
                                        <p class="min-w-[220px] whitespace-pre-line">
                                            {{ $transaction->description ?: 'Không có mô tả' }}
                                        </p>

                                        @if ($transaction->available_at)
                                            <p class="mt-2 text-xs text-gray-400">
                                                Khả dụng:
                                                {{ \Carbon\Carbon::parse($transaction->available_at)->format('d/m/Y H:i') }}
                                            </p>
                                        @endif

                                        @if ($transaction->expires_at)
                                            <p class="mt-1 text-xs text-red-500">
                                                Hết hạn:
                                                {{ \Carbon\Carbon::parse($transaction->expires_at)->format('d/m/Y H:i') }}
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="7"
                                        class="px-5 py-16 text-center text-gray-500"
                                    >
                                        Chưa có giao dịch Loyalty.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($transactions->hasPages())
                    <div class="border-t border-gray-200 px-5 py-4">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Điều chỉnh điểm
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Cộng hoặc trừ điểm thủ công và lưu lịch sử giao dịch.
                </p>

                <form
                    method="POST"
                    action="{{ route('admin.loyalty.adjust-points', $account->id) }}"
                    class="mt-5 space-y-4"
                >
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Thao tác
                        </label>

                        <select
                            name="operation"
                            required
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                            <option value="add" @selected(old('operation') === 'add')>
                                Cộng điểm
                            </option>

                            <option value="subtract" @selected(old('operation') === 'subtract')>
                                Trừ điểm
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Số điểm
                        </label>

                        <input
                            type="number"
                            name="points"
                            value="{{ old('points') }}"
                            min="1"
                            step="1"
                            required
                            class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Giá trị quy đổi
                        </label>

                        <input
                            type="number"
                            name="monetary_value"
                            value="{{ old('monetary_value', 0) }}"
                            min="0"
                            step="0.01"
                            class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Lý do
                        </label>

                        <textarea
                            name="description"
                            rows="4"
                            maxlength="2000"
                            required
                            class="w-full rounded-lg border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >{{ old('description') }}</textarea>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-pink-700"
                    >
                        Xác nhận điều chỉnh
                    </button>
                </form>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Đổi hạng thành viên
                </h3>

                <form
                    method="POST"
                    action="{{ route('admin.loyalty.update-tier', $account->id) }}"
                    class="mt-5 space-y-4"
                >
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Hạng mới
                        </label>

                        <select
                            name="tier_id"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                            <option value="">
                                Không có hạng
                            </option>

                            @foreach ($tiers as $tier)
                                <option
                                    value="{{ $tier->id }}"
                                    @selected(
                                        (string) old('tier_id', $account->tier_id)
                                        === (string) $tier->id
                                    )
                                >
                                    {{ $tier->name }}
                                    {{ (int) $tier->status === 1 ? '' : '(Đang tắt)' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Bắt đầu
                        </label>

                        <input
                            type="datetime-local"
                            name="tier_started_at"
                            value="{{ old(
                                'tier_started_at',
                                $account->tier_started_at
                                    ? \Carbon\Carbon::parse($account->tier_started_at)->format('Y-m-d\TH:i')
                                    : now()->format('Y-m-d\TH:i')
                            ) }}"
                            class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Hết hạn
                        </label>

                        <input
                            type="datetime-local"
                            name="tier_expires_at"
                            value="{{ old(
                                'tier_expires_at',
                                $account->tier_expires_at
                                    ? \Carbon\Carbon::parse($account->tier_expires_at)->format('Y-m-d\TH:i')
                                    : ''
                            ) }}"
                            class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Lý do đổi hạng
                        </label>

                        <textarea
                            name="reason"
                            rows="4"
                            maxlength="2000"
                            required
                            class="w-full rounded-lg border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >{{ old('reason') }}</textarea>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white"
                    >
                        Cập nhật hạng
                    </button>
                </form>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Thống kê giao dịch
                </h3>

                <dl class="mt-4 space-y-4 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">
                            Tổng giao dịch
                        </dt>

                        <dd class="font-semibold text-gray-900">
                            {{ number_format((int) ($transactionStatistics->total_transactions ?? 0)) }}
                        </dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">
                            Tổng điểm cộng
                        </dt>

                        <dd class="font-semibold text-green-600">
                            {{ number_format((int) ($transactionStatistics->credited_points ?? 0)) }}
                        </dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">
                            Tổng điểm trừ
                        </dt>

                        <dd class="font-semibold text-red-600">
                            {{ number_format((int) ($transactionStatistics->debited_points ?? 0)) }}
                        </dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">
                            Điểm giao dịch chờ
                        </dt>

                        <dd class="font-semibold text-orange-600">
                            {{ number_format((int) ($transactionStatistics->pending_transaction_points ?? 0)) }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Đăng nhập gần nhất
                </h3>

                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">
                            Thời gian
                        </dt>

                        <dd class="mt-1 font-medium text-gray-900">
                            {{ $account->last_login_at
                                ? \Carbon\Carbon::parse($account->last_login_at)->format('d/m/Y H:i')
                                : 'Chưa có' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">
                            IP
                        </dt>

                        <dd class="mt-1 font-medium text-gray-900">
                            {{ $account->last_login_ip ?: 'Chưa có' }}
                        </dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>
</div>
@endsection
