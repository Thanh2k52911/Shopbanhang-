@extends('admin.layouts.master')

@section('title', 'Chi tiết kho hàng')
@section('page-title', 'Chi tiết kho hàng')
@section('page-description', 'Theo dõi tồn kho theo SKU, cảnh báo tồn thấp và hoạt động liên quan.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex min-w-0 items-center gap-4">
            <a
                href="{{ route('admin.warehouses.index') }}"
                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white text-xl text-gray-600 transition hover:bg-gray-50"
                title="Quay lại"
            >
                ←
            </a>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h2
                        class="max-w-4xl truncate text-2xl font-bold text-gray-900"
                        title="{{ $warehouse->name }}"
                    >
                        {{ $warehouse->name }}
                    </h2>

                    @if ((int) $warehouse->status === 1)
                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                            Đang hoạt động
                        </span>
                    @else
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                            Ngừng hoạt động
                        </span>
                    @endif
                </div>

                <p class="mt-1 text-sm text-gray-500">
                    ID: {{ $warehouse->id }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('admin.warehouses.edit', $warehouse->id) }}"
                class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700"
            >
                Chỉnh sửa
            </a>

            <form
                method="POST"
                action="{{ route('admin.warehouses.destroy', $warehouse->id) }}"
                onsubmit="return confirm('Bạn chắc chắn muốn xóa kho này?');"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100"
                >
                    Xóa kho
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

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
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
            <p class="text-sm text-gray-500">Tồn thấp</p>

            <strong class="mt-2 block text-2xl text-red-600">
                {{ number_format($statistics['low_stock_rows']) }}
            </strong>
        </article>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <div class="space-y-6">
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Thông tin kho
                    </h3>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-gray-500">
                            Tên kho
                        </p>

                        <strong class="mt-1 block text-gray-900">
                            {{ $warehouse->name }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Trạng thái
                        </p>

                        <strong class="mt-1 block text-gray-900">
                            {{ (int) $warehouse->status === 1
                                ? 'Đang hoạt động'
                                : 'Ngừng hoạt động' }}
                        </strong>
                    </div>

                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500">
                            Địa chỉ
                        </p>

                        <p class="mt-1 whitespace-pre-line font-medium text-gray-900">
                            {{ $warehouse->address ?: 'Chưa cập nhật' }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Tồn kho theo SKU
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Danh sách tồn kho chi tiết của từng SKU trong kho.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Sản phẩm / SKU
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
                                    Cảnh báo
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

                                    $isLowStock =
                                        $availableQuantity
                                        <= (int) $inventory->minimum_stock;
                                @endphp

                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <div class="min-w-[300px]">
                                            <a
                                                href="{{ route('admin.products.show', $inventory->product_id) }}"
                                                class="block font-semibold text-gray-900 transition hover:text-pink-600"
                                            >
                                                {{ $inventory->product_name }}
                                            </a>

                                            <p class="mt-1 text-xs text-gray-500">
                                                SKU:
                                                <span class="font-semibold text-gray-700">
                                                    {{ $inventory->sku_code }}
                                                </span>
                                            </p>

                                            @if ($inventory->variant_name)
                                                <p class="mt-1 text-xs text-gray-400">
                                                    Biến thể: {{ $inventory->variant_name }}
                                                </p>
                                            @endif
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
                                        {{ number_format((int) $inventory->sold_quantity) }}
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        {{ number_format((int) $inventory->minimum_stock) }}
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        @if ($isLowStock)
                                            <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                                Tồn thấp
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                Ổn định
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="7"
                                        class="px-5 py-12 text-center text-gray-500"
                                    >
                                        Kho chưa có dữ liệu tồn kho.
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

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Hoạt động liên quan
                </h3>

                <dl class="mt-4 space-y-4">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-gray-500">
                            Giao dịch kho
                        </dt>

                        <dd class="font-semibold text-gray-900">
                            {{ number_format($statistics['transactions']) }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-gray-500">
                            Đơn hàng
                        </dt>

                        <dd class="font-semibold text-gray-900">
                            {{ number_format($statistics['orders']) }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-gray-500">
                            Vận chuyển
                        </dt>

                        <dd class="font-semibold text-gray-900">
                            {{ number_format($statistics['shipments']) }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-gray-500">
                            Đã bán
                        </dt>

                        <dd class="font-semibold text-gray-900">
                            {{ number_format($statistics['sold_quantity']) }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Trạng thái kho
                </h3>

                <div class="mt-4">
                    @if ((int) $warehouse->status === 1)
                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">
                            Đang hoạt động
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-600">
                            Ngừng hoạt động
                        </span>
                    @endif
                </div>
            </section>

            <section class="rounded-xl border border-orange-200 bg-orange-50 p-5">
                <h3 class="font-bold text-orange-900">
                    Lưu ý
                </h3>

                <p class="mt-3 text-sm leading-6 text-orange-800">
                    Kho đã có tồn kho, giao dịch kho, đơn hàng hoặc vận chuyển sẽ không thể xóa. Khi ngừng sử dụng, nên chuyển trạng thái sang ngừng hoạt động.
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
                            {{ \Carbon\Carbon::parse($warehouse->created_at)->format('d/m/Y H:i') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Cập nhật gần nhất
                        </dt>

                        <dd class="mt-1 font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($warehouse->updated_at)->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>
</div>
@endsection
