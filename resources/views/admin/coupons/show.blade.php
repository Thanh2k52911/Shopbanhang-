@extends('admin.layouts.master')

@section('title', 'Chi tiết mã giảm giá')
@section('page-title', 'Chi tiết mã giảm giá')
@section('page-description', 'Theo dõi điều kiện áp dụng, phạm vi sử dụng và lịch sử coupon.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex min-w-0 items-center gap-4">
            <a
                href="{{ route('admin.coupons.index') }}"
                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white text-xl text-gray-600 transition hover:bg-gray-50"
                title="Quay lại"
            >
                ←
            </a>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-2xl font-bold tracking-wide text-gray-900">
                        {{ $coupon->code }}
                    </h2>

                    @if ((int) $coupon->status === 1)
                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                            Đang bật
                        </span>
                    @else
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                            Đang tắt
                        </span>
                    @endif

                    @if ((int) $coupon->is_public === 1)
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                            Công khai
                        </span>
                    @else
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                            Riêng tư
                        </span>
                    @endif
                </div>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $coupon->name }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('admin.coupons.edit', $coupon->id) }}"
                class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700"
            >
                Chỉnh sửa
            </a>

            <form
                method="POST"
                action="{{ route('admin.coupons.destroy', $coupon->id) }}"
                onsubmit="return confirm('Bạn chắc chắn muốn xóa mã giảm giá này?');"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100"
                >
                    Xóa coupon
                </button>
            </form>
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
    @endphp

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Lượt sử dụng</p>
            <strong class="mt-2 block text-2xl text-gray-900">
                {{ number_format($statistics['usage_count']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Tổng tiền đã giảm</p>
            <strong class="mt-2 block text-xl text-orange-600">
                {{ number_format((float) $statistics['total_discount'], 0, ',', '.') }}đ
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Lượt lưu</p>
            <strong class="mt-2 block text-2xl text-pink-600">
                {{ number_format($statistics['saved_count']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Sản phẩm áp dụng</p>
            <strong class="mt-2 block text-2xl text-blue-600">
                {{ number_format($statistics['products_count']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Danh mục áp dụng</p>
            <strong class="mt-2 block text-2xl text-indigo-600">
                {{ number_format($statistics['categories_count']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Người dùng chỉ định</p>
            <strong class="mt-2 block text-2xl text-green-600">
                {{ number_format($statistics['users_count']) }}
            </strong>
        </article>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(340px,1fr)]">
        <div class="space-y-6">
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Thông tin coupon
                    </h3>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-gray-500">Mã coupon</p>
                        <strong class="mt-1 block text-lg tracking-wide text-gray-900">
                            {{ $coupon->code }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Tên coupon</p>
                        <strong class="mt-1 block text-gray-900">
                            {{ $coupon->name }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Loại giảm giá</p>
                        <strong class="mt-1 block text-gray-900">
                            {{ match ($coupon->discount_type) {
                                'fixed' => 'Giảm số tiền cố định',
                                'percentage' => 'Giảm theo phần trăm',
                                'free_shipping' => 'Miễn phí vận chuyển',
                                default => $coupon->discount_type,
                            } }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Giá trị giảm</p>

                        @if ($coupon->discount_type === 'percentage')
                            <strong class="mt-1 block text-lg text-pink-600">
                                {{ rtrim(rtrim(number_format((float) $coupon->discount_value, 2, '.', ''), '0'), '.') }}%
                            </strong>
                        @elseif ($coupon->discount_type === 'fixed')
                            <strong class="mt-1 block text-lg text-blue-600">
                                {{ number_format((float) $coupon->discount_value, 0, ',', '.') }}đ
                            </strong>
                        @else
                            <strong class="mt-1 block text-lg text-green-600">
                                Miễn phí vận chuyển
                            </strong>
                        @endif
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Giảm tối đa</p>
                        <strong class="mt-1 block text-gray-900">
                            {{ $coupon->maximum_discount !== null
                                ? number_format((float) $coupon->maximum_discount, 0, ',', '.') . 'đ'
                                : 'Không giới hạn' }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Đơn tối thiểu</p>
                        <strong class="mt-1 block text-gray-900">
                            {{ number_format((float) $coupon->minimum_order_amount, 0, ',', '.') }}đ
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Tổng lượt dùng</p>
                        <strong class="mt-1 block text-gray-900">
                            {{ $coupon->usage_limit !== null
                                ? number_format((int) $coupon->usage_limit)
                                : 'Không giới hạn' }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Giới hạn mỗi người</p>
                        <strong class="mt-1 block text-gray-900">
                            {{ number_format((int) $coupon->usage_limit_per_user) }} lượt
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Đơn hàng đầu tiên</p>
                        <strong class="mt-1 block text-gray-900">
                            {{ (int) $coupon->first_order_only === 1 ? 'Có' : 'Không' }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Người tạo</p>
                        <strong class="mt-1 block text-gray-900">
                            {{ $coupon->creator_name ?: 'Hệ thống' }}
                        </strong>

                        @if ($coupon->creator_email)
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $coupon->creator_email }}
                            </p>
                        @endif
                    </div>

                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500">Mô tả</p>
                        <p class="mt-2 whitespace-pre-line text-gray-800">
                            {{ $coupon->description ?: 'Chưa có mô tả' }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Phạm vi áp dụng
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Nếu các danh sách đều trống, coupon được hiểu là áp dụng toàn hệ thống.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 p-5 lg:grid-cols-3">
                    <div>
                        <h4 class="font-semibold text-gray-900">
                            Sản phẩm
                        </h4>

                        <div class="mt-3 space-y-2">
                            @forelse ($products as $product)
                                <a
                                    href="{{ route('admin.products.show', $product->id) }}"
                                    class="block rounded-lg border border-gray-200 px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:border-pink-300 hover:bg-pink-50 hover:text-pink-700"
                                >
                                    {{ $product->name }}
                                </a>
                            @empty
                                <p class="text-sm text-gray-500">
                                    Áp dụng cho toàn bộ sản phẩm.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h4 class="font-semibold text-gray-900">
                            Danh mục
                        </h4>

                        <div class="mt-3 space-y-2">
                            @forelse ($categories as $category)
                                <a
                                    href="{{ route('admin.categories.show', $category->id) }}"
                                    class="block rounded-lg border border-gray-200 px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:border-pink-300 hover:bg-pink-50 hover:text-pink-700"
                                >
                                    {{ $category->name }}
                                </a>
                            @empty
                                <p class="text-sm text-gray-500">
                                    Không giới hạn theo danh mục.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h4 class="font-semibold text-gray-900">
                            Người dùng
                        </h4>

                        <div class="mt-3 space-y-2">
                            @forelse ($users as $user)
                                <div class="rounded-lg border border-gray-200 px-3 py-2.5">
                                    <p class="text-sm font-medium text-gray-800">
                                        {{ $user->name }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $user->email }}
                                    </p>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">
                                    Không giới hạn người dùng cụ thể.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Lịch sử sử dụng
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Danh sách đơn hàng đã sử dụng coupon này.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Thời gian
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Khách hàng
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Đơn hàng
                                </th>

                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Số tiền giảm
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($usages as $usage)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                        {{ \Carbon\Carbon::parse($usage->used_at)->format('d/m/Y H:i') }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <p class="font-medium text-gray-800">
                                            {{ $usage->user_name ?: 'Khách vãng lai' }}
                                        </p>

                                        @if ($usage->user_email)
                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ $usage->user_email }}
                                            </p>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4">
                                        <a
                                            href="{{ route('admin.orders.show', $usage->order_id) }}"
                                            class="font-semibold text-blue-600 hover:underline"
                                        >
                                            {{ $usage->order_code }}
                                        </a>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $usage->order_status }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        <strong class="text-orange-600">
                                            {{ number_format((float) $usage->discount_amount, 0, ',', '.') }}đ
                                        </strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-12 text-center text-gray-500">
                                        Coupon chưa được sử dụng.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($usages->hasPages())
                    <div class="border-t border-gray-200 px-5 py-4">
                        {{ $usages->links() }}
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Hiệu lực hiện tại
                </h3>

                <div class="mt-4">
                    @if ((int) $coupon->status !== 1)
                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-600">
                            Đang tắt
                        </span>
                    @elseif ($isScheduled)
                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">
                            Sắp bắt đầu
                        </span>
                    @elseif ($isExpiredByTime || $isExhausted)
                        <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-700">
                            Hết hiệu lực
                        </span>
                    @elseif ($isActive)
                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">
                            Đang hoạt động
                        </span>
                    @endif
                </div>

                <dl class="mt-4 space-y-4">
                    <div>
                        <dt class="text-sm text-gray-500">Bắt đầu</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ $coupon->start_at
                                ? \Carbon\Carbon::parse($coupon->start_at)->format('d/m/Y H:i')
                                : 'Không giới hạn' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Kết thúc</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ $coupon->end_at
                                ? \Carbon\Carbon::parse($coupon->end_at)->format('d/m/Y H:i')
                                : 'Không giới hạn' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Đã dùng</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ number_format((int) $coupon->used_count) }}
                            /
                            {{ $coupon->usage_limit !== null
                                ? number_format((int) $coupon->usage_limit)
                                : '∞' }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Hiển thị
                </h3>

                <dl class="mt-4 space-y-4">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-gray-500">Trạng thái</dt>
                        <dd class="font-semibold text-gray-900">
                            {{ (int) $coupon->status === 1 ? 'Đang bật' : 'Đang tắt' }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-gray-500">Phạm vi</dt>
                        <dd class="font-semibold text-gray-900">
                            {{ (int) $coupon->is_public === 1 ? 'Công khai' : 'Riêng tư' }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-gray-500">Đơn đầu tiên</dt>
                        <dd class="font-semibold text-gray-900">
                            {{ (int) $coupon->first_order_only === 1 ? 'Có' : 'Không' }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-orange-200 bg-orange-50 p-5">
                <h3 class="font-bold text-orange-900">
                    Lưu ý
                </h3>

                <p class="mt-3 text-sm leading-6 text-orange-800">
                    Coupon đã có lượt sử dụng không thể xóa. Trường hợp không muốn tiếp tục áp dụng, hãy chuyển trạng thái sang tắt.
                </p>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Thời gian
                </h3>

                <dl class="mt-4 space-y-4">
                    <div>
                        <dt class="text-sm text-gray-500">
                            Ngày tạo
                        </dt>

                        <dd class="mt-1 font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($coupon->created_at)->format('d/m/Y H:i') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Cập nhật gần nhất
                        </dt>

                        <dd class="mt-1 font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($coupon->updated_at)->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>
</div>
@endsection
