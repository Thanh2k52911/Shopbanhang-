@php
    $isEdit = isset($shippingMethod);
@endphp

@if ($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div>
        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Tên phương thức <span class="text-red-500">*</span></label>
        <input id="name" name="name" type="text" value="{{ old('name', $shippingMethod->name ?? '') }}" required class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
    </div>

    <div>
        <label for="code" class="mb-1.5 block text-sm font-medium text-gray-700">Mã phương thức</label>
        <input id="code" name="code" type="text" value="{{ old('code', $shippingMethod->code ?? '') }}" placeholder="Ví dụ: giao_hang_nhanh" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
    </div>

    <div>
        <label for="provider" class="mb-1.5 block text-sm font-medium text-gray-700">Nhà cung cấp vận chuyển</label>
        <input id="provider" name="provider" type="text" value="{{ old('provider', $shippingMethod->provider ?? '') }}" placeholder="internal, ghn, ghtk..." class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
    </div>

    <div>
        <label for="sort_order" class="mb-1.5 block text-sm font-medium text-gray-700">Thứ tự hiển thị</label>
        <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $shippingMethod->sort_order ?? 0) }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
    </div>

    <div>
        <label for="base_fee" class="mb-1.5 block text-sm font-medium text-gray-700">Phí giao cơ bản <span class="text-red-500">*</span></label>
        <input id="base_fee" name="base_fee" type="number" min="0" step="1000" value="{{ old('base_fee', $shippingMethod->base_fee ?? 0) }}" required class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
    </div>

    <div>
        <label for="free_shipping_minimum" class="mb-1.5 block text-sm font-medium text-gray-700">Miễn phí từ giá trị đơn</label>
        <input id="free_shipping_minimum" name="free_shipping_minimum" type="number" min="0" step="1000" value="{{ old('free_shipping_minimum', $shippingMethod->free_shipping_minimum ?? '') }}" placeholder="Để trống nếu không miễn phí" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
    </div>

    <div>
        <label for="estimated_days_min" class="mb-1.5 block text-sm font-medium text-gray-700">Số ngày giao tối thiểu</label>
        <input id="estimated_days_min" name="estimated_days_min" type="number" min="0" value="{{ old('estimated_days_min', $shippingMethod->estimated_days_min ?? '') }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
    </div>

    <div>
        <label for="estimated_days_max" class="mb-1.5 block text-sm font-medium text-gray-700">Số ngày giao tối đa</label>
        <input id="estimated_days_max" name="estimated_days_max" type="number" min="0" value="{{ old('estimated_days_max', $shippingMethod->estimated_days_max ?? '') }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
    </div>

    <div class="lg:col-span-2">
        <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700">Mô tả</label>
        <textarea id="description" name="description" rows="5" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">{{ old('description', $shippingMethod->description ?? '') }}</textarea>
    </div>

    <div class="lg:col-span-2">
        <input type="hidden" name="status" value="0">
        <label class="inline-flex items-center gap-3">
            <input type="checkbox" name="status" value="1" @checked((bool) old('status', $shippingMethod->status ?? true)) class="rounded border-gray-300 text-pink-600 focus:ring-pink-500">
            <span class="text-sm font-medium text-gray-700">Cho phép sử dụng phương thức này</span>
        </label>
    </div>
</div>

<div class="mt-8 flex flex-wrap gap-3 border-t border-gray-200 pt-6">
    <button type="submit" class="rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-pink-700">
        {{ $isEdit ? 'Lưu thay đổi' : 'Thêm phương thức' }}
    </button>
    <a href="{{ route('admin.shipping-methods.index') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Hủy</a>
</div>
