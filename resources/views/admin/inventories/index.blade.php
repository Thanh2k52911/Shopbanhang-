@extends('admin.layouts.master')

@section('title', 'Quản lý tồn kho')
@section('page-title', 'Quản lý tồn kho')
@section('page-description', 'Theo dõi tồn kho theo SKU, cảnh báo tồn thấp và thao tác nghiệp vụ kho.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Tồn kho
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Quản lý số lượng tồn, hàng đang giữ, tồn khả dụng và cảnh báo theo SKU.
            </p>
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

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-7">
        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Dòng tồn kho</p>

            <strong class="mt-2 block text-2xl text-indigo-600">
                {{ number_format($statistics['inventory_rows']) }}
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

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Đã bán</p>

            <strong class="mt-2 block text-2xl text-green-600">
                {{ number_format($statistics['sold_quantity']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Tồn thấp</p>

            <strong class="mt-2 block text-2xl text-yellow-600">
                {{ number_format($statistics['low_stock_rows']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Hết hàng</p>

            <strong class="mt-2 block text-2xl text-red-600">
                {{ number_format($statistics['out_of_stock_rows']) }}
            </strong>
        </article>
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form
            method="GET"
            action="{{ route('admin.inventories.index') }}"
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
                    placeholder="Sản phẩm, SKU, barcode, biến thể hoặc kho..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label for="warehouse_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Kho hàng
                </label>

                <select
                    id="warehouse_id"
                    name="warehouse_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả kho</option>

                    @foreach ($warehouses as $warehouse)
                        <option
                            value="{{ $warehouse->id }}"
                            @selected((string) request('warehouse_id') === (string) $warehouse->id)
                        >
                            {{ $warehouse->name }}
                            {{ (int) $warehouse->status === 1 ? '' : ' (Ngừng hoạt động)' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="sku_status" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Trạng thái SKU
                </label>

                <select
                    id="sku_status"
                    name="sku_status"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả trạng thái</option>
                    <option value="1" @selected((string) request('sku_status') === '1')>
                        Đang hoạt động
                    </option>
                    <option value="0" @selected((string) request('sku_status') === '0')>
                        Ngừng hoạt động
                    </option>
                </select>
            </div>

            <div>
                <label for="stock_status" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Tình trạng tồn
                </label>

                <select
                    id="stock_status"
                    name="stock_status"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả</option>
                    <option value="in_stock" @selected(request('stock_status') === 'in_stock')>
                        Còn hàng
                    </option>
                    <option value="low_stock" @selected(request('stock_status') === 'low_stock')>
                        Tồn thấp
                    </option>
                    <option value="out_of_stock" @selected(request('stock_status') === 'out_of_stock')>
                        Hết hàng
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
                    <option value="">Ưu tiên cảnh báo</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                    <option value="product_asc" @selected(request('sort') === 'product_asc')>Sản phẩm A → Z</option>
                    <option value="product_desc" @selected(request('sort') === 'product_desc')>Sản phẩm Z → A</option>
                    <option value="quantity_desc" @selected(request('sort') === 'quantity_desc')>Tổng tồn cao nhất</option>
                    <option value="available_asc" @selected(request('sort') === 'available_asc')>Khả dụng thấp nhất</option>
                    <option value="available_desc" @selected(request('sort') === 'available_desc')>Khả dụng cao nhất</option>
                    <option value="sold_desc" @selected(request('sort') === 'sold_desc')>Đã bán nhiều nhất</option>
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
                    href="{{ route('admin.inventories.index') }}"
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
                    Danh sách tồn kho
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Hiển thị {{ number_format($inventories->count()) }}
                    trên tổng {{ number_format($inventories->total()) }} kết quả.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Sản phẩm / SKU
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Kho hàng
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Tổng tồn
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Đang giữ
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Khả dụng
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Đã bán
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Tối thiểu
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Tình trạng
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Thao tác
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($inventories as $inventory)
                        @php
                            $availableQuantity = max(
                                0,
                                (int) $inventory->available_quantity
                            );

                            $isOutOfStock = $availableQuantity <= 0;

                            $isLowStock = ! $isOutOfStock
                                && $availableQuantity
                                    <= (int) $inventory->minimum_stock;
                        @endphp

                        <tr class="transition hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="min-w-[330px]">
                                    <a
                                        href="{{ route('admin.products.show', $inventory->product_id) }}"
                                        class="block truncate font-semibold text-gray-900 transition hover:text-pink-600"
                                        title="{{ $inventory->product_name }}"
                                    >
                                        {{ $inventory->product_name }}
                                    </a>

                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                        <span>
                                            SKU:
                                            <strong class="text-gray-700">
                                                {{ $inventory->sku_code }}
                                            </strong>
                                        </span>

                                        @if ($inventory->barcode)
                                            <span>
                                                Barcode:
                                                <strong class="text-gray-700">
                                                    {{ $inventory->barcode }}
                                                </strong>
                                            </span>
                                        @endif
                                    </div>

                                    @if ($inventory->variant_name)
                                        <p class="mt-1 text-xs text-gray-400">
                                            Biến thể: {{ $inventory->variant_name }}
                                        </p>
                                    @endif

                                    <p class="mt-1 text-xs text-gray-400">
                                        Giá:
                                        {{ number_format((float) $inventory->price, 0, ',', '.') }}đ
                                    </p>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="min-w-[190px]">
                                    <a
                                        href="{{ route('admin.warehouses.show', $inventory->warehouse_id) }}"
                                        class="font-semibold text-gray-900 transition hover:text-pink-600"
                                    >
                                        {{ $inventory->warehouse_name }}
                                    </a>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ (int) $inventory->warehouse_status === 1
                                            ? 'Đang hoạt động'
                                            : 'Ngừng hoạt động' }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="font-semibold text-blue-700">
                                    {{ number_format((int) $inventory->quantity) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="font-semibold text-orange-600">
                                    {{ number_format((int) $inventory->reserved_quantity) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="font-semibold text-pink-600">
                                    {{ number_format($availableQuantity) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="font-semibold text-green-700">
                                    {{ number_format((int) $inventory->sold_quantity) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                {{ number_format((int) $inventory->minimum_stock) }}
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if ($isOutOfStock)
                                    <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                        Hết hàng
                                    </span>
                                @elseif ($isLowStock)
                                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">
                                        Tồn thấp
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        Ổn định
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <a
                                    href="{{ route('admin.inventories.show', $inventory->id) }}"
                                    class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-xs font-bold text-gray-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700"
                                >
                                    CHI TIẾT
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-16 text-center text-gray-500">
                                <div class="mx-auto max-w-md">
                                    <p class="text-lg font-semibold text-gray-700">
                                        Không tìm thấy dữ liệu tồn kho
                                    </p>

                                    <p class="mt-2 text-sm text-gray-500">
                                        Thử thay đổi từ khóa hoặc bộ lọc để xem dữ liệu khác.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inventories->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $inventories->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
