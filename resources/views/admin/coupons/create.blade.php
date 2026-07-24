@extends('admin.layouts.master')

@section('title', 'Thêm mã giảm giá')
@section('page-title', 'Thêm mã giảm giá')
@section('page-description', 'Tạo coupon mới, cấu hình điều kiện, thời gian và phạm vi áp dụng.')

@section('content')
<form
    id="coupon-create-form"
    method="POST"
    action="{{ route('admin.coupons.store') }}"
    class="space-y-6"
>
    @csrf

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Tạo mã giảm giá mới
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Các trường có dấu
                <span class="font-semibold text-red-500">*</span>
                là bắt buộc.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('admin.coupons.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
            >
                Quay lại
            </a>

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                Lưu mã giảm giá
            </button>
        </div>
    </div>

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

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(340px,1fr)]">
        <div class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <div class="mb-5 border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Thông tin cơ bản
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Khai báo mã coupon, tên và mô tả.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div>
                        <label for="code" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Mã coupon <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="code"
                            type="text"
                            name="code"
                            value="{{ old('code') }}"
                            required
                            maxlength="50"
                            autocomplete="off"
                            placeholder="Ví dụ: SUMMER2026"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 uppercase text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Tên coupon <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            maxlength="255"
                            placeholder="Ví dụ: Ưu đãi mùa hè"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Mô tả
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            rows="5"
                            placeholder="Nhập mô tả coupon..."
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >{{ old('description') }}</textarea>

                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <div class="mb-5 border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Cấu hình giảm giá
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Chọn loại giảm và thiết lập các điều kiện giá trị.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div>
                        <label for="discount_type" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Loại giảm giá <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="discount_type"
                            name="discount_type"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                            <option value="">-- Chọn loại --</option>
                            <option value="fixed" @selected(old('discount_type') === 'fixed')>
                                Giảm số tiền cố định
                            </option>
                            <option value="percentage" @selected(old('discount_type') === 'percentage')>
                                Giảm theo phần trăm
                            </option>
                            <option value="free_shipping" @selected(old('discount_type') === 'free_shipping')>
                                Miễn phí vận chuyển
                            </option>
                        </select>

                        @error('discount_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="discount_value" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Giá trị giảm <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="discount_value"
                            type="number"
                            name="discount_value"
                            value="{{ old('discount_value', 0) }}"
                            min="0"
                            step="0.01"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        <p id="discount-value-help" class="mt-1 text-xs text-gray-500">
                            Nhập số tiền hoặc phần trăm tùy loại coupon.
                        </p>

                        @error('discount_value')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="maximum-discount-wrapper">
                        <label for="maximum_discount" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Giảm tối đa
                        </label>

                        <input
                            id="maximum_discount"
                            type="number"
                            name="maximum_discount"
                            value="{{ old('maximum_discount') }}"
                            min="0"
                            step="0.01"
                            placeholder="Chỉ dùng cho giảm phần trăm"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('maximum_discount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="minimum_order_amount" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Giá trị đơn tối thiểu
                        </label>

                        <input
                            id="minimum_order_amount"
                            type="number"
                            name="minimum_order_amount"
                            value="{{ old('minimum_order_amount', 0) }}"
                            min="0"
                            step="0.01"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('minimum_order_amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <div class="mb-5 border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Giới hạn sử dụng
                    </h3>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div>
                        <label for="usage_limit" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Tổng lượt sử dụng
                        </label>

                        <input
                            id="usage_limit"
                            type="number"
                            name="usage_limit"
                            value="{{ old('usage_limit') }}"
                            min="1"
                            step="1"
                            placeholder="Để trống nếu không giới hạn"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('usage_limit')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="usage_limit_per_user" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Giới hạn mỗi người <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="usage_limit_per_user"
                            type="number"
                            name="usage_limit_per_user"
                            value="{{ old('usage_limit_per_user', 1) }}"
                            min="1"
                            step="1"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('usage_limit_per_user')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <div class="mb-5 border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Thời gian áp dụng
                    </h3>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div>
                        <label for="start_at" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Bắt đầu
                        </label>

                        <input
                            id="start_at"
                            type="datetime-local"
                            name="start_at"
                            value="{{ old('start_at') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('start_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_at" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Kết thúc
                        </label>

                        <input
                            id="end_at"
                            type="datetime-local"
                            name="end_at"
                            value="{{ old('end_at') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('end_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <p id="date-error" class="mt-3 hidden text-sm text-red-600">
                    Thời gian kết thúc phải sau thời gian bắt đầu.
                </p>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <div class="mb-5 border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Phạm vi áp dụng
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Để trống nếu coupon áp dụng cho toàn bộ hệ thống.
                    </p>
                </div>

                <div class="space-y-6">
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label class="text-sm font-medium text-gray-700">
                                Sản phẩm áp dụng
                            </label>

                            <span id="product-selected-count" class="text-xs text-gray-500">
                                0 đã chọn
                            </span>
                        </div>

                        <select
                            id="product_ids"
                            name="product_ids[]"
                            multiple
                            size="10"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                            @foreach ($products as $product)
                                <option
                                    value="{{ $product->id }}"
                                    @selected(in_array(
                                        (int) $product->id,
                                        array_map('intval', old('product_ids', [])),
                                        true
                                    ))
                                >
                                    {{ $product->name }}
                                    {{ (int) $product->status === 1 ? '' : ' (Ngừng bán)' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label class="text-sm font-medium text-gray-700">
                                Danh mục áp dụng
                            </label>

                            <span id="category-selected-count" class="text-xs text-gray-500">
                                0 đã chọn
                            </span>
                        </div>

                        <select
                            id="category_ids"
                            name="category_ids[]"
                            multiple
                            size="10"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                            @foreach ($categories as $category)
                                <option
                                    value="{{ $category->id }}"
                                    @selected(in_array(
                                        (int) $category->id,
                                        array_map('intval', old('category_ids', [])),
                                        true
                                    ))
                                >
                                    {{ $category->parent_id ? '— ' : '' }}
                                    {{ $category->name }}
                                    {{ (int) $category->status === 1 ? '' : ' (Đang ẩn)' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label class="text-sm font-medium text-gray-700">
                                Người dùng được chỉ định
                            </label>

                            <span id="user-selected-count" class="text-xs text-gray-500">
                                0 đã chọn
                            </span>
                        </div>

                        <select
                            id="user_ids"
                            name="user_ids[]"
                            multiple
                            size="10"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                            @foreach ($users as $user)
                                <option
                                    value="{{ $user->id }}"
                                    @selected(in_array(
                                        (int) $user->id,
                                        array_map('intval', old('user_ids', [])),
                                        true
                                    ))
                                >
                                    {{ $user->name }} — {{ $user->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Trạng thái
                </h3>

                <div class="mt-4 space-y-4">
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4 transition hover:bg-gray-50">
                        <input type="hidden" name="status" value="0">

                        <input
                            type="checkbox"
                            name="status"
                            value="1"
                            @checked(old('status', 1))
                            class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                        >

                        <span>
                            <span class="block text-sm font-semibold text-gray-900">
                                Kích hoạt coupon
                            </span>
                            <span class="mt-1 block text-xs text-gray-500">
                                Cho phép coupon được sử dụng khi đủ điều kiện.
                            </span>
                        </span>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4 transition hover:bg-gray-50">
                        <input type="hidden" name="is_public" value="0">

                        <input
                            type="checkbox"
                            name="is_public"
                            value="1"
                            @checked(old('is_public', 1))
                            class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                        >

                        <span>
                            <span class="block text-sm font-semibold text-gray-900">
                                Công khai
                            </span>
                            <span class="mt-1 block text-xs text-gray-500">
                                Hiển thị coupon cho khách hàng.
                            </span>
                        </span>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4 transition hover:bg-gray-50">
                        <input type="hidden" name="first_order_only" value="0">

                        <input
                            type="checkbox"
                            name="first_order_only"
                            value="1"
                            @checked(old('first_order_only'))
                            class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                        >

                        <span>
                            <span class="block text-sm font-semibold text-gray-900">
                                Chỉ đơn hàng đầu tiên
                            </span>
                            <span class="mt-1 block text-xs text-gray-500">
                                Chỉ khách chưa từng đặt đơn được áp dụng.
                            </span>
                        </span>
                    </label>
                </div>
            </section>

            <section class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                <h3 class="font-bold text-blue-900">
                    Cách chọn nhiều mục
                </h3>

                <p class="mt-3 text-sm leading-6 text-blue-800">
                    Giữ phím Ctrl trên Windows hoặc Command trên macOS rồi nhấn vào nhiều sản phẩm, danh mục hoặc người dùng.
                </p>
            </section>

            <section class="rounded-xl border border-orange-200 bg-orange-50 p-5">
                <h3 class="font-bold text-orange-900">
                    Lưu ý
                </h3>

                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-orange-800">
                    <li>Coupon phần trăm không được vượt quá 100%.</li>
                    <li>Coupon miễn phí vận chuyển có giá trị giảm bằng 0.</li>
                    <li>Coupon đã có lượt sử dụng sẽ không thể xóa.</li>
                </ul>
            </section>
        </aside>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ route('admin.coupons.index') }}"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
        >
            Hủy
        </a>

        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700 disabled:cursor-not-allowed disabled:opacity-60"
        >
            Lưu mã giảm giá
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('coupon-create-form');
    const codeInput = document.getElementById('code');
    const discountType = document.getElementById('discount_type');
    const discountValue = document.getElementById('discount_value');
    const maximumDiscountWrapper = document.getElementById('maximum-discount-wrapper');
    const maximumDiscount = document.getElementById('maximum_discount');
    const discountValueHelp = document.getElementById('discount-value-help');
    const startAt = document.getElementById('start_at');
    const endAt = document.getElementById('end_at');
    const dateError = document.getElementById('date-error');

    const productSelect = document.getElementById('product_ids');
    const categorySelect = document.getElementById('category_ids');
    const userSelect = document.getElementById('user_ids');

    const updateDiscountFields = function () {
        const type = discountType?.value;

        if (type === 'percentage') {
            maximumDiscountWrapper?.classList.remove('hidden');
            discountValue.max = '100';
            discountValue.disabled = false;

            if (discountValueHelp) {
                discountValueHelp.textContent =
                    'Nhập phần trăm từ 0 đến 100.';
            }
        } else if (type === 'free_shipping') {
            maximumDiscountWrapper?.classList.add('hidden');
            maximumDiscount.value = '';
            discountValue.value = '0';
            discountValue.disabled = true;

            if (discountValueHelp) {
                discountValueHelp.textContent =
                    'Coupon miễn phí vận chuyển luôn có giá trị 0.';
            }
        } else {
            maximumDiscountWrapper?.classList.add('hidden');
            maximumDiscount.value = '';
            discountValue.removeAttribute('max');
            discountValue.disabled = false;

            if (discountValueHelp) {
                discountValueHelp.textContent =
                    'Nhập số tiền giảm trực tiếp.';
            }
        }
    };

    const validateDates = function () {
        if (!startAt?.value || !endAt?.value) {
            dateError?.classList.add('hidden');
            return true;
        }

        const isValid =
            new Date(endAt.value) > new Date(startAt.value);

        dateError?.classList.toggle('hidden', isValid);

        return isValid;
    };

    const updateSelectedCount = function (
        select,
        targetId
    ) {
        const target = document.getElementById(targetId);

        if (!select || !target) {
            return;
        }

        target.textContent =
            `${select.selectedOptions.length} đã chọn`;
    };

    codeInput?.addEventListener('input', function () {
        codeInput.value = codeInput.value
            .toUpperCase()
            .replace(/\s+/g, '');
    });

    discountType?.addEventListener(
        'change',
        updateDiscountFields
    );

    startAt?.addEventListener('change', validateDates);
    endAt?.addEventListener('change', validateDates);

    productSelect?.addEventListener('change', function () {
        updateSelectedCount(
            productSelect,
            'product-selected-count'
        );
    });

    categorySelect?.addEventListener('change', function () {
        updateSelectedCount(
            categorySelect,
            'category-selected-count'
        );
    });

    userSelect?.addEventListener('change', function () {
        updateSelectedCount(
            userSelect,
            'user-selected-count'
        );
    });

    updateDiscountFields();
    validateDates();

    updateSelectedCount(
        productSelect,
        'product-selected-count'
    );

    updateSelectedCount(
        categorySelect,
        'category-selected-count'
    );

    updateSelectedCount(
        userSelect,
        'user-selected-count'
    );

    form?.addEventListener('submit', function (event) {
        if (!validateDates()) {
            event.preventDefault();
            return;
        }

        if (
            discountType?.value === 'free_shipping'
            && discountValue
        ) {
            discountValue.disabled = false;
            discountValue.value = '0';
        }

        form
            .querySelectorAll('button[type="submit"]')
            .forEach(function (button) {
                button.disabled = true;
                button.textContent = 'Đang lưu...';
            });
    });
});
</script>
@endpush
