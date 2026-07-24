@extends('admin.layouts.master')

@section('title', 'Thêm nhà cung cấp')
@section('page-title', 'Thêm nhà cung cấp')
@section('page-description', 'Tạo nhà cung cấp mới và khai báo đầy đủ thông tin liên hệ.')

@section('content')
<form
    id="supplier-create-form"
    method="POST"
    action="{{ route('admin.suppliers.store') }}"
    class="space-y-6"
>
    @csrf

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Tạo nhà cung cấp mới
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Các trường có dấu
                <span class="font-semibold text-red-500">*</span>
                là bắt buộc.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('admin.suppliers.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
            >
                Quay lại
            </a>

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                Lưu nhà cung cấp
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

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <div class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <div class="mb-5 border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Thông tin nhà cung cấp
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Khai báo tên doanh nghiệp và người liên hệ.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Tên nhà cung cấp
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            maxlength="255"
                            autocomplete="organization"
                            placeholder="Ví dụ: Công ty TNHH L'Oréal Việt Nam"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('name')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="contact_name" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Người liên hệ
                        </label>

                        <input
                            id="contact_name"
                            type="text"
                            name="contact_name"
                            value="{{ old('contact_name') }}"
                            maxlength="255"
                            autocomplete="name"
                            placeholder="Họ và tên người phụ trách"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('contact_name')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="tax_code" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Mã số thuế
                        </label>

                        <input
                            id="tax_code"
                            type="text"
                            name="tax_code"
                            value="{{ old('tax_code') }}"
                            maxlength="255"
                            autocomplete="off"
                            placeholder="Ví dụ: 0101234567"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('tax_code')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <div class="mb-5 border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Thông tin liên hệ
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Nhập điện thoại, email và địa chỉ của nhà cung cấp.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div>
                        <label for="phone" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Điện thoại
                        </label>

                        <input
                            id="phone"
                            type="text"
                            name="phone"
                            value="{{ old('phone') }}"
                            maxlength="15"
                            inputmode="tel"
                            autocomplete="tel"
                            placeholder="Ví dụ: 0901234567"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Email
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            maxlength="255"
                            autocomplete="email"
                            placeholder="supplier@example.com"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('email')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label for="address" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Địa chỉ
                        </label>

                        <textarea
                            id="address"
                            name="address"
                            rows="5"
                            maxlength="255"
                            placeholder="Nhập địa chỉ nhà cung cấp..."
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >{{ old('address') }}</textarea>

                        <div class="mt-1 flex items-center justify-between gap-3">
                            @error('address')
                                <p class="text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @else
                                <p class="text-xs text-gray-500">
                                    Tối đa 255 ký tự.
                                </p>
                            @enderror

                            <span
                                id="address-character-count"
                                class="text-xs text-gray-400"
                            >
                                0/255
                            </span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Trạng thái hợp tác
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Kiểm soát trạng thái hoạt động của nhà cung cấp.
                </p>

                <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4 transition hover:bg-gray-50">
                    <input
                        type="hidden"
                        name="status"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        name="status"
                        value="1"
                        @checked(old('status', 1))
                        class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                    >

                    <span>
                        <span class="block text-sm font-semibold text-gray-900">
                            Đang hợp tác
                        </span>

                        <span class="mt-1 block text-xs leading-5 text-gray-500">
                            Cho phép nhà cung cấp được sử dụng trong quản lý sản phẩm.
                        </span>
                    </span>
                </label>

                @error('status')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Thứ tự hiển thị
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Số nhỏ hơn sẽ được ưu tiên hiển thị trước.
                </p>

                <div class="mt-4">
                    <label for="sort_order" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Sort order
                    </label>

                    <input
                        id="sort_order"
                        type="number"
                        name="sort_order"
                        value="{{ old('sort_order', 0) }}"
                        min="0"
                        step="1"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                    >

                    @error('sort_order')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </section>

            <section class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                <h3 class="font-bold text-blue-900">
                    Gợi ý
                </h3>

                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-blue-800">
                    <li>
                        Nên nhập đầy đủ người liên hệ, điện thoại và email để thuận tiện làm việc.
                    </li>

                    <li>
                        Mã số thuế giúp phân biệt các doanh nghiệp có tên gần giống nhau.
                    </li>

                    <li>
                        Nhà cung cấp đang được sản phẩm sử dụng sẽ không thể xóa.
                    </li>
                </ul>
            </section>
        </aside>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ route('admin.suppliers.index') }}"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
        >
            Hủy
        </a>

        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700 disabled:cursor-not-allowed disabled:opacity-60"
        >
            Lưu nhà cung cấp
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById(
        'supplier-create-form'
    );

    const phoneInput = document.getElementById(
        'phone'
    );

    const addressInput = document.getElementById(
        'address'
    );

    const addressCharacterCount =
        document.getElementById(
            'address-character-count'
        );

    const updateAddressCharacterCount = function () {
        if (
            !addressInput
            || !addressCharacterCount
        ) {
            return;
        }

        addressCharacterCount.textContent =
            `${addressInput.value.length}/255`;
    };

    addressInput?.addEventListener(
        'input',
        updateAddressCharacterCount
    );

    updateAddressCharacterCount();

    phoneInput?.addEventListener('input', function () {
        phoneInput.value = phoneInput.value.replace(
            /[^0-9+\-\s().]/g,
            ''
        );
    });

    form?.addEventListener('submit', function () {
        form
            .querySelectorAll(
                'button[type="submit"]'
            )
            .forEach(function (button) {
                button.disabled = true;
                button.textContent =
                    'Đang lưu...';
            });
    });
});
</script>
@endpush
