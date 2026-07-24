@extends('admin.layouts.master')

@section('title', 'Chi tiết chiến dịch giảm giá')
@section('page-title', 'Chi tiết chiến dịch giảm giá')
@section('page-description', 'Theo dõi sản phẩm áp dụng, giới hạn và số lượng đã bán.')

@section('content')
@php
    $scheduled = \Carbon\Carbon::parse($campaign->start_date)->isFuture();
    $ended = \Carbon\Carbon::parse($campaign->end_date)->isPast();
    $active = (int) $campaign->status === 1 && ! $scheduled && ! $ended;
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-2xl font-bold text-gray-900">{{ $campaign->name }}</h2>
                @if ((int) $campaign->is_flash_sale === 1)
                    <span class="rounded-full bg-pink-100 px-3 py-1 text-xs font-semibold text-pink-700">Flash sale</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-gray-500">ID: {{ $campaign->id }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.discount-campaigns.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">Quay lại</a>
            <a href="{{ route('admin.discount-campaigns.edit', $campaign->id) }}" class="rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white">Chỉnh sửa</a>
            <form method="POST" action="{{ route('admin.discount-campaigns.destroy', $campaign->id) }}" onsubmit="return confirm('Bạn chắc chắn muốn xóa chiến dịch này?');">
                @csrf
                @method('DELETE')
                <button class="rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700">Xóa</button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Sản phẩm', $statistics['products_count'], 'text-blue-600'],
            ['Đã bán', $statistics['sold_quantity'], 'text-orange-600'],
            ['Có giới hạn', $statistics['limited_products'], 'text-indigo-600'],
            ['Đã bán hết', $statistics['sold_out_products'], 'text-red-600'],
        ] as [$label, $value, $class])
            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <strong class="mt-2 block text-2xl {{ $class }}">{{ number_format($value) }}</strong>
            </article>
        @endforeach
    </div>

    <div class="admin-responsive-sidebar-layout grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_340px]">
        <div class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">Thông tin chiến dịch</h3>
                <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div><dt class="text-sm text-gray-500">Tên</dt><dd class="mt-1 font-semibold text-gray-900">{{ $campaign->name }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Trạng thái</dt><dd class="mt-1 font-semibold text-gray-900">{{ (int) $campaign->status === 1 ? 'Đang bật' : 'Đang tắt' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Bắt đầu</dt><dd class="mt-1 font-semibold text-gray-900">{{ \Carbon\Carbon::parse($campaign->start_date)->format('d/m/Y H:i') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Kết thúc</dt><dd class="mt-1 font-semibold text-gray-900">{{ \Carbon\Carbon::parse($campaign->end_date)->format('d/m/Y H:i') }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-sm text-gray-500">Mô tả</dt><dd class="mt-2 whitespace-pre-line text-gray-800">{{ $campaign->description ?: 'Không có mô tả' }}</dd></div>
                </dl>
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-900">Sản phẩm áp dụng</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Sản phẩm</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Mức giảm</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Giới hạn</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Đã bán</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Còn lại</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($discounts as $discount)
                                @php
                                    $remaining = $discount->limit_quantity === null
                                        ? null
                                        : max(0, (int) $discount->limit_quantity - (int) $discount->sold_quantity);
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('admin.products.show', $discount->product_id) }}" class="font-semibold text-gray-900 hover:text-pink-600">{{ $discount->product_name }}</a>
                                    </td>
                                    <td class="px-5 py-4 text-center font-semibold">
                                        @if ($discount->discount_percent !== null)
                                            <span class="text-pink-600">{{ rtrim(rtrim(number_format((float) $discount->discount_percent, 2, '.', ''), '0'), '.') }}%</span>
                                        @else
                                            <span class="text-blue-600">{{ number_format((float) $discount->discount_amount, 0, ',', '.') }}đ</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">{{ $discount->limit_quantity !== null ? number_format((int) $discount->limit_quantity) : 'Không giới hạn' }}</td>
                                    <td class="px-5 py-4 text-center font-semibold text-orange-600">{{ number_format((int) $discount->sold_quantity) }}</td>
                                    <td class="px-5 py-4 text-center">
                                        @if ($remaining === null)
                                            ∞
                                        @elseif ($remaining <= 0)
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Hết lượt</span>
                                        @else
                                            {{ number_format($remaining) }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-12 text-center text-gray-500">Chưa có sản phẩm.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Hiệu lực</h3>
                <div class="mt-4">
                    @if ((int) $campaign->status !== 1)
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-600">Đang tắt</span>
                    @elseif ($scheduled)
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">Sắp diễn ra</span>
                    @elseif ($ended)
                        <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-700">Đã kết thúc</span>
                    @elseif ($active)
                        <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">Đang hoạt động</span>
                    @endif
                </div>
            </section>

            <section class="rounded-xl border border-orange-200 bg-orange-50 p-5">
                <h3 class="font-bold text-orange-900">Lưu ý</h3>
                <p class="mt-3 text-sm leading-6 text-orange-800">Chiến dịch đã phát sinh lượt bán không thể xóa. Hãy chuyển trạng thái sang tắt.</p>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Thời gian</h3>
                <dl class="mt-4 space-y-4 text-sm">
                    <div><dt class="text-gray-500">Ngày tạo</dt><dd class="mt-1 font-medium text-gray-900">{{ \Carbon\Carbon::parse($campaign->created_at)->format('d/m/Y H:i') }}</dd></div>
                    <div><dt class="text-gray-500">Cập nhật</dt><dd class="mt-1 font-medium text-gray-900">{{ \Carbon\Carbon::parse($campaign->updated_at)->format('d/m/Y H:i') }}</dd></div>
                </dl>
            </section>
        </aside>
    </div>
</div>
@endsection
