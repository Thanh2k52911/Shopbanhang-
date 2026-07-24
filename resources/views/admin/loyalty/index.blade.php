@extends('admin.layouts.master')

@section('title', 'Quản lý Loyalty')
@section('page-title', 'Quản lý Loyalty')
@section('page-description', 'Theo dõi tài khoản thành viên, điểm thưởng, hạng và lịch sử hoạt động.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Loyalty
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Quản lý tài khoản thành viên, điểm thưởng và hạng khách hàng.
            </p>
        </div>

        <a
            href="{{ route('admin.loyalty.tiers') }}"
            class="inline-flex justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-pink-700"
        >
            Quản lý hạng Loyalty
        </a>
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

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-8">
        @foreach ([
            ['Tài khoản Loyalty', $statistics['total_accounts'], 'text-gray-900'],
            ['Điểm khả dụng', $statistics['total_available_points'], 'text-pink-600'],
            ['Điểm đang chờ', $statistics['total_pending_points'], 'text-orange-600'],
            ['Tổng điểm đã nhận', $statistics['lifetime_earned_points'], 'text-green-600'],
            ['Tổng điểm đã dùng', $statistics['lifetime_redeemed_points'], 'text-red-600'],
            ['Hạng đã hết hạn', $statistics['expired_tiers'], 'text-red-600'],
            ['Sắp hết hạn', $statistics['expiring_soon'], 'text-blue-600'],
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
                    (float) $statistics['lifetime_spending'],
                    0,
                    ',',
                    '.'
                ) }}đ
            </strong>
        </article>
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form
            method="GET"
            action="{{ route('admin.loyalty.index') }}"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6"
        >
            <div class="xl:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Tìm kiếm
                </label>

                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    placeholder="Tên, email, tên hạng hoặc mã hạng..."
                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Hạng Loyalty
                </label>

                <select
                    name="tier_id"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">
                        Tất cả hạng
                    </option>

                    @foreach ($tiers as $tier)
                        <option
                            value="{{ $tier->id }}"
                            @selected((string) request('tier_id') === (string) $tier->id)
                        >
                            {{ $tier->name }}
                            {{ (int) $tier->status === 1 ? '' : '(Đang tắt)' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Điểm khả dụng
                </label>

                <select
                    name="points"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">
                        Tất cả
                    </option>

                    <option
                        value="positive"
                        @selected(request('points') === 'positive')
                    >
                        Có điểm
                    </option>

                    <option
                        value="zero"
                        @selected(request('points') === 'zero')
                    >
                        Bằng 0
                    </option>

                    <option
                        value="high"
                        @selected(request('points') === 'high')
                    >
                        Từ 1.000 điểm
                    </option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Hạn hạng thành viên
                </label>

                <select
                    name="tier_expiry"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">
                        Tất cả
                    </option>

                    <option
                        value="valid"
                        @selected(request('tier_expiry') === 'valid')
                    >
                        Còn hiệu lực
                    </option>

                    <option
                        value="expiring_soon"
                        @selected(request('tier_expiry') === 'expiring_soon')
                    >
                        Hết hạn trong 30 ngày
                    </option>

                    <option
                        value="expired"
                        @selected(request('tier_expiry') === 'expired')
                    >
                        Đã hết hạn
                    </option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Trạng thái khách hàng
                </label>

                <select
                    name="customer_status"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">
                        Tất cả
                    </option>

                    <option
                        value="active"
                        @selected(request('customer_status') === 'active')
                    >
                        Đang hoạt động
                    </option>

                    <option
                        value="inactive"
                        @selected(request('customer_status') === 'inactive')
                    >
                        Tạm ngừng
                    </option>

                    <option
                        value="blocked"
                        @selected(request('customer_status') === 'blocked')
                    >
                        Đã khóa
                    </option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Sắp xếp
                </label>

                <select
                    name="sort"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">
                        Mới nhất
                    </option>

                    <option
                        value="oldest"
                        @selected(request('sort') === 'oldest')
                    >
                        Cũ nhất
                    </option>

                    <option
                        value="name_asc"
                        @selected(request('sort') === 'name_asc')
                    >
                        Tên A → Z
                    </option>

                    <option
                        value="name_desc"
                        @selected(request('sort') === 'name_desc')
                    >
                        Tên Z → A
                    </option>

                    <option
                        value="points_desc"
                        @selected(request('sort') === 'points_desc')
                    >
                        Điểm cao nhất
                    </option>

                    <option
                        value="points_asc"
                        @selected(request('sort') === 'points_asc')
                    >
                        Điểm thấp nhất
                    </option>

                    <option
                        value="spending_desc"
                        @selected(request('sort') === 'spending_desc')
                    >
                        Chi tiêu cao nhất
                    </option>

                    <option
                        value="transactions_desc"
                        @selected(request('sort') === 'transactions_desc')
                    >
                        Nhiều giao dịch nhất
                    </option>

                    <option
                        value="last_transaction_desc"
                        @selected(request('sort') === 'last_transaction_desc')
                    >
                        Giao dịch gần nhất
                    </option>
                </select>
            </div>

            <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-6">
                <button
                    type="submit"
                    class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white"
                >
                    Lọc dữ liệu
                </button>

                <a
                    href="{{ route('admin.loyalty.index') }}"
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
                Danh sách tài khoản Loyalty
            </h3>

            <p class="mt-1 text-sm text-gray-500">
                Hiển thị {{ $accounts->count() }} / {{ $accounts->total() }} kết quả.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Khách hàng
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Hạng thành viên
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                            Điểm
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                            Chi tiêu
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            Giao dịch
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Hạn hạng
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                            Thao tác
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($accounts as $account)
                        @php
                            $customerStatusClass = match ($account->customer_status) {
                                'active' => 'bg-green-100 text-green-700',
                                'inactive' => 'bg-orange-100 text-orange-700',
                                'blocked' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700',
                            };

                            $customerStatusLabel = match ($account->customer_status) {
                                'active' => 'Hoạt động',
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

                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="flex min-w-[280px] items-center gap-3">
                                    @if ($account->customer_avatar)
                                        <img
                                            src="{{ asset('storage/' . ltrim($account->customer_avatar, '/')) }}"
                                            alt="{{ $account->customer_name }}"
                                            class="h-11 w-11 rounded-full border border-gray-200 object-cover"
                                        >
                                    @else
                                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-pink-100 font-bold text-pink-600">
                                            {{ mb_strtoupper(mb_substr($account->customer_name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <a
                                            href="{{ route('admin.loyalty.show', $account->id) }}"
                                            class="block truncate font-semibold text-gray-900 hover:text-pink-600"
                                        >
                                            {{ $account->customer_name }}
                                        </a>

                                        <p class="mt-1 truncate text-xs text-gray-500">
                                            {{ $account->customer_email }}
                                        </p>

                                        <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $customerStatusClass }}">
                                            {{ $customerStatusLabel }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if ($account->tier_id)
                                    <div class="min-w-[160px]">
                                        <div class="flex items-center gap-2">
                                            @if ($account->tier_icon)
                                                <span class="text-lg">
                                                    {{ $account->tier_icon }}
                                                </span>
                                            @endif

                                            <span
                                                class="font-semibold"
                                                style="color: {{ $account->tier_color ?: '#111827' }}"
                                            >
                                                {{ $account->tier_name }}
                                            </span>
                                        </div>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $account->tier_code }}
                                        </p>

                                        @if ((int) $account->tier_status !== 1)
                                            <span class="mt-2 inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                                Hạng đang tắt
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500">
                                        Chưa có hạng
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="min-w-[140px]">
                                    <p class="font-bold text-pink-600">
                                        {{ number_format((int) $account->available_points) }}
                                    </p>

                                    <p class="mt-1 text-xs text-orange-600">
                                        Chờ:
                                        {{ number_format((int) $account->pending_points) }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-400">
                                        Đã dùng:
                                        {{ number_format((int) $account->lifetime_redeemed_points) }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-right font-semibold text-purple-600">
                                {{ number_format(
                                    (float) $account->lifetime_spending,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </td>

                            <td class="px-5 py-4 text-center">
                                <p class="font-semibold text-blue-600">
                                    {{ number_format((int) $account->transactions_count) }}
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    {{ $account->last_transaction_at
                                        ? \Carbon\Carbon::parse($account->last_transaction_at)->format('d/m/Y H:i')
                                        : 'Chưa có' }}
                                </p>
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-600">
                                @if (! $account->tier_expires_at)
                                    <span class="text-gray-500">
                                        Không giới hạn
                                    </span>
                                @elseif ($tierExpired)
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                        Đã hết hạn
                                    </span>

                                    <p class="mt-2">
                                        {{ \Carbon\Carbon::parse($account->tier_expires_at)->format('d/m/Y H:i') }}
                                    </p>
                                @elseif ($tierExpiringSoon)
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                        Sắp hết hạn
                                    </span>

                                    <p class="mt-2">
                                        {{ \Carbon\Carbon::parse($account->tier_expires_at)->format('d/m/Y H:i') }}
                                    </p>
                                @else
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        Còn hiệu lực
                                    </span>

                                    <p class="mt-2">
                                        {{ \Carbon\Carbon::parse($account->tier_expires_at)->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <a
                                    href="{{ route('admin.loyalty.show', $account->id) }}"
                                    class="inline-flex rounded-lg border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50"
                                >
                                    CHI TIẾT
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="7"
                                class="px-5 py-16 text-center text-gray-500"
                            >
                                Không tìm thấy tài khoản Loyalty.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($accounts->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $accounts->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
