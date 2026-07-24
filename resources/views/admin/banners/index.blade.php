@extends('admin.layouts.master')

@section('title', 'Quản lý banner')
@section('page-title', 'Quản lý banner')
@section('page-description', 'Theo dõi vị trí, lịch hiển thị và trạng thái banner.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Banner
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Quản lý banner desktop, mobile, liên kết và lịch hiển thị.
            </p>
        </div>

        <a
            href="{{ route('admin.banners.create') }}"
            class="inline-flex justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-pink-700"
        >
            + Thêm banner
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
        @foreach ([
            ['Tổng banner', $statistics['total'], 'text-gray-900'],
            ['Đang hoạt động', $statistics['active'], 'text-green-600'],
            ['Sắp hiển thị', $statistics['scheduled'], 'text-blue-600'],
            ['Đã hết hạn', $statistics['expired'], 'text-red-600'],
            ['Slider trang chủ', $statistics['home_slider'], 'text-pink-600'],
            ['Popup', $statistics['popup'], 'text-orange-600'],
        ] as [$label, $value, $class])
            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <strong class="mt-2 block text-2xl {{ $class }}">
                    {{ number_format($value) }}
                </strong>
            </article>
        @endforeach
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form
            method="GET"
            action="{{ route('admin.banners.index') }}"
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
                    placeholder="Tên, tiêu đề, phụ đề hoặc liên kết..."
                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Vị trí
                </label>

                <select
                    name="position"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả</option>

                    @foreach ($positions as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected(request('position') === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Trạng thái
                </label>

                <select
                    name="status"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả</option>
                    <option value="1" @selected((string) request('status') === '1')>Đang bật</option>
                    <option value="0" @selected((string) request('status') === '0')>Đang tắt</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Hiệu lực
                </label>

                <select
                    name="validity"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('validity') === 'active')>Đang hoạt động</option>
                    <option value="scheduled" @selected(request('validity') === 'scheduled')>Sắp hiển thị</option>
                    <option value="expired" @selected(request('validity') === 'expired')>Đã hết hạn</option>
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
                    <option value="">Thứ tự hiển thị</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                    <option value="name_asc" @selected(request('sort') === 'name_asc')>Tên A → Z</option>
                    <option value="name_desc" @selected(request('sort') === 'name_desc')>Tên Z → A</option>
                    <option value="sort_asc" @selected(request('sort') === 'sort_asc')>Thứ tự tăng</option>
                    <option value="sort_desc" @selected(request('sort') === 'sort_desc')>Thứ tự giảm</option>
                    <option value="start_desc" @selected(request('sort') === 'start_desc')>Bắt đầu mới nhất</option>
                    <option value="end_asc" @selected(request('sort') === 'end_asc')>Sắp hết hạn</option>
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
                    href="{{ route('admin.banners.index') }}"
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
                Danh sách banner
            </h3>

            <p class="mt-1 text-sm text-gray-500">
                Hiển thị {{ $banners->count() }} / {{ $banners->total() }} kết quả.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Banner</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Vị trí</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Thời gian</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Thứ tự</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Hiệu lực</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($banners as $banner)
                        @php
                            $scheduled = $banner->start_at
                                && \Carbon\Carbon::parse($banner->start_at)->isFuture();

                            $expired = $banner->end_at
                                && \Carbon\Carbon::parse($banner->end_at)->isPast();

                            $active = (int) $banner->status === 1
                                && ! $scheduled
                                && ! $expired;
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="flex min-w-[360px] items-center gap-4">
                                    <img
                                        src="{{ asset('storage/' . ltrim($banner->desktop_image, '/')) }}"
                                        alt="{{ $banner->name }}"
                                        class="h-16 w-28 rounded-lg border border-gray-200 object-cover"
                                    >

                                    <div class="min-w-0">
                                        <a
                                            href="{{ route('admin.banners.show', $banner->id) }}"
                                            class="block truncate font-semibold text-gray-900 hover:text-pink-600"
                                        >
                                            {{ $banner->name }}
                                        </a>

                                        <p class="mt-1 max-w-xs truncate text-xs text-gray-500">
                                            {{ $banner->title ?: 'Không có tiêu đề' }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-400">
                                            Người tạo: {{ $banner->creator_name ?: 'Hệ thống' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-700">
                                {{ $positions[$banner->position] ?? $banner->position }}
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-600">
                                <p>
                                    {{ $banner->start_at
                                        ? \Carbon\Carbon::parse($banner->start_at)->format('d/m/Y H:i')
                                        : 'Không giới hạn' }}
                                </p>

                                <p class="mt-1">
                                    {{ $banner->end_at
                                        ? \Carbon\Carbon::parse($banner->end_at)->format('d/m/Y H:i')
                                        : 'Không giới hạn' }}
                                </p>
                            </td>

                            <td class="px-5 py-4 text-center font-semibold text-gray-800">
                                {{ number_format((int) $banner->sort_order) }}
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if ((int) $banner->status !== 1)
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Đang tắt</span>
                                @elseif ($scheduled)
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">Sắp hiển thị</span>
                                @elseif ($expired)
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Đã hết hạn</span>
                                @elseif ($active)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Đang hoạt động</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex min-w-[240px] justify-end gap-2">
                                    <a
                                        href="{{ route('admin.banners.show', $banner->id) }}"
                                        class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700"
                                    >
                                        CHI TIẾT
                                    </a>

                                    <a
                                        href="{{ route('admin.banners.edit', $banner->id) }}"
                                        class="rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 text-xs font-bold text-orange-700"
                                    >
                                        SỬA
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.banners.destroy', $banner->id) }}"
                                        onsubmit="return confirm('Bạn chắc chắn muốn xóa banner này?');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-600"
                                        >
                                            XÓA
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center text-gray-500">
                                Không tìm thấy banner.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($banners->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $banners->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
