@extends('admin.layouts.master')

@section('title', 'Chi tiết danh mục')
@section('page-title', 'Chi tiết danh mục')
@section('page-description', 'Theo dõi thông tin, danh mục con và sản phẩm thuộc danh mục.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex min-w-0 items-center gap-4">
            <a href="{{ route('admin.categories.index') }}"
               class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white text-xl text-gray-600 transition hover:bg-gray-50"
               title="Quay lại">
                ←
            </a>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="max-w-4xl truncate text-2xl font-bold text-gray-900"
                        title="{{ $category->name }}">
                        {{ $category->name }}
                    </h2>

                    @if ((int) $category->status === 1)
                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                            Đang hiển thị
                        </span>
                    @else
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                            Đang ẩn
                        </span>
                    @endif
                </div>

                <p class="mt-1 text-sm text-gray-500">
                    ID: {{ $category->id }} · {{ $category->slug }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.categories.edit', $category->id) }}"
               class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700">
                Chỉnh sửa
            </a>

            <form method="POST"
                  action="{{ route('admin.categories.destroy', $category->id) }}"
                  onsubmit="return confirm('Bạn chắc chắn muốn xóa danh mục này?');">
                @csrf
                @method('DELETE')

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                    Xóa danh mục
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

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Danh mục con</p>
            <strong class="mt-2 block text-2xl text-blue-600">
                {{ number_format($statistics['children']) }}
            </strong>
            <p class="mt-1 text-xs text-gray-400">
                {{ number_format($statistics['active_children']) }} đang hiển thị
            </p>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Sản phẩm</p>
            <strong class="mt-2 block text-2xl text-pink-600">
                {{ number_format($statistics['products']) }}
            </strong>
            <p class="mt-1 text-xs text-gray-400">
                {{ number_format($statistics['active_products']) }} đang bán
            </p>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Thứ tự hiển thị</p>
            <strong class="mt-2 block text-2xl text-indigo-600">
                {{ number_format((int) $category->sort_order) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Loại danh mục</p>
            <strong class="mt-2 block text-lg text-gray-900">
                {{ $category->parent_id ? 'Danh mục con' : 'Danh mục gốc' }}
            </strong>
        </article>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <div class="space-y-6">
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-900">Thông tin danh mục</h3>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-gray-500">Tên danh mục</p>
                        <strong class="mt-1 block text-gray-900">
                            {{ $category->name }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Slug</p>
                        <strong class="mt-1 block break-all text-gray-900">
                            {{ $category->slug }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Danh mục cha</p>
                        <strong class="mt-1 block text-gray-900">
                            {{ $category->parent_name ?: 'Không có' }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Trạng thái</p>
                        <strong class="mt-1 block text-gray-900">
                            {{ (int) $category->status === 1 ? 'Hiển thị' : 'Ẩn' }}
                        </strong>
                    </div>
                </div>

                @if ($category->description)
                    <div class="border-t border-gray-200 px-5 py-4">
                        <p class="text-sm text-gray-500">Mô tả</p>
                        <p class="mt-2 whitespace-pre-line text-gray-800">
                            {{ $category->description }}
                        </p>
                    </div>
                @endif
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Danh mục con</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ number_format($children->count()) }} danh mục.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Danh mục</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Thứ tự</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Trạng thái</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Thao tác</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($children as $child)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                                                @if ($child->thumbnail)
                                                    <img src="{{ asset('storage/' . ltrim($child->thumbnail, '/')) }}"
                                                         alt="{{ $child->name }}"
                                                         class="h-full w-full object-cover">
                                                @else
                                                    <div class="grid h-full w-full place-items-center bg-pink-50 font-bold text-pink-600">
                                                        {{ mb_strtoupper(mb_substr($child->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="min-w-0">
                                                <a href="{{ route('admin.categories.show', $child->id) }}"
                                                   class="block truncate font-semibold text-gray-900 hover:text-pink-600">
                                                    {{ $child->name }}
                                                </a>
                                                <p class="mt-1 truncate text-xs text-gray-500">
                                                    {{ $child->slug }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        {{ number_format((int) $child->sort_order) }}
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        @if ((int) $child->status === 1)
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                Hiển thị
                                            </span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                                Ẩn
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('admin.categories.edit', $child->id) }}"
                                           class="inline-flex rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 text-xs font-bold text-orange-700 hover:bg-orange-100">
                                            SỬA
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-12 text-center text-gray-500">
                                        Danh mục chưa có danh mục con.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-900">Sản phẩm thuộc danh mục</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Hiển thị tối đa 20 sản phẩm mới nhất.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Sản phẩm</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Lượt xem</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Trạng thái</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Thao tác</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($products as $product)
                                <tr>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('admin.products.show', $product->id) }}"
                                           class="font-semibold text-gray-900 hover:text-pink-600">
                                            {{ $product->name }}
                                        </a>

                                        @if ((bool) $product->is_featured)
                                            <span class="ml-2 rounded-full bg-pink-100 px-2 py-1 text-xs font-semibold text-pink-700">
                                                Nổi bật
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        {{ number_format((int) $product->view_count) }}
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        @if ((int) $product->status === 1)
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                Đang bán
                                            </span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                                Ngừng bán
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('admin.products.show', $product->id) }}"
                                           class="inline-flex rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">
                                            CHI TIẾT
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-12 text-center text-gray-500">
                                        Danh mục chưa có sản phẩm.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-900">Thumbnail</h3>
                </div>

                <div class="p-5">
                    @if ($category->thumbnail)
                        <img src="{{ asset('storage/' . ltrim($category->thumbnail, '/')) }}"
                             alt="{{ $category->name }}"
                             class="aspect-square w-full rounded-xl border border-gray-200 object-cover">
                    @else
                        <div class="grid aspect-square w-full place-items-center rounded-xl border border-dashed border-gray-300 bg-gray-50 text-center text-gray-500">
                            Chưa có thumbnail
                        </div>
                    @endif
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Thời gian</h3>

                <dl class="mt-4 space-y-4">
                    <div>
                        <dt class="text-sm text-gray-500">Ngày tạo</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($category->created_at)->format('d/m/Y H:i') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Cập nhật gần nhất</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($category->updated_at)->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>
</div>
@endsection
