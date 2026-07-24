@extends('admin.layouts.master')

@section('title', 'Quản lý thương hiệu')
@section('page-title', 'Quản lý thương hiệu')
@section('page-description', 'Theo dõi, tìm kiếm và quản lý toàn bộ thương hiệu sản phẩm.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Thương hiệu</h2>
            <p class="mt-1 text-sm text-gray-500">
                Quản lý thông tin thương hiệu, quốc gia, website và trạng thái hiển thị.
            </p>
        </div>

        <a
            href="{{ route('admin.brands.create') }}"
            class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700"
        >
            + Thêm thương hiệu
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

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Tổng thương hiệu</p>
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
            <p class="text-sm text-gray-500">Đang ẩn</p>
            <strong class="mt-2 block text-2xl text-gray-500">
                {{ number_format($statistics['inactive']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Có sản phẩm</p>
            <strong class="mt-2 block text-2xl text-pink-600">
                {{ number_format($statistics['with_products']) }}
            </strong>
        </article>
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form
            method="GET"
            action="{{ route('admin.brands.index') }}"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5"
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
                    placeholder="Tên, slug, quốc gia hoặc website..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
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
                    <option value="1" @selected((string) request('status') === '1')>Đang hoạt động</option>
                    <option value="0" @selected((string) request('status') === '0')>Đang ẩn</option>
                </select>
            </div>

            <div>
                <label for="country" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Quốc gia
                </label>

                <select
                    id="country"
                    name="country"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả quốc gia</option>

                    @foreach ($countries as $country)
                        <option
                            value="{{ $country }}"
                            @selected((string) request('country') === (string) $country)
                        >
                            {{ $country }}
                        </option>
                    @endforeach
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
                    <option value="">Mặc định</option>
                    <option value="name_asc" @selected(request('sort') === 'name_asc')>Tên A → Z</option>
                    <option value="name_desc" @selected(request('sort') === 'name_desc')>Tên Z → A</option>
                    <option value="sort_asc" @selected(request('sort') === 'sort_asc')>Thứ tự tăng dần</option>
                    <option value="sort_desc" @selected(request('sort') === 'sort_desc')>Thứ tự giảm dần</option>
                    <option value="products_desc" @selected(request('sort') === 'products_desc')>Nhiều sản phẩm nhất</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                </select>
            </div>

            <div class="flex flex-wrap items-end gap-3 md:col-span-2 xl:col-span-5">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800"
                >
                    Lọc dữ liệu
                </button>

                <a
                    href="{{ route('admin.brands.index') }}"
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
                <h3 class="text-lg font-bold text-gray-900">Danh sách thương hiệu</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Hiển thị {{ number_format($brands->count()) }} trên tổng
                    {{ number_format($brands->total()) }} kết quả.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Thương hiệu</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Quốc gia</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Website</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Sản phẩm</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Thứ tự</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Trạng thái</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($brands as $brand)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="flex min-w-[280px] items-center gap-4">
                                    <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                        @if ($brand->thumbnail)
                                            <img
                                                src="{{ asset('storage/' . ltrim($brand->thumbnail, '/')) }}"
                                                alt="{{ $brand->name }}"
                                                class="h-full w-full object-cover"
                                            >
                                        @else
                                            <div class="grid h-full w-full place-items-center bg-pink-50 text-lg font-bold text-pink-600">
                                                {{ mb_strtoupper(mb_substr($brand->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <a
                                            href="{{ route('admin.brands.show', $brand->id) }}"
                                            class="block truncate font-semibold text-gray-900 transition hover:text-pink-600"
                                            title="{{ $brand->name }}"
                                        >
                                            {{ $brand->name }}
                                        </a>

                                        <p class="mt-1 max-w-xs truncate text-xs text-gray-500" title="{{ $brand->slug }}">
                                            {{ $brand->slug }}
                                        </p>

                                        @if ($brand->description)
                                            <p class="mt-1 max-w-sm truncate text-xs text-gray-400" title="{{ $brand->description }}">
                                                {{ $brand->description }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if ($brand->country)
                                    <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                        {{ $brand->country }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">Chưa cập nhật</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @if ($brand->website)
                                    <a
                                        href="{{ $brand->website }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="block max-w-[240px] truncate text-sm font-medium text-blue-600 hover:underline"
                                        title="{{ $brand->website }}"
                                    >
                                        {{ $brand->website }}
                                    </a>
                                @else
                                    <span class="text-sm text-gray-400">Chưa có</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex min-w-9 justify-center rounded-full bg-pink-50 px-3 py-1 text-xs font-semibold text-pink-700">
                                    {{ number_format((int) $brand->products_count) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="font-semibold text-gray-700">
                                    {{ number_format((int) $brand->sort_order) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if ((int) $brand->status === 1)
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                        Đang ẩn
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex min-w-[245px] items-center justify-end gap-2">
                                    <a
                                        href="{{ route('admin.brands.show', $brand->id) }}"
                                        class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-xs font-bold text-gray-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700"
                                    >
                                        CHI TIẾT
                                    </a>

                                    <a
                                        href="{{ route('admin.brands.edit', $brand->id) }}"
                                        class="inline-flex h-9 items-center justify-center rounded-lg border border-orange-200 bg-orange-50 px-3 text-xs font-bold text-orange-700 transition hover:bg-orange-100"
                                    >
                                        SỬA
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.brands.destroy', $brand->id) }}"
                                        class="inline-flex"
                                        onsubmit="return confirm('Bạn chắc chắn muốn xóa thương hiệu này?');"
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
                            <td colspan="7" class="px-5 py-16 text-center text-gray-500">
                                <div class="mx-auto max-w-md">
                                    <p class="text-lg font-semibold text-gray-700">
                                        Không tìm thấy thương hiệu
                                    </p>

                                    <p class="mt-2 text-sm text-gray-500">
                                        Thử thay đổi từ khóa hoặc bộ lọc, hoặc tạo thương hiệu mới.
                                    </p>

                                    <a
                                        href="{{ route('admin.brands.create') }}"
                                        class="mt-4 inline-flex rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700"
                                    >
                                        Thêm thương hiệu
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($brands->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $brands->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
