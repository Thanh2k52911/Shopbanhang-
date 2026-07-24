@extends('admin.layouts.master')

@section('title', 'Chỉnh sửa kho hàng')
@section('page-title', 'Chỉnh sửa kho hàng')
@section('page-description', 'Cập nhật tên kho, địa chỉ và trạng thái hoạt động.')

@section('content')
<form
    id="warehouse-edit-form"
    method="POST"
    action="{{ route('admin.warehouses.update', $warehouse->id) }}"
    class="space-y-6"
>
    @csrf
    @method('PUT')

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Chỉnh sửa: {{ $warehouse->name }}
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                ID: {{ $warehouse->id }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('admin.warehouses.show', $warehouse->id) }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
            >
                Quay lại
            </a>

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                Lưu thay đổi
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
                        Thông tin kho
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Cập nhật tên kho và địa chỉ hoạt động.
                    </p>
                </div>

                <div class="space-y-5">
                    <div>
                        <label
                            for="name"
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Tên kho
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name', $warehouse->name) }}"
                            required
                            maxlength="255"
                            autocomplete="organization"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('name')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="address"
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Địa chỉ
                        </label>

                        <textarea
                            id="address"
                            name="address"
                            rows="6"
                            maxlength="255"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >{{ old('address', $warehouse->address) }}</textarea>

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

            <section class="rounded-xl border border-blue-200 bg-blue-50 p-5 sm:p-6">
                <h3 class="font-bold text-blue-900">
                    Gợi ý vận hành
                </h3>

                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-blue-800">
                    <li>
                        Nếu kho tạm ngừng sử dụng, nên chuyển trạng thái sang ngừng hoạt động.
                    </li>

                    <li>
                        Không nên đổi tên kho quá thường xuyên vì tên kho được hiển thị trong nhiều nghiệp vụ.
                    </li>

                    <li>
                        Địa chỉ kho nên ghi đầy đủ để hỗ trợ xử lý vận chuyển và nhập hàng.
                    </li>
                </ul>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Trạng thái hoạt động
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Kiểm soát việc kho có tiếp tục được sử dụng trong hệ thống hay không.
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
                        @checked(old('status', $warehouse->status))
                        class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                    >

                    <span>
                        <span class="block text-sm font-semibold text-gray-900">
                            Kho đang hoạt động
                        </span>

                        <span class="mt-1 block text-xs leading-5 text-gray-500">
                            Cho phép kho tiếp tục được sử dụng trong tồn kho, đơn hàng và vận chuyển.
                        </span>
                    </span>
                </label>

                @error('status')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </section>

            <section class="rounded-xl border border-orange-200 bg-orange-50 p-5">
                <h3 class="font-bold text-orange-900">
                    Lưu ý khi xóa
                </h3>

                <p class="mt-3 text-sm leading-6 text-orange-800">
                    Kho đã có tồn kho, giao dịch kho, đơn hàng hoặc vận chuyển sẽ không thể xóa. Trường hợp này hãy chuyển trạng thái sang ngừng hoạt động.
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

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ route('admin.warehouses.show', $warehouse->id) }}"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
        >
            Hủy
        </a>

        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700 disabled:cursor-not-allowed disabled:opacity-60"
        >
            Lưu thay đổi
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById(
        'warehouse-edit-form'
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
