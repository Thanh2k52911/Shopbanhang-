@extends('admin.layouts.master')

@section('title', 'Chi tiết tồn kho')
@section('page-title', 'Chi tiết tồn kho')
@section('page-description', 'Theo dõi tồn kho theo SKU, cập nhật mức tồn tối thiểu và thực hiện giao dịch kho.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex min-w-0 items-center gap-4">
            <a
                href="{{ route('admin.inventories.index') }}"
                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white text-xl text-gray-600 transition hover:bg-gray-50"
                title="Quay lại"
            >
                ←
            </a>

            <div class="min-w-0">
                <h2
                    class="max-w-4xl truncate text-2xl font-bold text-gray-900"
                    title="{{ $inventory->product_name }}"
                >
                    {{ $inventory->product_name }}
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    SKU: {{ $inventory->sku_code }}
                    · Kho: {{ $inventory->warehouse_name }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('admin.products.show', $inventory->product_id) }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
            >
                Xem sản phẩm
            </a>

            <a
                href="{{ route('admin.warehouses.show', $inventory->warehouse_id) }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
            >
                Xem kho
            </a>
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

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="font-semibold text-red-700">
                Dữ liệu chưa hợp lệ.
            </p>

            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $availableQuantity = max(
            0,
            (int) $inventory->available_quantity
        );

        $isOutOfStock = $availableQuantity <= 0;

        $isLowStock = ! $isOutOfStock
            && $availableQuantity <= (int) $inventory->minimum_stock;
    @endphp

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Tổng tồn
            </p>

            <strong class="mt-2 block text-2xl text-blue-600">
                {{ number_format((int) $inventory->quantity) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Đang giữ
            </p>

            <strong class="mt-2 block text-2xl text-orange-600">
                {{ number_format((int) $inventory->reserved_quantity) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Khả dụng
            </p>

            <strong class="mt-2 block text-2xl text-pink-600">
                {{ number_format($availableQuantity) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Đã bán
            </p>

            <strong class="mt-2 block text-2xl text-green-600">
                {{ number_format((int) $inventory->sold_quantity) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Tồn tối thiểu
            </p>

            <strong class="mt-2 block text-2xl text-indigo-600">
                {{ number_format((int) $inventory->minimum_stock) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Tình trạng
            </p>

            <div class="mt-3">
                @if ($isOutOfStock)
                    <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-700">
                        Hết hàng
                    </span>
                @elseif ($isLowStock)
                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-sm font-semibold text-yellow-700">
                        Tồn thấp
                    </span>
                @else
                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">
                        Ổn định
                    </span>
                @endif
            </div>
        </article>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(340px,1fr)]">
        <div class="space-y-6">
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Thông tin SKU và kho
                    </h3>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-gray-500">
                            Sản phẩm
                        </p>

                        <a
                            href="{{ route('admin.products.show', $inventory->product_id) }}"
                            class="mt-1 block font-semibold text-gray-900 transition hover:text-pink-600"
                        >
                            {{ $inventory->product_name }}
                        </a>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Mã SKU
                        </p>

                        <strong class="mt-1 block text-gray-900">
                            {{ $inventory->sku_code }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Barcode
                        </p>

                        <strong class="mt-1 block text-gray-900">
                            {{ $inventory->barcode ?: 'Chưa cập nhật' }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Biến thể
                        </p>

                        <strong class="mt-1 block text-gray-900">
                            {{ $inventory->variant_name ?: 'Không có' }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Giá bán
                        </p>

                        <strong class="mt-1 block text-pink-600">
                            {{ number_format((float) $inventory->price, 0, ',', '.') }}đ
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Giá vốn
                        </p>

                        <strong class="mt-1 block text-gray-900">
                            {{ $inventory->cost_price !== null
                                ? number_format((float) $inventory->cost_price, 0, ',', '.') . 'đ'
                                : 'Chưa cập nhật' }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Khối lượng
                        </p>

                        <strong class="mt-1 block text-gray-900">
                            {{ $inventory->weight !== null
                                ? number_format((float) $inventory->weight, 2, ',', '.') . ' g'
                                : 'Chưa cập nhật' }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Trạng thái SKU
                        </p>

                        @if ((int) $inventory->sku_status === 1)
                            <span class="mt-1 inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                Đang hoạt động
                            </span>
                        @else
                            <span class="mt-1 inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                Ngừng hoạt động
                            </span>
                        @endif
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Kho hàng
                        </p>

                        <a
                            href="{{ route('admin.warehouses.show', $inventory->warehouse_id) }}"
                            class="mt-1 block font-semibold text-gray-900 transition hover:text-pink-600"
                        >
                            {{ $inventory->warehouse_name }}
                        </a>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Trạng thái kho
                        </p>

                        @if ((int) $inventory->warehouse_status === 1)
                            <span class="mt-1 inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                Đang hoạt động
                            </span>
                        @else
                            <span class="mt-1 inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                Ngừng hoạt động
                            </span>
                        @endif
                    </div>

                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500">
                            Địa chỉ kho
                        </p>

                        <p class="mt-1 whitespace-pre-line font-medium text-gray-900">
                            {{ $inventory->warehouse_address ?: 'Chưa cập nhật' }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Lịch sử giao dịch kho
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Hiển thị các giao dịch của đúng SKU tại kho hiện tại.
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
                                    Loại
                                </th>

                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Số lượng
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Tham chiếu
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Người tạo
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Ghi chú
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($transactions as $transaction)
                                @php
                                    $typeLabel = match ($transaction->type) {
                                        'import' => 'Nhập kho',
                                        'export' => 'Xuất kho',
                                        'return' => 'Trả về kho',
                                        'cancel' => 'Hủy / hoàn giữ',
                                        'adjust' => 'Điều chỉnh',
                                        default => $transaction->type,
                                    };

                                    $typeClasses = match ($transaction->type) {
                                        'import', 'return' => 'bg-green-100 text-green-700',
                                        'export' => 'bg-red-100 text-red-700',
                                        'cancel' => 'bg-blue-100 text-blue-700',
                                        'adjust' => 'bg-yellow-100 text-yellow-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp

                                <tr class="align-top transition hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                        {{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $typeClasses }}">
                                            {{ $typeLabel }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <span class="font-semibold text-gray-900">
                                            {{ number_format((int) $transaction->quantity) }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 text-sm text-gray-600">
                                        @if ($transaction->reference_type || $transaction->reference_id)
                                            <div>
                                                {{ $transaction->reference_type ?: 'Tham chiếu' }}
                                            </div>

                                            @if ($transaction->reference_id)
                                                <div class="mt-1 text-xs text-gray-400">
                                                    ID: {{ $transaction->reference_id }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">
                                                Không có
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4">
                                        <p class="text-sm font-medium text-gray-800">
                                            {{ $transaction->creator_name ?: 'Hệ thống' }}
                                        </p>

                                        @if ($transaction->creator_email)
                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ $transaction->creator_email }}
                                            </p>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4">
                                        <p class="max-w-lg whitespace-pre-line text-sm text-gray-600">
                                            {{ $transaction->note ?: 'Không có ghi chú' }}
                                        </p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="6"
                                        class="px-5 py-12 text-center text-gray-500"
                                    >
                                        Chưa có giao dịch kho.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($transactions->hasPages())
                    <div class="border-t border-gray-200 px-5 py-4">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Cập nhật tồn tối thiểu
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Hệ thống cảnh báo khi tồn khả dụng nhỏ hơn hoặc bằng mức này.
                </p>

                <form
                    method="POST"
                    action="{{ route(
                        'admin.inventories.update-minimum-stock',
                        $inventory->id
                    ) }}"
                    class="mt-4 space-y-4"
                >
                    @csrf
                    @method('PATCH')

                    <div>
                        <label
                            for="minimum_stock"
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Mức tồn tối thiểu
                        </label>

                        <input
                            id="minimum_stock"
                            type="number"
                            name="minimum_stock"
                            value="{{ old('minimum_stock', $inventory->minimum_stock) }}"
                            min="0"
                            step="1"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800"
                    >
                        Cập nhật mức tồn
                    </button>
                </form>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Tạo giao dịch kho
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Nhập, xuất, trả về hoặc điều chỉnh tổng tồn kho.
                </p>

                <form
                    id="inventory-transaction-form"
                    method="POST"
                    action="{{ route(
                        'admin.inventories.transactions.store',
                        $inventory->id
                    ) }}"
                    class="mt-4 space-y-4"
                >
                    @csrf

                    <div>
                        <label
                            for="type"
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Loại giao dịch
                        </label>

                        <select
                            id="type"
                            name="type"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                            <option value="">
                                -- Chọn loại --
                            </option>

                            <option value="import" @selected(old('type') === 'import')>
                                Nhập kho
                            </option>

                            <option value="export" @selected(old('type') === 'export')>
                                Xuất kho
                            </option>

                            <option value="return" @selected(old('type') === 'return')>
                                Trả hàng về kho
                            </option>

                            <option value="adjust" @selected(old('type') === 'adjust')>
                                Điều chỉnh tăng / giảm
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            for="quantity"
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Số lượng
                        </label>

                        <input
                            id="quantity"
                            type="number"
                            name="quantity"
                            value="{{ old('quantity') }}"
                            step="1"
                            required
                            placeholder="Ví dụ: 10 hoặc -5 khi điều chỉnh"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        <p
                            id="quantity-help"
                            class="mt-1 text-xs text-gray-500"
                        >
                            Nhập số dương. Chỉ loại điều chỉnh mới cho phép số âm.
                        </p>
                    </div>

                    <div>
                        <label
                            for="reference_type"
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Loại tham chiếu
                        </label>

                        <input
                            id="reference_type"
                            type="text"
                            name="reference_type"
                            value="{{ old('reference_type') }}"
                            maxlength="255"
                            placeholder="Ví dụ: purchase_order"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                    </div>

                    <div>
                        <label
                            for="reference_id"
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            ID tham chiếu
                        </label>

                        <input
                            id="reference_id"
                            type="number"
                            name="reference_id"
                            value="{{ old('reference_id') }}"
                            min="1"
                            step="1"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                    </div>

                    <div>
                        <label
                            for="note"
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Ghi chú
                        </label>

                        <textarea
                            id="note"
                            name="note"
                            rows="4"
                            maxlength="5000"
                            placeholder="Nhập lý do hoặc nội dung giao dịch..."
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >{{ old('note') }}</textarea>
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        Tạo giao dịch
                    </button>
                </form>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Tổng hợp giao dịch
                </h3>

                <dl class="mt-4 space-y-4">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-gray-500">
                            Tổng giao dịch
                        </dt>

                        <dd class="font-semibold text-gray-900">
                            {{ number_format((int) ($transactionStatistics->total_transactions ?? 0)) }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-gray-500">
                            Đã nhập
                        </dt>

                        <dd class="font-semibold text-green-700">
                            {{ number_format((int) ($transactionStatistics->imported_quantity ?? 0)) }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-gray-500">
                            Trả về kho
                        </dt>

                        <dd class="font-semibold text-blue-700">
                            {{ number_format((int) ($transactionStatistics->returned_quantity ?? 0)) }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-gray-500">
                            Đã xuất
                        </dt>

                        <dd class="font-semibold text-red-700">
                            {{ number_format((int) ($transactionStatistics->exported_quantity ?? 0)) }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-orange-200 bg-orange-50 p-5">
                <h3 class="font-bold text-orange-900">
                    Lưu ý nghiệp vụ
                </h3>

                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-orange-800">
                    <li>
                        Không thể xuất vượt tồn khả dụng.
                    </li>

                    <li>
                        Điều chỉnh giảm không được làm tổng tồn nhỏ hơn số đang giữ.
                    </li>

                    <li>
                        Loại “Hủy / hoàn giữ” do workflow đơn hàng tự tạo, không nhập thủ công tại đây.
                    </li>
                </ul>
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
                            {{ \Carbon\Carbon::parse($inventory->created_at)->format('d/m/Y H:i') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Cập nhật gần nhất
                        </dt>

                        <dd class="mt-1 font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($inventory->updated_at)->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById(
        'inventory-transaction-form'
    );

    const typeInput = document.getElementById(
        'type'
    );

    const quantityInput = document.getElementById(
        'quantity'
    );

    const quantityHelp = document.getElementById(
        'quantity-help'
    );

    const updateQuantityRules = function () {
        if (!typeInput || !quantityInput) {
            return;
        }

        const type = typeInput.value;

        if (type === 'adjust') {
            quantityInput.removeAttribute('min');

            if (quantityHelp) {
                quantityHelp.textContent =
                    'Nhập số dương để tăng hoặc số âm để giảm tồn kho.';
            }
        } else {
            quantityInput.min = '1';

            if (quantityHelp) {
                quantityHelp.textContent =
                    'Loại giao dịch này chỉ chấp nhận số lượng lớn hơn 0.';
            }
        }
    };

    typeInput?.addEventListener(
        'change',
        updateQuantityRules
    );

    updateQuantityRules();

    form?.addEventListener('submit', function () {
        form
            .querySelectorAll(
                'button[type="submit"]'
            )
            .forEach(function (button) {
                button.disabled = true;
                button.textContent =
                    'Đang xử lý...';
            });
    });
});
</script>
@endpush
