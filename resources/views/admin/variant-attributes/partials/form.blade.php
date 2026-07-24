@php($editing = isset($variantAttribute))

@if ($errors->any())
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div>
    <label for="name" class="mb-1.5 block text-sm font-semibold text-gray-700">
        Tên thuộc tính <span class="text-red-500">*</span>
    </label>
    <input
        id="name"
        name="name"
        type="text"
        maxlength="100"
        required
        value="{{ old('name', $variantAttribute->name ?? '') }}"
        placeholder="Ví dụ: Dung tích, Màu sắc, Loại da..."
        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
    >
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-pink-700">
        {{ $editing ? 'Lưu thay đổi' : 'Thêm thuộc tính' }}
    </button>
    <a href="{{ route('admin.variant-attributes.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
        Hủy
    </a>
</div>
