@extends('admin.layouts.master')

@section('title', 'Thêm chiến dịch giảm giá')
@section('page-title', 'Thêm chiến dịch giảm giá')
@section('page-description', 'Tạo chiến dịch mới và cấu hình giảm giá theo từng sản phẩm.')

@section('content')
<form id="campaign-form" method="POST" action="{{ route('admin.discount-campaigns.store') }}" class="space-y-6">
    @csrf

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Tạo chiến dịch mới</h2>
            <p class="mt-1 text-sm text-gray-500">Mỗi sản phẩm dùng giảm phần trăm hoặc số tiền cố định.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.discount-campaigns.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">Quay lại</a>
            <button class="rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-pink-700">Lưu chiến dịch</button>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <ul class="list-disc space-y-1 pl-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="admin-responsive-sidebar-layout grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_340px]">
        <div class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">Thông tin chiến dịch</h3>
                <div class="mt-5 space-y-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Tên chiến dịch *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required maxlength="255"
                               class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Mô tả</label>
                        <textarea name="description" rows="5" class="w-full rounded-lg border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500">{{ old('description') }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Bắt đầu *</label>
                            <input id="start_date" type="datetime-local" name="start_date" value="{{ old('start_date') }}" required
                                   class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Kết thúc *</label>
                            <input id="end_date" type="datetime-local" name="end_date" value="{{ old('end_date') }}" required
                                   class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
                        </div>
                    </div>
                    <p id="date-error" class="hidden text-sm text-red-600">Thời gian kết thúc phải sau thời gian bắt đầu.</p>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Sản phẩm giảm giá</h3>
                        <p class="mt-1 text-sm text-gray-500">Không được chọn trùng sản phẩm.</p>
                    </div>
                    <button id="add-discount-row" type="button" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white">+ Thêm sản phẩm</button>
                </div>

                <div id="discount-rows" class="mt-5 space-y-4">
                    @foreach (old('discounts', [['product_id' => '', 'discount_percent' => '', 'discount_amount' => '', 'limit_quantity' => '']]) as $index => $discount)
                        @include('admin.discount-campaigns.partials.discount-row', [
                            'index' => $index,
                            'discount' => (object) $discount,
                            'products' => $products,
                            'soldQuantity' => 0,
                        ])
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Trạng thái</h3>
                <div class="mt-4 space-y-4">
                    <label class="flex gap-3 rounded-lg border border-gray-200 p-4">
                        <input type="hidden" name="status" value="0">
                        <input type="checkbox" name="status" value="1" @checked(old('status', 1)) class="mt-0.5 rounded text-pink-600">
                        <span><strong class="block text-sm text-gray-900">Kích hoạt</strong><span class="text-xs text-gray-500">Cho phép chiến dịch chạy khi đến thời gian.</span></span>
                    </label>
                    <label class="flex gap-3 rounded-lg border border-gray-200 p-4">
                        <input type="hidden" name="is_flash_sale" value="0">
                        <input type="checkbox" name="is_flash_sale" value="1" @checked(old('is_flash_sale')) class="mt-0.5 rounded text-pink-600">
                        <span><strong class="block text-sm text-gray-900">Flash sale</strong><span class="text-xs text-gray-500">Đánh dấu chiến dịch bán nhanh.</span></span>
                    </label>
                </div>
            </section>
        </aside>
    </div>
</form>

<template id="discount-row-template">
    @include('admin.discount-campaigns.partials.discount-row', [
        'index' => '__INDEX__',
        'discount' => (object) [
            'product_id' => '',
            'discount_percent' => '',
            'discount_amount' => '',
            'limit_quantity' => '',
        ],
        'products' => $products,
        'soldQuantity' => 0,
    ])
</template>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('campaign-form');
    const container = document.getElementById('discount-rows');
    const template = document.getElementById('discount-row-template');
    const addButton = document.getElementById('add-discount-row');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    const dateError = document.getElementById('date-error');

    const renumberRows = function () {
        container?.querySelectorAll('[data-discount-row]').forEach(function (row, index) {
            row.querySelectorAll('[data-field]').forEach(function (input) {
                input.name = `discounts[${index}][${input.dataset.field}]`;
            });
        });
    };

    const bindRow = function (row) {
        const type = row.querySelector('[data-discount-type]');
        const percent = row.querySelector('[data-percent]');
        const amount = row.querySelector('[data-amount]');
        const remove = row.querySelector('[data-remove-row]');

        const updateType = function () {
            if (type.value === 'percent') {
                percent.disabled = false;
                amount.disabled = true;
                amount.value = '';
            } else {
                percent.disabled = true;
                percent.value = '';
                amount.disabled = false;
            }
        };

        type?.addEventListener('change', updateType);
        remove?.addEventListener('click', function () {
            row.remove();
            renumberRows();
        });

        updateType();
    };

    addButton?.addEventListener('click', function () {
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('[data-discount-row]');
        container.appendChild(clone);
        bindRow(container.lastElementChild);
        renumberRows();
    });

    container?.querySelectorAll('[data-discount-row]').forEach(bindRow);

    const validateDates = function () {
        if (!startDate?.value || !endDate?.value) {
            dateError?.classList.add('hidden');
            return true;
        }

        const valid = new Date(endDate.value) > new Date(startDate.value);
        dateError?.classList.toggle('hidden', valid);
        return valid;
    };

    startDate?.addEventListener('change', validateDates);
    endDate?.addEventListener('change', validateDates);

    form?.addEventListener('submit', function (event) {
        if (!validateDates()) {
            event.preventDefault();
            return;
        }

        renumberRows();

        form.querySelectorAll('button[type="submit"]').forEach(function (button) {
            button.disabled = true;
            button.textContent = 'Đang lưu...';
        });
    });

    validateDates();
    renumberRows();
});
</script>
@endpush
