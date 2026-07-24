@extends('admin.layouts.master')

@section('title', 'Quản lý mã giảm giá')
@section('page-title', 'Quản lý mã giảm giá')
@section('page-description', 'Theo dõi hiệu lực, phạm vi áp dụng và hiệu quả sử dụng coupon.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Mã giảm giá
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Quản lý coupon cố định, phần trăm, miễn phí vận chuyển và giới hạn sử dụng.
            </p>
        </div>

        <a
            href="{{ route('admin.coupons.create') }}"
            class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700"
        >
            + Thêm mã giảm giá
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

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Tổng coupon</p>
            <strong class="mt-2 block text-2xl text-gray-900">
                {{ number_format($statistics['total']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Đang hoạt động</p>
            <strong class="mt-2 block text-2xl text-green-600">
                {{ number_format($statistics['active']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Sắp bắt đầu</p>
            <strong class="mt-2 block text-2xl text-blue-600">
                {{ number_format($statistics['scheduled']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Đã hết hạn</p>
            <strong class="mt-2 block text-2xl text-red-600">
                {{ number_format($statistics['expired']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Công khai</p>
            <strong class="mt-2 block text-2xl text-pink-600">
                {{ number_format($statistics['public']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Tổng tiền đã giảm</p>
            <strong class="mt-2 block text-xl text-orange-600">
                {{ number_format((float) $statistics['total_discount'], 0, ',', '.') }}đ
            </strong>
        </article>
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form
            method="GET"
            action="{{ route('admin.coupons.index') }}"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6"
        >
            <div class="xl:col-span-2">
                <label for="keyword" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Tìm kiếm
                </label>

                <input
                    id="keyword"
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    placeholder="Mã, tên hoặc mô tả coupon..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label for="discount_type" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Loại giảm giá
                </label>

                <select
                    id="discount_type"
                    name="discount_type"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả loại</option>
                    <option value="fixed" @selected(request('discount_type') === 'fixed')>
                        Giảm cố định
                    </option>
                    <option value="percentage" @selected(request('discount_type') === 'percentage')>
                        Giảm phần trăm
                    </option>
                    <option value="free_shipping" @selected(request('discount_type') === 'free_shipping')>
                        Miễn phí vận chuyển
                    </option>
                </select>
            </div>

            <div>
                <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Trạng thái
                </label>

                <select
                    id="status"
                    name="status"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả trạng thái</option>
                    <option value="1" @selected((string) request('status') === '1')>
                        Đang bật
                    </option>
                    <option value="0" @selected((string) request('status') === '0')>
                        Đang tắt
                    </option>
                </select>
            </div>

            <div>
                <label for="visibility" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Phạm vi hiển thị
                </label>

                <select
                    id="visibility"
                    name="visibility"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả</option>
                    <option value="public" @selected(request('visibility') === 'public')>
                        Công khai
                    </option>
                    <option value="private" @selected(request('visibility') === 'private')>
                        Riêng tư
                    </option>
                </select>
            </div>

            <div>
                <label for="validity" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Hiệu lực
                </label>

                <select
                    id="validity"
                    name="validity"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('validity') === 'active')>
                        Đang hoạt động
                    </option>
                    <option value="scheduled" @selected(request('validity') === 'scheduled')>
                        Sắp bắt đầu
                    </option>
                    <option value="expired" @selected(request('validity') === 'expired')>
                        Hết hạn
                    </option>
                </select>
            </div>

            <div>
                <label for="sort" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Sắp xếp
                </label>

                <select
                    id="sort"
                    name="sort"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Mới nhất</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                    <option value="code_asc" @selected(request('sort') === 'code_asc')>Mã A → Z</option>
                    <option value="code_desc" @selected(request('sort') === 'code_desc')>Mã Z → A</option>
                    <option value="used_desc" @selected(request('sort') === 'used_desc')>Dùng nhiều nhất</option>
                    <option value="start_desc" @selected(request('sort') === 'start_desc')>Bắt đầu mới nhất</option>
                    <option value="end_asc" @selected(request('sort') === 'end_asc')>Sắp hết hạn</option>
                </select>
            </div>

            <div class="flex flex-wrap items-end gap-3 md:col-span-2 xl:col-span-6">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800"
                >
                    Lọc dữ liệu
                </button>

                <a
                    href="{{ route('admin.coupons.index') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Đặt lại
                </a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="flex flex-col gap-2 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">
                    Danh sách mã giảm giá
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Hiển thị {{ number_format($coupons->count()) }}
                    trên tổng {{ number_format($coupons->total()) }} kết quả.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Coupon
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Giá trị
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Điều kiện
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Đã dùng
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Thời gian
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Phạm vi
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Hiệu lực
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Thao tác
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($coupons as $coupon)
                        @php
                            $now = now();

                            $isScheduled = $coupon->start_at
                                && \Carbon\Carbon::parse($coupon->start_at)->isFuture();

                            $isExpiredByTime = $coupon->end_at
                                && \Carbon\Carbon::parse($coupon->end_at)->isPast();

                            $isExhausted = $coupon->usage_limit !== null
                                && (int) $coupon->used_count >= (int) $coupon->usage_limit;

                            $isActive = (int) $coupon->status === 1
                                && ! $isScheduled
                                && ! $isExpiredByTime
                                && ! $isExhausted;

                            $typeLabel = match ($coupon->discount_type) {
                                'fixed' => 'Giảm cố định',
                                'percentage' => 'Giảm phần trăm',
                                'free_shipping' => 'Miễn phí vận chuyển',
                                default => $coupon->discount_type,
                            };

                            $typeClasses = match ($coupon->discount_type) {
                                'fixed' => 'bg-blue-100 text-blue-700',
                                'percentage' => 'bg-pink-100 text-pink-700',
                                'free_shipping' => 'bg-green-100 text-green-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp

                        <tr class="align-top transition hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="min-w-[280px]">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a
                                            href="{{ route('admin.coupons.show', $coupon->id) }}"
                                            class="font-bold tracking-wide text-gray-900 transition hover:text-pink-600"
                                        >
                                            {{ $coupon->code }}
                                        </a>

                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $typeClasses }}">
                                            {{ $typeLabel }}
                                        </span>
                                    </div>

                                    <p class="mt-2 font-medium text-gray-800">
                                        {{ $coupon->name }}
                                    </p>

                                    @if ($coupon->description)
                                        <p
                                            class="mt-1 max-w-sm truncate text-xs text-gray-500"
                                            title="{{ $coupon->description }}"
                                        >
                                            {{ $coupon->description }}
                                        </p>
                                    @endif

                                    <p class="mt-2 text-xs text-gray-400">
                                        Người tạo: {{ $coupon->creator_name ?: 'Hệ thống' }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="min-w-[170px]">
                                    @if ($coupon->discount_type === 'percentage')
                                        <p class="text-lg font-bold text-pink-600">
                                            {{ rtrim(rtrim(number_format((float) $coupon->discount_value, 2, '.', ''), '0'), '.') }}%
                                        </p>

                                        @if ($coupon->maximum_discount !== null)
                                            <p class="mt-1 text-xs text-gray-500">
                                                Tối đa:
                                                {{ number_format((float) $coupon->maximum_discount, 0, ',', '.') }}đ
                                            </p>
                                        @endif
                                    @elseif ($coupon->discount_type === 'fixed')
                                        <p class="text-lg font-bold text-blue-600">
                                            {{ number_format((float) $coupon->discount_value, 0, ',', '.') }}đ
                                        </p>
                                    @else
                                        <p class="font-bold text-green-600">
                                            Miễn phí ship
                                        </p>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="min-w-[190px] space-y-1 text-sm text-gray-600">
                                    <p>
                                        Đơn tối thiểu:
                                        <strong class="text-gray-900">
                                            {{ number_format((float) $coupon->minimum_order_amount, 0, ',', '.') }}đ
                                        </strong>
                                    </p>

                                    <p>
                                        Mỗi người:
                                        <strong class="text-gray-900">
                                            {{ number_format((int) $coupon->usage_limit_per_user) }} lượt
                                        </strong>
                                    </p>

                                    @if ((int) $coupon->first_order_only === 1)
                                        <span class="inline-flex rounded-full bg-orange-100 px-2.5 py-1 text-xs font-semibold text-orange-700">
                                            Chỉ đơn đầu
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <p class="font-bold text-gray-900">
                                    {{ number_format((int) $coupon->used_count) }}
                                    /
                                    {{ $coupon->usage_limit !== null
                                        ? number_format((int) $coupon->usage_limit)
                                        : '∞' }}
                                </p>

                                <div class="mt-2 text-xs text-gray-500">
                                    <p>{{ number_format((int) $coupon->saved_count) }} lượt lưu</p>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="min-w-[190px] text-sm text-gray-600">
                                    <p>
                                        Bắt đầu:
                                        <strong class="text-gray-900">
                                            {{ $coupon->start_at
                                                ? \Carbon\Carbon::parse($coupon->start_at)->format('d/m/Y H:i')
                                                : 'Không giới hạn' }}
                                        </strong>
                                    </p>

                                    <p class="mt-1">
                                        Kết thúc:
                                        <strong class="text-gray-900">
                                            {{ $coupon->end_at
                                                ? \Carbon\Carbon::parse($coupon->end_at)->format('d/m/Y H:i')
                                                : 'Không giới hạn' }}
                                        </strong>
                                    </p>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if ((int) $coupon->is_public === 1)
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        Công khai
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        Riêng tư
                                    </span>
                                @endif

                                <div class="mt-2 space-y-1 text-xs text-gray-500">
                                    <p>{{ number_format((int) $coupon->products_count) }} sản phẩm</p>
                                    <p>{{ number_format((int) $coupon->categories_count) }} danh mục</p>
                                    <p>{{ number_format((int) $coupon->users_count) }} người dùng</p>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if ((int) $coupon->status !== 1)
                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                        Đang tắt
                                    </span>
                                @elseif ($isScheduled)
                                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                        Sắp bắt đầu
                                    </span>
                                @elseif ($isExpiredByTime || $isExhausted)
                                    <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                        Hết hiệu lực
                                    </span>
                                @elseif ($isActive)
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        Đang hoạt động
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex min-w-[245px] items-center justify-end gap-2">
                                    <a
                                        href="{{ route('admin.coupons.show', $coupon->id) }}"
                                        class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-xs font-bold text-gray-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700"
                                    >
                                        CHI TIẾT
                                    </a>

                                    <a
                                        href="{{ route('admin.coupons.edit', $coupon->id) }}"
                                        class="inline-flex h-9 items-center justify-center rounded-lg border border-orange-200 bg-orange-50 px-3 text-xs font-bold text-orange-700 transition hover:bg-orange-100"
                                    >
                                        SỬA
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.coupons.destroy', $coupon->id) }}"
                                        class="inline-flex"
                                        onsubmit="return confirm('Bạn chắc chắn muốn xóa mã giảm giá này?');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="inline-flex h-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-bold text-red-600 transition hover:bg-red-100"
                                        >
                                            XÓA
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center text-gray-500">
                                <div class="mx-auto max-w-md">
                                    <p class="text-lg font-semibold text-gray-700">
                                        Không tìm thấy mã giảm giá
                                    </p>

                                    <p class="mt-2 text-sm text-gray-500">
                                        Thử thay đổi từ khóa hoặc bộ lọc, hoặc tạo coupon mới.
                                    </p>

                                    <a
                                        href="{{ route('admin.coupons.create') }}"
                                        class="mt-4 inline-flex rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700"
                                    >
                                        Thêm mã giảm giá
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($coupons->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $coupons->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
