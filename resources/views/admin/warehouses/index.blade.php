@extends('admin.layouts.master')

@section('title', 'Quản lý kho hàng')
@section('page-title', 'Quản lý kho hàng')
@section('page-description', 'Theo dõi trạng thái kho, tổng tồn, hàng đang giữ và tồn khả dụng.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Kho hàng</h2>
            <p class="mt-1 text-sm text-gray-500">
                Quản lý thông tin kho, trạng thái hoạt động và số lượng tồn kho.
            </p>
        </div>

        <a href="{{ route('admin.warehouses.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700">
            + Thêm kho hàng
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
            <p class="text-sm text-gray-500">Tổng kho</p>
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
            <p class="text-sm text-gray-500">Ngừng hoạt động</p>
            <strong class="mt-2 block text-2xl text-gray-500">
                {{ number_format($statistics['inactive']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Tổng tồn</p>
            <strong class="mt-2 block text-2xl text-blue-600">
                {{ number_format($statistics['total_quantity']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Đang giữ</p>
            <strong class="mt-2 block text-2xl text-orange-600">
                {{ number_format($statistics['reserved_quantity']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Khả dụng</p>
            <strong class="mt-2 block text-2xl text-pink-600">
                {{ number_format($statistics['available_quantity']) }}
            </strong>
        </article>
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form method="GET"
              action="{{ route('admin.warehouses.index') }}"
              class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="xl:col-span-2">
                <label for="keyword" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Tìm kiếm
                </label>

                <input id="keyword"
                       type="text"
                       name="keyword"
                       value="{{ request('keyword') }}"
                       placeholder="Tên kho hoặc địa chỉ..."
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
            </div>

            <div>
                <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Trạng thái
                </label>

                <select id="status"
                        name="status"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1" @selected((string) request('status') === '1')>Đang hoạt động</option>
                    <option value="0" @selected((string) request('status') === '0')>Ngừng hoạt động</option>
                </select>
            </div>

            <div>
                <label for="sort" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Sắp xếp
                </label>

                <select id="sort"
                        name="sort"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Mới nhất</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                    <option value="name_asc" @selected(request('sort') === 'name_asc')>Tên A → Z</option>
                    <option value="name_desc" @selected(request('sort') === 'name_desc')>Tên Z → A</option>
                    <option value="quantity_desc" @selected(request('sort') === 'quantity_desc')>Tổng tồn cao nhất</option>
                    <option value="available_desc" @selected(request('sort') === 'available_desc')>Khả dụng cao nhất</option>
                </select>
            </div>

            <div class="flex flex-wrap items-end gap-3 md:col-span-2 xl:col-span-4">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800">
                    Lọc dữ liệu
                </button>

                <a href="{{ route('admin.warehouses.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Đặt lại
                </a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="flex flex-col gap-2 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">
                    Danh sách kho hàng
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Hiển thị {{ number_format($warehouses->count()) }}
                    trên tổng {{ number_format($warehouses->total()) }} kết quả.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Kho hàng</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Dòng tồn kho</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Tổng tồn</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Đang giữ</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Khả dụng</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Trạng thái</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($warehouses as $warehouse)
                        @php
                            $availableQuantity = max(
                                0,
                                (int) $warehouse->available_quantity
                            );
                        @endphp

                        <tr class="transition hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="min-w-[320px]">
                                    <a href="{{ route('admin.warehouses.show', $warehouse->id) }}"
                                       class="block truncate font-semibold text-gray-900 transition hover:text-pink-600"
                                       title="{{ $warehouse->name }}">
                                        {{ $warehouse->name }}
                                    </a>

                                    <p class="mt-1 max-w-lg truncate text-xs text-gray-500"
                                       title="{{ $warehouse->address }}">
                                        {{ $warehouse->address ?: 'Chưa cập nhật địa chỉ' }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex min-w-10 justify-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                    {{ number_format((int) $warehouse->inventory_rows_count) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="font-semibold text-blue-700">
                                    {{ number_format((int) $warehouse->total_quantity) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="font-semibold text-orange-600">
                                    {{ number_format((int) $warehouse->reserved_quantity) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="font-semibold text-pink-600">
                                    {{ number_format($availableQuantity) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if ((int) $warehouse->status === 1)
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                        Ngừng hoạt động
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex min-w-[245px] items-center justify-end gap-2">
                                    <a href="{{ route('admin.warehouses.show', $warehouse->id) }}"
                                       class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-xs font-bold text-gray-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">
                                        CHI TIẾT
                                    </a>

                                    <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}"
                                       class="inline-flex h-9 items-center justify-center rounded-lg border border-orange-200 bg-orange-50 px-3 text-xs font-bold text-orange-700 transition hover:bg-orange-100">
                                        SỬA
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.warehouses.destroy', $warehouse->id) }}"
                                          class="inline-flex"
                                          onsubmit="return confirm('Bạn chắc chắn muốn xóa kho này?');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="inline-flex h-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-bold text-red-600 transition hover:bg-red-100">
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
                                        Không tìm thấy kho hàng
                                    </p>

                                    <p class="mt-2 text-sm text-gray-500">
                                        Thử thay đổi từ khóa hoặc bộ lọc, hoặc tạo kho mới.
                                    </p>

                                    <a href="{{ route('admin.warehouses.create') }}"
                                       class="mt-4 inline-flex rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700">
                                        Thêm kho hàng
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($warehouses->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $warehouses->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
