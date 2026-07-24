@extends('admin.layouts.master')

@section('title', 'Chiến dịch giảm giá')
@section('page-title', 'Chiến dịch giảm giá')
@section('page-description', 'Quản lý chương trình giảm giá và flash sale theo sản phẩm.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Chiến dịch giảm giá</h2>
            <p class="mt-1 text-sm text-gray-500">Theo dõi thời gian, sản phẩm, số lượng bán và trạng thái chiến dịch.</p>
        </div>
        <a href="{{ route('admin.discount-campaigns.create') }}"
           class="inline-flex justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-pink-700">
            + Thêm chiến dịch
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ([
            ['Tổng chiến dịch', $statistics['total'], 'text-gray-900'],
            ['Đang hoạt động', $statistics['active'], 'text-green-600'],
            ['Sắp diễn ra', $statistics['scheduled'], 'text-blue-600'],
            ['Đã kết thúc', $statistics['ended'], 'text-red-600'],
            ['Flash sale', $statistics['flash_sale'], 'text-pink-600'],
            ['Đã bán', $statistics['sold_quantity'], 'text-orange-600'],
        ] as [$label, $value, $class])
            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <strong class="mt-2 block text-2xl {{ $class }}">{{ number_format($value) }}</strong>
            </article>
        @endforeach
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form method="GET" action="{{ route('admin.discount-campaigns.index') }}"
              class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="xl:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Tìm kiếm</label>
                <input type="text" name="keyword" value="{{ request('keyword') }}"
                       placeholder="Tên hoặc mô tả chiến dịch..."
                       class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Trạng thái</label>
                <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="1" @selected((string) request('status') === '1')>Đang bật</option>
                    <option value="0" @selected((string) request('status') === '0')>Đang tắt</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Flash sale</label>
                <select name="flash_sale" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="yes" @selected(request('flash_sale') === 'yes')>Có</option>
                    <option value="no" @selected(request('flash_sale') === 'no')>Không</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Thời kỳ</label>
                <select name="period" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('period') === 'active')>Đang hoạt động</option>
                    <option value="scheduled" @selected(request('period') === 'scheduled')>Sắp diễn ra</option>
                    <option value="ended" @selected(request('period') === 'ended')>Đã kết thúc</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Sắp xếp</label>
                <select name="sort" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Mới nhất</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                    <option value="name_asc" @selected(request('sort') === 'name_asc')>Tên A → Z</option>
                    <option value="name_desc" @selected(request('sort') === 'name_desc')>Tên Z → A</option>
                    <option value="start_desc" @selected(request('sort') === 'start_desc')>Bắt đầu mới nhất</option>
                    <option value="end_asc" @selected(request('sort') === 'end_asc')>Sắp kết thúc</option>
                    <option value="products_desc" @selected(request('sort') === 'products_desc')>Nhiều sản phẩm nhất</option>
                    <option value="sold_desc" @selected(request('sort') === 'sold_desc')>Bán nhiều nhất</option>
                </select>
            </div>
            <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-5">
                <button class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white">Lọc dữ liệu</button>
                <a href="{{ route('admin.discount-campaigns.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-lg font-bold text-gray-900">Danh sách chiến dịch</h3>
            <p class="mt-1 text-sm text-gray-500">Hiển thị {{ $campaigns->count() }} / {{ $campaigns->total() }} kết quả.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Chiến dịch</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Thời gian</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Sản phẩm</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Đã bán</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Hiệu lực</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($campaigns as $campaign)
                        @php
                            $scheduled = \Carbon\Carbon::parse($campaign->start_date)->isFuture();
                            $ended = \Carbon\Carbon::parse($campaign->end_date)->isPast();
                            $active = (int) $campaign->status === 1 && ! $scheduled && ! $ended;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="min-w-[260px]">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('admin.discount-campaigns.show', $campaign->id) }}" class="font-semibold text-gray-900 hover:text-pink-600">{{ $campaign->name }}</a>
                                        @if ((int) $campaign->is_flash_sale === 1)
                                            <span class="rounded-full bg-pink-100 px-2.5 py-1 text-xs font-semibold text-pink-700">Flash sale</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 max-w-sm truncate text-xs text-gray-500">{{ $campaign->description ?: 'Không có mô tả' }}</p>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600">
                                <p>{{ \Carbon\Carbon::parse($campaign->start_date)->format('d/m/Y H:i') }}</p>
                                <p class="mt-1">{{ \Carbon\Carbon::parse($campaign->end_date)->format('d/m/Y H:i') }}</p>
                            </td>
                            <td class="px-5 py-4 text-center font-semibold text-blue-600">{{ number_format((int) $campaign->products_count) }}</td>
                            <td class="px-5 py-4 text-center font-semibold text-orange-600">{{ number_format((int) $campaign->sold_quantity) }}</td>
                            <td class="px-5 py-4 text-center">
                                @if ((int) $campaign->status !== 1)
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Đang tắt</span>
                                @elseif ($scheduled)
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">Sắp diễn ra</span>
                                @elseif ($ended)
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Đã kết thúc</span>
                                @elseif ($active)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Đang hoạt động</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex min-w-[240px] justify-end gap-2">
                                    <a href="{{ route('admin.discount-campaigns.show', $campaign->id) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700">CHI TIẾT</a>
                                    <a href="{{ route('admin.discount-campaigns.edit', $campaign->id) }}" class="rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 text-xs font-bold text-orange-700">SỬA</a>
                                    <form method="POST" action="{{ route('admin.discount-campaigns.destroy', $campaign->id) }}" onsubmit="return confirm('Bạn chắc chắn muốn xóa chiến dịch này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-600">XÓA</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-16 text-center text-gray-500">Không tìm thấy chiến dịch.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($campaigns->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">{{ $campaigns->links() }}</div>
        @endif
    </section>
</div>
@endsection
