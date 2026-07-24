@extends('admin.layouts.master')

@section('title', 'Thống kê hệ thống')
@section('page-title', 'Thống kê hệ thống')
@section('page-description', 'Theo dõi doanh thu, đơn hàng, khách hàng, sản phẩm và tồn kho.')

@section('content')
@php
    $orderStatusLabels = [
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'processing' => 'Đang xử lý',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];

    $paymentStatusLabels = [
        'pending' => 'Chờ thanh toán',
        'processing' => 'Đang xử lý',
        'paid' => 'Đã thanh toán',
        'failed' => 'Thất bại',
        'cancelled' => 'Đã hủy',
        'refunded' => 'Đã hoàn tiền',
        'partially_refunded' => 'Hoàn một phần',
    ];

    $shippingStatusLabels = [
        'pending' => 'Chờ xử lý',
        'processing' => 'Đang chuẩn bị',
        'ready_to_ship' => 'Sẵn sàng giao',
        'picked_up' => 'Đã lấy hàng',
        'in_transit' => 'Đang vận chuyển',
        'out_for_delivery' => 'Đang giao',
        'shipping' => 'Đang giao',
        'delivered' => 'Đã giao',
        'failed' => 'Giao thất bại',
        'cancelled' => 'Đã hủy',
        'returned' => 'Đã hoàn trả',
    ];

    $changeClass = static function (float|int $value): string {
        if ($value > 0) {
            return 'text-green-600';
        }

        if ($value < 0) {
            return 'text-red-600';
        }

        return 'text-gray-500';
    };

    $changePrefix = static function (float|int $value): string {
        return $value > 0 ? '+' : '';
    };
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Báo cáo tổng hợp
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Dữ liệu từ
                <strong>{{ $dateFrom->format('d/m/Y') }}</strong>
                đến
                <strong>{{ $dateTo->format('d/m/Y') }}</strong>.
            </p>
        </div>

        <form
            method="GET"
            action="{{ route('admin.statistics.index') }}"
            class="grid grid-cols-1 gap-3 rounded-xl border border-gray-200 bg-white p-4 md:grid-cols-4"
        >
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-gray-600">
                    Khoảng thời gian
                </label>

                <select
                    name="range"
                    id="statistics-range"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="7_days" @selected($range === '7_days')>
                        7 ngày gần nhất
                    </option>

                    <option value="30_days" @selected($range === '30_days')>
                        30 ngày gần nhất
                    </option>

                    <option value="90_days" @selected($range === '90_days')>
                        90 ngày gần nhất
                    </option>

                    <option value="this_month" @selected($range === 'this_month')>
                        Tháng này
                    </option>

                    <option value="last_month" @selected($range === 'last_month')>
                        Tháng trước
                    </option>

                    <option value="this_year" @selected($range === 'this_year')>
                        Năm nay
                    </option>

                    <option value="custom" @selected($range === 'custom')>
                        Tùy chỉnh
                    </option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold text-gray-600">
                    Từ ngày
                </label>

                <input
                    type="date"
                    name="date_from"
                    value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}"
                    class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold text-gray-600">
                    Đến ngày
                </label>

                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to', $dateTo->format('Y-m-d')) }}"
                    class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div class="flex items-end">
                <button
                    type="submit"
                    class="w-full rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white"
                >
                    Cập nhật
                </button>
            </div>
        </form>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <ul class="list-disc space-y-1 pl-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Doanh thu hoàn thành
            </p>

            <strong class="mt-2 block text-2xl text-pink-600">
                {{ number_format((float) $overview['revenue'], 0, ',', '.') }}đ
            </strong>

            <p class="mt-2 text-xs {{ $changeClass($changes['revenue']) }}">
                {{ $changePrefix($changes['revenue']) }}{{ number_format((float) $changes['revenue'], 2) }}%
                so với kỳ trước
            </p>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Tổng đơn hàng
            </p>

            <strong class="mt-2 block text-2xl text-blue-600">
                {{ number_format((int) $overview['orders']) }}
            </strong>

            <p class="mt-2 text-xs {{ $changeClass($changes['orders']) }}">
                {{ $changePrefix($changes['orders']) }}{{ number_format((float) $changes['orders'], 2) }}%
                so với kỳ trước
            </p>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Đơn hoàn thành
            </p>

            <strong class="mt-2 block text-2xl text-green-600">
                {{ number_format((int) $overview['completed_orders']) }}
            </strong>

            <p class="mt-2 text-xs {{ $changeClass($changes['completed_orders']) }}">
                {{ $changePrefix($changes['completed_orders']) }}{{ number_format((float) $changes['completed_orders'], 2) }}%
                so với kỳ trước
            </p>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Sản phẩm đã bán
            </p>

            <strong class="mt-2 block text-2xl text-purple-600">
                {{ number_format((int) $overview['products_sold']) }}
            </strong>

            <p class="mt-2 text-xs {{ $changeClass($changes['products_sold']) }}">
                {{ $changePrefix($changes['products_sold']) }}{{ number_format((float) $changes['products_sold'], 2) }}%
                so với kỳ trước
            </p>
        </article>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Giá trị đơn trung bình
            </p>

            <strong class="mt-2 block text-xl text-orange-600">
                {{ number_format((float) $overview['average_order_value'], 0, ',', '.') }}đ
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Khách hàng mới
            </p>

            <strong class="mt-2 block text-xl text-cyan-600">
                {{ number_format((int) $overview['new_customers']) }}
            </strong>

            <p class="mt-2 text-xs {{ $changeClass($changes['new_customers']) }}">
                {{ $changePrefix($changes['new_customers']) }}{{ number_format((float) $changes['new_customers'], 2) }}%
                so với kỳ trước
            </p>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Đơn đã thanh toán
            </p>

            <strong class="mt-2 block text-xl text-green-600">
                {{ number_format((int) $overview['paid_orders']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Đơn đã hủy
            </p>

            <strong class="mt-2 block text-xl text-red-600">
                {{ number_format((int) $overview['cancelled_orders']) }}
            </strong>
        </article>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="rounded-xl border border-gray-200 bg-white p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">
                        Doanh thu theo ngày
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Chỉ tính các đơn hàng đã hoàn thành.
                    </p>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <div class="min-w-[680px]">
                    <canvas
                        id="revenue-chart"
                        height="120"
                    ></canvas>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6">
            <div>
                <h3 class="text-lg font-bold text-gray-900">
                    Số đơn theo ngày
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Tổng số đơn được tạo trong khoảng thời gian.
                </p>
            </div>

            <div class="mt-6 overflow-x-auto">
                <div class="min-w-[680px]">
                    <canvas
                        id="orders-chart"
                        height="120"
                    ></canvas>
                </div>
            </div>
        </section>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="rounded-xl border border-gray-200 bg-white p-5">
            <h3 class="text-lg font-bold text-gray-900">
                Trạng thái đơn hàng
            </h3>

            <div class="mt-5 space-y-3">
                @forelse ($orderStatusStatistics as $item)
                    <div class="flex items-center justify-between gap-4 rounded-lg bg-gray-50 px-4 py-3">
                        <span class="text-sm text-gray-600">
                            {{ $orderStatusLabels[$item->order_status] ?? $item->order_status }}
                        </span>

                        <strong class="text-gray-900">
                            {{ number_format((int) $item->total) }}
                        </strong>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">
                        Chưa có dữ liệu.
                    </p>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5">
            <h3 class="text-lg font-bold text-gray-900">
                Trạng thái thanh toán
            </h3>

            <div class="mt-5 space-y-3">
                @forelse ($paymentStatusStatistics as $item)
                    <div class="flex items-center justify-between gap-4 rounded-lg bg-gray-50 px-4 py-3">
                        <span class="text-sm text-gray-600">
                            {{ $paymentStatusLabels[$item->payment_status] ?? $item->payment_status }}
                        </span>

                        <strong class="text-gray-900">
                            {{ number_format((int) $item->total) }}
                        </strong>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">
                        Chưa có dữ liệu.
                    </p>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5">
            <h3 class="text-lg font-bold text-gray-900">
                Trạng thái vận chuyển
            </h3>

            <div class="mt-5 space-y-3">
                @forelse ($shippingStatusStatistics as $item)
                    <div class="flex items-center justify-between gap-4 rounded-lg bg-gray-50 px-4 py-3">
                        <span class="text-sm text-gray-600">
                            {{ $shippingStatusLabels[$item->shipping_status] ?? $item->shipping_status }}
                        </span>

                        <strong class="text-gray-900">
                            {{ number_format((int) $item->total) }}
                        </strong>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">
                        Chưa có dữ liệu.
                    </p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-5 py-4">
                <h3 class="text-lg font-bold text-gray-900">
                    Top sản phẩm
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Xếp theo doanh thu và số lượng đã bán.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                Sản phẩm
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                Đã bán
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                Doanh thu
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($topProducts as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="flex min-w-[260px] items-center gap-3">
                                        @if (! empty($product->image_path))
                                            <img
                                                src="{{ asset('storage/' . ltrim((string) $product->image_path, '/')) }}"
                                                alt="{{ $product->name }}"
                                                class="h-12 w-12 rounded-lg border border-gray-200 object-cover"
                                            >
                                        @else
                                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400">
                                                No image
                                            </div>
                                        @endif

                                        <div>
                                            <a
                                                href="{{ route('admin.products.show', $product->id) }}"
                                                class="font-semibold text-gray-900 hover:text-pink-600"
                                            >
                                                {{ $product->name }}
                                            </a>

                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ number_format((int) ($product->views ?? 0)) }} lượt xem ·
                                                {{ number_format((int) ($product->favorites ?? 0)) }} yêu thích
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-right font-semibold text-purple-600">
                                    {{ number_format((int) ($product->sold_quantity ?? 0)) }}
                                </td>

                                <td class="px-5 py-4 text-right font-semibold text-pink-600">
                                    {{ number_format((float) ($product->revenue ?? 0), 0, ',', '.') }}đ
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-12 text-center text-gray-500">
                                    Chưa có dữ liệu sản phẩm.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-5 py-4">
                <h3 class="text-lg font-bold text-gray-900">
                    Top khách hàng
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Xếp theo tổng chi tiêu trong kỳ.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                Khách hàng
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                Đơn hoàn thành
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                Chi tiêu
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($topCustomers as $customer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <a
                                        href="{{ route('admin.customers.show', $customer->id) }}"
                                        class="font-semibold text-gray-900 hover:text-pink-600"
                                    >
                                        {{ $customer->name }}
                                    </a>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $customer->email }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-right font-semibold text-blue-600">
                                    {{ number_format((int) $customer->completed_orders) }}
                                </td>

                                <td class="px-5 py-4 text-right font-semibold text-pink-600">
                                    {{ number_format((float) $customer->spending, 0, ',', '.') }}đ
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-12 text-center text-gray-500">
                                    Chưa có dữ liệu khách hàng.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
        <section class="rounded-xl border border-gray-200 bg-white p-5">
            <h3 class="text-lg font-bold text-gray-900">
                Tổng quan tồn kho
            </h3>

            <dl class="mt-5 space-y-4 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Tổng số lượng</dt>
                    <dd class="font-semibold text-gray-900">
                        {{ number_format((int) $inventoryStatistics['quantity']) }}
                    </dd>
                </div>

                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Đang giữ</dt>
                    <dd class="font-semibold text-orange-600">
                        {{ number_format((int) $inventoryStatistics['reserved_quantity']) }}
                    </dd>
                </div>

                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Khả dụng</dt>
                    <dd class="font-semibold text-green-600">
                        {{ number_format((int) $inventoryStatistics['available_quantity']) }}
                    </dd>
                </div>

                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Đã bán</dt>
                    <dd class="font-semibold text-purple-600">
                        {{ number_format((int) $inventoryStatistics['sold_quantity']) }}
                    </dd>
                </div>

                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Sắp hết hàng</dt>
                    <dd class="font-semibold text-orange-600">
                        {{ number_format((int) $inventoryStatistics['low_stock']) }}
                    </dd>
                </div>

                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Hết hàng</dt>
                    <dd class="font-semibold text-red-600">
                        {{ number_format((int) $inventoryStatistics['out_of_stock']) }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-5 py-4">
                <h3 class="text-lg font-bold text-gray-900">
                    Sản phẩm tồn kho thấp
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                Sản phẩm / SKU
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                Kho
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                Khả dụng
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                Mức tối thiểu
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($lowStockItems as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <a
                                        href="{{ route('admin.products.show', $item->product_id) }}"
                                        class="font-semibold text-gray-900 hover:text-pink-600"
                                    >
                                        {{ $item->product_name }}
                                    </a>

                                    <p class="mt-1 text-xs text-gray-500">
                                        SKU: {{ $item->sku_code }}
                                        @if ($item->variant_name)
                                            · {{ $item->variant_name }}
                                        @endif
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-600">
                                    {{ $item->warehouse_name }}
                                </td>

                                <td class="px-5 py-4 text-right font-semibold {{ (int) $item->available_quantity <= 0 ? 'text-red-600' : 'text-orange-600' }}">
                                    {{ number_format((int) $item->available_quantity) }}
                                </td>

                                <td class="px-5 py-4 text-right font-semibold text-gray-900">
                                    {{ number_format((int) $item->minimum_stock) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-gray-500">
                                    Không có sản phẩm tồn kho thấp.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="admin-responsive-sidebar-layout grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_360px]">
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-5 py-4">
                <h3 class="text-lg font-bold text-gray-900">
                    Đơn hàng gần nhất
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                Đơn hàng
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                Khách hàng
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                Tổng tiền
                            </th>

                            <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                                Trạng thái
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentOrders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <a
                                        href="{{ route('admin.orders.show', $order->id) }}"
                                        class="font-semibold text-blue-600 hover:underline"
                                    >
                                        {{ $order->order_code }}
                                    </a>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-600">
                                    <p class="font-semibold text-gray-900">
                                        {{ $order->user_name ?: $order->customer_name }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $order->user_email ?: $order->customer_email }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-right font-semibold text-pink-600">
                                    {{ number_format((float) $order->total_amount, 0, ',', '.') }}đ
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        {{ $orderStatusLabels[$order->order_status] ?? $order->order_status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-gray-500">
                                    Chưa có đơn hàng.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5">
            <h3 class="text-lg font-bold text-gray-900">
                Danh mục hệ thống
            </h3>

            <dl class="mt-5 space-y-4 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Tổng sản phẩm</dt>
                    <dd class="font-semibold text-gray-900">
                        {{ number_format((int) $catalogStatistics['products']) }}
                    </dd>
                </div>

                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Sản phẩm hoạt động</dt>
                    <dd class="font-semibold text-green-600">
                        {{ number_format((int) $catalogStatistics['active_products']) }}
                    </dd>
                </div>

                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Danh mục</dt>
                    <dd class="font-semibold text-blue-600">
                        {{ number_format((int) $catalogStatistics['categories']) }}
                    </dd>
                </div>

                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Thương hiệu</dt>
                    <dd class="font-semibold text-purple-600">
                        {{ number_format((int) $catalogStatistics['brands']) }}
                    </dd>
                </div>

                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Lượt xem sản phẩm</dt>
                    <dd class="font-semibold text-orange-600">
                        {{ number_format((int) $catalogStatistics['product_views']) }}
                    </dd>
                </div>

                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Lượt yêu thích</dt>
                    <dd class="font-semibold text-pink-600">
                        {{ number_format((int) $catalogStatistics['product_favorites']) }}
                    </dd>
                </div>
            </dl>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = @json($dailyStatistics);

    const labels = rows.map(function (item) {
        return item.label;
    });

    const revenues = rows.map(function (item) {
        return item.revenue;
    });

    const orders = rows.map(function (item) {
        return item.orders;
    });

    const revenueCanvas = document.getElementById('revenue-chart');
    const ordersCanvas = document.getElementById('orders-chart');

    if (revenueCanvas) {
        new Chart(revenueCanvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu',
                    data: revenues,
                    tension: 0.35,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return new Intl.NumberFormat('vi-VN').format(
                                    context.raw
                                ) + 'đ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return new Intl.NumberFormat('vi-VN', {
                                    notation: 'compact'
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });
    }

    if (ordersCanvas) {
        new Chart(ordersCanvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Số đơn',
                    data: orders
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
