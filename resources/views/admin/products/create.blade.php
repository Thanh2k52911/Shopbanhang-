@extends('admin.layouts.master')

@section('title', 'Thêm sản phẩm')

@section('page-title', 'Thêm sản phẩm')

@section(
    'page-description',
    'Tạo sản phẩm mới, hình ảnh, video, biến thể, SKU và tồn kho.'
)

@section('content')
    @php
        $oldVideos = old('videos', []);
        $oldVariants = old('variants', []);
        $oldSkus = old('skus', [
            [
                'sku_code' => '',
                'barcode' => '',
                'price' => '',
                'cost_price' => '',
                'weight' => '',
                'status' => 1,
                'inventories' => [],
            ],
        ]);
    @endphp

    <form
        id="product-create-form"
        method="POST"
        action="{{ route('admin.products.store') }}"
        enctype="multipart/form-data"
        class="space-y-6"
    >
        @csrf

        {{-- Thanh thao tác đầu trang --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    Tạo sản phẩm mới
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Các trường có dấu <span class="text-red-500">*</span> là bắt buộc.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ route('admin.products.index') }}"
                    class="
                        inline-flex items-center justify-center rounded-lg
                        border border-gray-300 bg-white px-4 py-2.5
                        text-sm font-semibold text-gray-700
                        transition hover:bg-gray-50
                    "
                >
                    Quay lại
                </a>

                <button
                    type="submit"
                    class="
                        inline-flex items-center justify-center rounded-lg
                        bg-pink-600 px-5 py-2.5 text-sm font-semibold
                        text-white transition hover:bg-pink-700
                        disabled:cursor-not-allowed disabled:opacity-60
                    "
                >
                    Lưu sản phẩm
                </button>
            </div>
        </div>

        {{-- Thông tin cơ bản --}}
        <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
            <div class="mb-5 border-b border-gray-100 pb-4">
                <h3 class="text-lg font-bold text-gray-900">
                    Thông tin cơ bản
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Khai báo tên, phân loại và trạng thái sản phẩm.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Tên sản phẩm <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        maxlength="255"
                        placeholder="Ví dụ: Serum dưỡng sáng da..."
                        class="
                            w-full rounded-lg border border-gray-300 px-4 py-2.5
                            text-sm focus:border-pink-500 focus:ring-pink-500
                        "
                    >

                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Slug
                    </label>

                    <input
                        id="slug"
                        type="text"
                        name="slug"
                        value="{{ old('slug') }}"
                        maxlength="255"
                        placeholder="Để trống để hệ thống tự tạo"
                        class="
                            w-full rounded-lg border border-gray-300 px-4 py-2.5
                            text-sm focus:border-pink-500 focus:ring-pink-500
                        "
                    >

                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Danh mục <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        required
                        class="
                            w-full rounded-lg border border-gray-300 px-3 py-2.5
                            text-sm focus:border-pink-500 focus:ring-pink-500
                        "
                    >
                        <option value="">-- Chọn danh mục --</option>

                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected((string) old('category_id') === (string) $category->id)
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="brand_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Thương hiệu
                    </label>

                    <select
                        id="brand_id"
                        name="brand_id"
                        class="
                            w-full rounded-lg border border-gray-300 px-3 py-2.5
                            text-sm focus:border-pink-500 focus:ring-pink-500
                        "
                    >
                        <option value="">-- Không chọn --</option>

                        @foreach ($brands as $brand)
                            <option
                                value="{{ $brand->id }}"
                                @selected((string) old('brand_id') === (string) $brand->id)
                            >
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="supplier_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Nhà cung cấp
                    </label>

                    <select
                        id="supplier_id"
                        name="supplier_id"
                        class="
                            w-full rounded-lg border border-gray-300 px-3 py-2.5
                            text-sm focus:border-pink-500 focus:ring-pink-500
                        "
                    >
                        <option value="">-- Không chọn --</option>

                        @foreach ($suppliers as $supplier)
                            <option
                                value="{{ $supplier->id }}"
                                @selected((string) old('supplier_id') === (string) $supplier->id)
                            >
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="skin_type" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Loại da
                    </label>

                    <input
                        id="skin_type"
                        type="text"
                        name="skin_type"
                        value="{{ old('skin_type') }}"
                        maxlength="255"
                        placeholder="Ví dụ: Da dầu, da khô, mọi loại da..."
                        class="
                            w-full rounded-lg border border-gray-300 px-4 py-2.5
                            text-sm focus:border-pink-500 focus:ring-pink-500
                        "
                    >
                </div>

                <div>
                    <label for="origin" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Xuất xứ
                    </label>

                    <input
                        id="origin"
                        type="text"
                        name="origin"
                        value="{{ old('origin') }}"
                        maxlength="255"
                        placeholder="Ví dụ: Hàn Quốc"
                        class="
                            w-full rounded-lg border border-gray-300 px-4 py-2.5
                            text-sm focus:border-pink-500 focus:ring-pink-500
                        "
                    >
                </div>

                <div class="lg:col-span-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-4">
                        <input type="hidden" name="status" value="0">

                        <input
                            type="checkbox"
                            name="status"
                            value="1"
                            @checked(old('status', 1))
                            class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                        >

                        <span>
                            <span class="block text-sm font-semibold text-gray-800">
                                Đang bán
                            </span>

                            <span class="mt-0.5 block text-xs text-gray-500">
                                Sản phẩm được hiển thị trên website khách hàng.
                            </span>
                        </span>
                    </label>

                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-4">
                        <input type="hidden" name="is_featured" value="0">

                        <input
                            type="checkbox"
                            name="is_featured"
                            value="1"
                            @checked(old('is_featured', 0))
                            class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                        >

                        <span>
                            <span class="block text-sm font-semibold text-gray-800">
                                Sản phẩm nổi bật
                            </span>

                            <span class="mt-0.5 block text-xs text-gray-500">
                                Ưu tiên hiển thị tại khu vực sản phẩm nổi bật.
                            </span>
                        </span>
                    </label>
                </div>
            </div>
        </section>

        {{-- Nội dung mô tả --}}
        <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
            <div class="mb-5 border-b border-gray-100 pb-4">
                <h3 class="text-lg font-bold text-gray-900">
                    Nội dung sản phẩm
                </h3>
            </div>

            <div class="space-y-5">
                <div>
                    <label for="short_description" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Mô tả ngắn
                    </label>

                    <textarea
                        id="short_description"
                        name="short_description"
                        rows="3"
                        placeholder="Tóm tắt ngắn về sản phẩm..."
                        class="
                            w-full rounded-lg border border-gray-300 px-4 py-3
                            text-sm focus:border-pink-500 focus:ring-pink-500
                        "
                    >{{ old('short_description') }}</textarea>
                </div>

                <div>
                    <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Mô tả chi tiết
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="8"
                        placeholder="Nhập nội dung chi tiết..."
                        class="
                            w-full rounded-lg border border-gray-300 px-4 py-3
                            text-sm focus:border-pink-500 focus:ring-pink-500
                        "
                    >{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div>
                        <label for="ingredient" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Thành phần
                        </label>

                        <textarea
                            id="ingredient"
                            name="ingredient"
                            rows="6"
                            placeholder="Nhập thành phần sản phẩm..."
                            class="
                                w-full rounded-lg border border-gray-300 px-4 py-3
                                text-sm focus:border-pink-500 focus:ring-pink-500
                            "
                        >{{ old('ingredient') }}</textarea>
                    </div>

                    <div>
                        <label for="usage" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Hướng dẫn sử dụng
                        </label>

                        <textarea
                            id="usage"
                            name="usage"
                            rows="6"
                            placeholder="Nhập hướng dẫn sử dụng..."
                            class="
                                w-full rounded-lg border border-gray-300 px-4 py-3
                                text-sm focus:border-pink-500 focus:ring-pink-500
                            "
                        >{{ old('usage') }}</textarea>
                    </div>
                </div>
            </div>
        </section>

        {{-- Hình ảnh --}}
        <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
            <div class="mb-5 border-b border-gray-100 pb-4">
                <h3 class="text-lg font-bold text-gray-900">
                    Hình ảnh sản phẩm
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Tối đa 20 ảnh, mỗi ảnh không quá 5 MB.
                </p>
            </div>

            <div>
                <label for="images" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Chọn nhiều ảnh
                </label>

                <input
                    id="images"
                    type="file"
                    name="images[]"
                    multiple
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="
                        block w-full rounded-lg border border-gray-300
                        bg-white px-3 py-2.5 text-sm text-gray-700
                        file:mr-4 file:rounded-md file:border-0
                        file:bg-pink-50 file:px-4 file:py-2
                        file:text-sm file:font-semibold file:text-pink-700
                        hover:file:bg-pink-100
                    "
                >

                <input id="thumbnail_index" type="hidden" name="thumbnail_index" value="{{ old('thumbnail_index', 0) }}">

                <p class="mt-2 text-xs text-gray-500">
                    Sau khi chọn ảnh, bấm vào một ảnh để đặt làm ảnh đại diện.
                </p>
            </div>

            <div
                id="image-preview-list"
                class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5"
            ></div>
        </section>

        {{-- Video --}}
        <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
            <div class="flex flex-col gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">
                        Video sản phẩm
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Nhập URL video hoặc tải file MP4, WEBM, MOV từ máy.
                    </p>
                </div>

                <button
                    id="add-video-button"
                    type="button"
                    class="
                        inline-flex items-center justify-center rounded-lg
                        border border-pink-200 bg-pink-50 px-4 py-2
                        text-sm font-semibold text-pink-700
                        transition hover:bg-pink-100
                    "
                >
                    + Thêm video
                </button>
            </div>

            <div id="video-list" class="mt-5 space-y-4">
                @foreach ($oldVideos as $videoIndex => $video)
                    <div data-video-row class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="mb-4 flex items-center justify-between">
                            <strong class="text-sm text-gray-800">
                                Video #{{ $loop->iteration }}
                            </strong>

                            <button
                                type="button"
                                data-remove-video
                                class="text-sm font-semibold text-red-600 hover:text-red-700"
                            >
                                Xóa
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                            <input
                                type="text"
                                name="videos[{{ $videoIndex }}][title]"
                                value="{{ $video['title'] ?? '' }}"
                                placeholder="Tiêu đề video"
                                class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                            >

                            <input
                                type="url"
                                name="videos[{{ $videoIndex }}][video_url]"
                                value="{{ $video['video_url'] ?? '' }}"
                                placeholder="https://..."
                                class="lg:col-span-2 rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                            >
                            <div class="lg:col-span-2">
    <label class="mb-1 block text-xs font-medium text-gray-600">
        Hoặc tải video từ máy
    </label>

    <input
        type="file"
        name="videos[{{ $videoIndex }}][video_file]"
        accept=".mp4,.webm,.mov,video/mp4,video/webm,video/quicktime"
        class="
            block w-full rounded-lg border border-gray-300
            bg-white px-3 py-2 text-sm
            file:mr-3 file:rounded-md file:border-0
            file:bg-pink-50 file:px-3 file:py-1.5
            file:text-xs file:font-semibold file:text-pink-700
        "
    >
</div>

                            <select
                                name="videos[{{ $videoIndex }}][type]"
                                class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                            >
                                <option value="intro" @selected(($video['type'] ?? 'intro') === 'intro')>Giới thiệu</option>
                                <option value="tutorial" @selected(($video['type'] ?? '') === 'tutorial')>Hướng dẫn</option>
                                <option value="review" @selected(($video['type'] ?? '') === 'review')>Đánh giá</option>
                            </select>
                        </div>

                        <input
                            type="hidden"
                            name="videos[{{ $videoIndex }}][sort_order]"
                            value="{{ $video['sort_order'] ?? $videoIndex }}"
                        >
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Biến thể --}}
        <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
            <div class="flex flex-col gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">
                        Biến thể sản phẩm
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Có thể bỏ qua nếu sản phẩm không có biến thể.
                    </p>
                </div>

                <button
                    id="add-variant-button"
                    type="button"
                    class="
                        inline-flex items-center justify-center rounded-lg
                        border border-pink-200 bg-pink-50 px-4 py-2
                        text-sm font-semibold text-pink-700
                        transition hover:bg-pink-100
                    "
                >
                    + Thêm biến thể
                </button>
            </div>

            <div id="variant-list" class="mt-5 space-y-4">
                @foreach ($oldVariants as $variantIndex => $variant)
                    <div
                        data-variant-row
                        data-variant-index="{{ $variantIndex }}"
                        class="rounded-lg border border-gray-200 bg-gray-50 p-4"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <strong class="text-sm text-gray-800">
                                Biến thể #{{ $loop->iteration }}
                            </strong>

                            <button
                                type="button"
                                data-remove-variant
                                class="text-sm font-semibold text-red-600 hover:text-red-700"
                            >
                                Xóa
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                            <div class="xl:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-600">
                                    Tên biến thể
                                </label>

                                <input
                                    type="text"
                                    name="variants[{{ $variantIndex }}][name]"
                                    value="{{ $variant['name'] ?? '' }}"
                                    placeholder="Ví dụ: Chai 30ml"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                                >
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">
                                    Mã biến thể
                                </label>

                                <input
                                    type="text"
                                    name="variants[{{ $variantIndex }}][sku]"
                                    value="{{ $variant['sku'] ?? '' }}"
                                    placeholder="VAR-001"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                                >
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">
                                    Giá
                                </label>

                                <input
                                    type="number"
                                    name="variants[{{ $variantIndex }}][price]"
                                    value="{{ $variant['price'] ?? '' }}"
                                    min="0"
                                    step="0.01"
                                    placeholder="0"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                                >
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">
                                    Giá so sánh
                                </label>

                                <input
                                    type="number"
                                    name="variants[{{ $variantIndex }}][compare_price]"
                                    value="{{ $variant['compare_price'] ?? '' }}"
                                    min="0"
                                    step="0.01"
                                    placeholder="0"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                                >
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">
                                    Khối lượng
                                </label>

                                <input
                                    type="number"
                                    name="variants[{{ $variantIndex }}][weight]"
                                    value="{{ $variant['weight'] ?? '' }}"
                                    min="0"
                                    placeholder="Gram"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                                >
                            </div>

                            <div class="md:col-span-2 xl:col-span-4">
                                <label class="mb-2 block text-xs font-medium text-gray-600">
                                    Giá trị thuộc tính
                                </label>

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                    @foreach ($variantAttributes as $attribute)
                                        <div class="rounded-lg border border-gray-200 bg-white p-3">
                                            <p class="mb-2 text-xs font-semibold text-gray-700">
                                                {{ $attribute->name }}
                                            </p>

                                            <div class="max-h-28 space-y-2 overflow-y-auto">
                                                @foreach ($variantValues->get($attribute->id, collect()) as $value)
                                                    <label class="flex items-center gap-2 text-xs text-gray-600">
                                                        <input
                                                            type="checkbox"
                                                            name="variants[{{ $variantIndex }}][value_ids][]"
                                                            value="{{ $value->id }}"
                                                            @checked(in_array($value->id, $variant['value_ids'] ?? []))
                                                            class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                                        >

                                                        {{ $value->value }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <label class="flex items-center gap-2 self-end pb-2">
                                <input type="hidden" name="variants[{{ $variantIndex }}][status]" value="0">

                                <input
                                    type="checkbox"
                                    name="variants[{{ $variantIndex }}][status]"
                                    value="1"
                                    @checked($variant['status'] ?? 1)
                                    class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                >

                                <span class="text-sm font-medium text-gray-700">
                                    Hoạt động
                                </span>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- SKU và tồn kho --}}
        <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
            <div class="flex flex-col gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">
                        SKU và tồn kho
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Sản phẩm bắt buộc phải có ít nhất một SKU.
                    </p>
                </div>

                <button
                    id="add-sku-button"
                    type="button"
                    class="
                        inline-flex items-center justify-center rounded-lg
                        border border-pink-200 bg-pink-50 px-4 py-2
                        text-sm font-semibold text-pink-700
                        transition hover:bg-pink-100
                    "
                >
                    + Thêm SKU
                </button>
            </div>

            <div id="sku-list" class="mt-5 space-y-5">
                @foreach ($oldSkus as $skuIndex => $sku)
                    <div data-sku-row class="rounded-xl border border-gray-200 p-4 sm:p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <strong class="text-sm text-gray-900">
                                SKU #{{ $loop->iteration }}
                            </strong>

                            <button
                                type="button"
                                data-remove-sku
                                class="text-sm font-semibold text-red-600 hover:text-red-700"
                            >
                                Xóa
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">
                                    Gắn với biến thể
                                </label>

                                <select
                                    name="skus[{{ $skuIndex }}][variant_index]"
                                    data-variant-select
                                    data-selected="{{ $sku['variant_index'] ?? '' }}"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                                >
                                    <option value="">Không gắn biến thể</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">
                                    Mã SKU <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="skus[{{ $skuIndex }}][sku_code]"
                                    value="{{ $sku['sku_code'] ?? '' }}"
                                    required
                                    placeholder="SKU-001"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                                >
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">
                                    Barcode
                                </label>

                                <input
                                    type="text"
                                    name="skus[{{ $skuIndex }}][barcode]"
                                    value="{{ $sku['barcode'] ?? '' }}"
                                    placeholder="Mã vạch"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                                >
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">
                                    Giá bán <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="number"
                                    name="skus[{{ $skuIndex }}][price]"
                                    value="{{ $sku['price'] ?? '' }}"
                                    min="0"
                                    step="0.01"
                                    required
                                    placeholder="0"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                                >
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">
                                    Giá vốn
                                </label>

                                <input
                                    type="number"
                                    name="skus[{{ $skuIndex }}][cost_price]"
                                    value="{{ $sku['cost_price'] ?? '' }}"
                                    min="0"
                                    step="0.01"
                                    placeholder="0"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                                >
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">
                                    Khối lượng
                                </label>

                                <input
                                    type="number"
                                    name="skus[{{ $skuIndex }}][weight]"
                                    value="{{ $sku['weight'] ?? '' }}"
                                    min="0"
                                    placeholder="Gram"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                                >
                            </div>

                            <label class="flex items-center gap-2 self-end pb-2">
                                <input type="hidden" name="skus[{{ $skuIndex }}][status]" value="0">

                                <input
                                    type="checkbox"
                                    name="skus[{{ $skuIndex }}][status]"
                                    value="1"
                                    @checked($sku['status'] ?? 1)
                                    class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                >

                                <span class="text-sm font-medium text-gray-700">
                                    Hoạt động
                                </span>
                            </label>
                        </div>

                        <div class="mt-5 overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Kho
                                        </th>

                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Địa chỉ
                                        </th>

                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Số lượng
                                        </th>

                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Tồn tối thiểu
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($warehouses as $warehouseIndex => $warehouse)
                                        @php
                                            $oldInventory = collect($sku['inventories'] ?? [])
                                                ->firstWhere('warehouse_id', $warehouse->id);
                                        @endphp

                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-800">
                                                {{ $warehouse->name }}

                                                <input
                                                    type="hidden"
                                                    name="skus[{{ $skuIndex }}][inventories][{{ $warehouseIndex }}][warehouse_id]"
                                                    value="{{ $warehouse->id }}"
                                                >
                                            </td>

                                            <td class="px-4 py-3 text-gray-500">
                                                {{ $warehouse->address ?: '—' }}
                                            </td>

                                            <td class="px-4 py-3">
                                                <input
                                                    type="number"
                                                    name="skus[{{ $skuIndex }}][inventories][{{ $warehouseIndex }}][quantity]"
                                                    value="{{ $oldInventory['quantity'] ?? 0 }}"
                                                    min="0"
                                                    class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-pink-500 focus:ring-pink-500"
                                                >
                                            </td>

                                            <td class="px-4 py-3">
                                                <input
                                                    type="number"
                                                    name="skus[{{ $skuIndex }}][inventories][{{ $warehouseIndex }}][minimum_stock]"
                                                    value="{{ $oldInventory['minimum_stock'] ?? 10 }}"
                                                    min="0"
                                                    class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-pink-500 focus:ring-pink-500"
                                                >
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Thanh thao tác cuối trang --}}
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a
                href="{{ route('admin.products.index') }}"
                class="
                    inline-flex items-center justify-center rounded-lg
                    border border-gray-300 bg-white px-4 py-2.5
                    text-sm font-semibold text-gray-700
                    transition hover:bg-gray-50
                "
            >
                Hủy
            </a>

            <button
                type="submit"
                class="
                    inline-flex items-center justify-center rounded-lg
                    bg-pink-600 px-5 py-2.5 text-sm font-semibold
                    text-white transition hover:bg-pink-700
                "
            >
                Lưu sản phẩm
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const warehouses = @json($warehouses);
            const variantAttributes = @json($variantAttributes);
            const groupedVariantValues = @json(
                $variantValues->map(fn ($items) => $items->values())
            );

            const imageInput = document.getElementById('images');
            const imagePreviewList = document.getElementById('image-preview-list');
            const thumbnailIndexInput = document.getElementById('thumbnail_index');

            const videoList = document.getElementById('video-list');
            const variantList = document.getElementById('variant-list');
            const skuList = document.getElementById('sku-list');

            let videoIndex = {{ count($oldVideos) }};
            let variantIndex = {{ count($oldVariants) }};
            let skuIndex = {{ count($oldSkus) }};

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function refreshSectionNumbers(selector, label) {
                document.querySelectorAll(selector).forEach(function (row, index) {
                    const title = row.querySelector('strong');

                    if (title) {
                        title.textContent = `${label} #${index + 1}`;
                    }
                });
            }

            function updateVariantSelects() {
                const variants = Array.from(
                    variantList.querySelectorAll('[data-variant-row]')
                ).map(function (row) {
                    const index = row.dataset.variantIndex;
                    const nameInput = row.querySelector(
                        `input[name="variants[${index}][name]"]`
                    );

                    return {
                        index,
                        name: nameInput?.value?.trim() || `Biến thể ${Number(index) + 1}`,
                    };
                });

                document.querySelectorAll('[data-variant-select]').forEach(function (select) {
                    const selected = select.value || select.dataset.selected || '';

                    select.innerHTML = '<option value="">Không gắn biến thể</option>';

                    variants.forEach(function (variant) {
                        const option = document.createElement('option');
                        option.value = variant.index;
                        option.textContent = variant.name;
                        option.selected = String(selected) === String(variant.index);
                        select.appendChild(option);
                    });

                    select.dataset.selected = '';
                });
            }

            imageInput?.addEventListener('change', function () {
                imagePreviewList.innerHTML = '';

                Array.from(imageInput.files).forEach(function (file, index) {
                    const reader = new FileReader();

                    reader.onload = function (event) {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.dataset.imageIndex = index;
                        button.className = [
                            'relative overflow-hidden rounded-lg border-2',
                            'border-transparent bg-gray-50 text-left',
                            'transition hover:border-pink-300',
                        ].join(' ');

                        button.innerHTML = `
                            <img
                                src="${event.target.result}"
                                alt="${escapeHtml(file.name)}"
                                class="h-36 w-full object-cover"
                            >

                            <span
                                data-thumbnail-label
                                class="
                                    absolute left-2 top-2 hidden rounded-full
                                    bg-pink-600 px-2.5 py-1 text-xs
                                    font-semibold text-white
                                "
                            >
                                Ảnh đại diện
                            </span>

                            <span class="block truncate px-3 py-2 text-xs text-gray-600">
                                ${escapeHtml(file.name)}
                            </span>
                        `;

                        button.addEventListener('click', function () {
                            thumbnailIndexInput.value = index;

                            imagePreviewList
                                .querySelectorAll('[data-thumbnail-label]')
                                .forEach(function (label) {
                                    label.classList.add('hidden');
                                });

                            imagePreviewList
                                .querySelectorAll('[data-image-index]')
                                .forEach(function (item) {
                                    item.classList.remove('border-pink-500');
                                    item.classList.add('border-transparent');
                                });

                            button.classList.remove('border-transparent');
                            button.classList.add('border-pink-500');
                            button
                                .querySelector('[data-thumbnail-label]')
                                .classList.remove('hidden');
                        });

                        imagePreviewList.appendChild(button);

                        if (
                            index === Number(thumbnailIndexInput.value || 0)
                            || (index === 0 && !thumbnailIndexInput.value)
                        ) {
                            button.click();
                        }
                    };

                    reader.readAsDataURL(file);
                });
            });

            document.getElementById('add-video-button')?.addEventListener('click', function () {
                const row = document.createElement('div');
                row.dataset.videoRow = '';
                row.className = 'rounded-lg border border-gray-200 bg-gray-50 p-4';

                row.innerHTML = `
                    <div class="mb-4 flex items-center justify-between">
                        <strong class="text-sm text-gray-800">Video mới</strong>

                        <button
                            type="button"
                            data-remove-video
                            class="text-sm font-semibold text-red-600 hover:text-red-700"
                        >
                            Xóa
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                        <input
                            type="text"
                            name="videos[${videoIndex}][title]"
                            placeholder="Tiêu đề video"
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        <input
                            type="url"
                            name="videos[${videoIndex}][video_url]"
                            placeholder="https://..."
                            class="lg:col-span-2 rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                Hoặc tải video từ máy
                            </label>

                            <input
                                type="file"
                                name="videos[${videoIndex}][video_file]"
                                accept=".mp4,.webm,.mov,video/mp4,video/webm,video/quicktime"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm"
                            >
                        </div>

                        <select
                            name="videos[${videoIndex}][type]"
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                            <option value="intro">Giới thiệu</option>
                            <option value="tutorial">Hướng dẫn</option>
                            <option value="review">Đánh giá</option>
                        </select>
                    </div>

                    <input
                        type="hidden"
                        name="videos[${videoIndex}][sort_order]"
                        value="${videoIndex}"
                    >
                `;

                videoList.appendChild(row);
                videoIndex++;
                refreshSectionNumbers('[data-video-row]', 'Video');
            });

            function buildAttributeGroups(index) {
                return variantAttributes.map(function (attribute) {
                    const values = groupedVariantValues[attribute.id] || [];

                    const valueHtml = values.map(function (value) {
                        return `
                            <label class="flex items-center gap-2 text-xs text-gray-600">
                                <input
                                    type="checkbox"
                                    name="variants[${index}][value_ids][]"
                                    value="${value.id}"
                                    class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                >
                                ${escapeHtml(value.value)}
                            </label>
                        `;
                    }).join('');

                    return `
                        <div class="rounded-lg border border-gray-200 bg-white p-3">
                            <p class="mb-2 text-xs font-semibold text-gray-700">
                                ${escapeHtml(attribute.name)}
                            </p>

                            <div class="max-h-28 space-y-2 overflow-y-auto">
                                ${valueHtml || '<p class="text-xs text-gray-400">Không có giá trị.</p>'}
                            </div>
                        </div>
                    `;
                }).join('');
            }

            document.getElementById('add-variant-button')?.addEventListener('click', function () {
                const currentIndex = variantIndex;
                const row = document.createElement('div');

                row.dataset.variantRow = '';
                row.dataset.variantIndex = currentIndex;
                row.className = 'rounded-lg border border-gray-200 bg-gray-50 p-4';

                row.innerHTML = `
                    <div class="mb-4 flex items-center justify-between">
                        <strong class="text-sm text-gray-800">Biến thể mới</strong>

                        <button
                            type="button"
                            data-remove-variant
                            class="text-sm font-semibold text-red-600 hover:text-red-700"
                        >
                            Xóa
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                        <div class="xl:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                Tên biến thể
                            </label>

                            <input
                                type="text"
                                name="variants[${currentIndex}][name]"
                                placeholder="Ví dụ: Chai 30ml"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                            >
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                Mã biến thể
                            </label>

                            <input
                                type="text"
                                name="variants[${currentIndex}][sku]"
                                placeholder="VAR-001"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                            >
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                Giá
                            </label>

                            <input
                                type="number"
                                name="variants[${currentIndex}][price]"
                                min="0"
                                step="0.01"
                                placeholder="0"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                            >
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                Giá so sánh
                            </label>

                            <input
                                type="number"
                                name="variants[${currentIndex}][compare_price]"
                                min="0"
                                step="0.01"
                                placeholder="0"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                            >
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                Khối lượng
                            </label>

                            <input
                                type="number"
                                name="variants[${currentIndex}][weight]"
                                min="0"
                                placeholder="Gram"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                            >
                        </div>

                        <div class="md:col-span-2 xl:col-span-4">
                            <label class="mb-2 block text-xs font-medium text-gray-600">
                                Giá trị thuộc tính
                            </label>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                ${buildAttributeGroups(currentIndex)}
                            </div>
                        </div>

                        <label class="flex items-center gap-2 self-end pb-2">
                            <input
                                type="hidden"
                                name="variants[${currentIndex}][status]"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="variants[${currentIndex}][status]"
                                value="1"
                                checked
                                class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                            >

                            <span class="text-sm font-medium text-gray-700">
                                Hoạt động
                            </span>
                        </label>
                    </div>
                `;

                variantList.appendChild(row);
                variantIndex++;
                refreshSectionNumbers('[data-variant-row]', 'Biến thể');
                updateVariantSelects();
            });

            function buildInventoryRows(index) {
                return warehouses.map(function (warehouse, warehouseIndex) {
                    return `
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-800">
                                ${escapeHtml(warehouse.name)}

                                <input
                                    type="hidden"
                                    name="skus[${index}][inventories][${warehouseIndex}][warehouse_id]"
                                    value="${warehouse.id}"
                                >
                            </td>

                            <td class="px-4 py-3 text-gray-500">
                                ${escapeHtml(warehouse.address || '—')}
                            </td>

                            <td class="px-4 py-3">
                                <input
                                    type="number"
                                    name="skus[${index}][inventories][${warehouseIndex}][quantity]"
                                    value="0"
                                    min="0"
                                    class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-pink-500 focus:ring-pink-500"
                                >
                            </td>

                            <td class="px-4 py-3">
                                <input
                                    type="number"
                                    name="skus[${index}][inventories][${warehouseIndex}][minimum_stock]"
                                    value="10"
                                    min="0"
                                    class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-pink-500 focus:ring-pink-500"
                                >
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            document.getElementById('add-sku-button')?.addEventListener('click', function () {
                const currentIndex = skuIndex;
                const row = document.createElement('div');

                row.dataset.skuRow = '';
                row.className = 'rounded-xl border border-gray-200 p-4 sm:p-5';

                row.innerHTML = `
                    <div class="mb-4 flex items-center justify-between">
                        <strong class="text-sm text-gray-900">SKU mới</strong>

                        <button
                            type="button"
                            data-remove-sku
                            class="text-sm font-semibold text-red-600 hover:text-red-700"
                        >
                            Xóa
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                Gắn với biến thể
                            </label>

                            <select
                                name="skus[${currentIndex}][variant_index]"
                                data-variant-select
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                            >
                                <option value="">Không gắn biến thể</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                Mã SKU <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="text"
                                name="skus[${currentIndex}][sku_code]"
                                required
                                placeholder="SKU-001"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                            >
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                Barcode
                            </label>

                            <input
                                type="text"
                                name="skus[${currentIndex}][barcode]"
                                placeholder="Mã vạch"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                            >
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                Giá bán <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="number"
                                name="skus[${currentIndex}][price]"
                                min="0"
                                step="0.01"
                                required
                                placeholder="0"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                            >
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                Giá vốn
                            </label>

                            <input
                                type="number"
                                name="skus[${currentIndex}][cost_price]"
                                min="0"
                                step="0.01"
                                placeholder="0"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                            >
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                Khối lượng
                            </label>

                            <input
                                type="number"
                                name="skus[${currentIndex}][weight]"
                                min="0"
                                placeholder="Gram"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                            >
                        </div>

                        <label class="flex items-center gap-2 self-end pb-2">
                            <input
                                type="hidden"
                                name="skus[${currentIndex}][status]"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="skus[${currentIndex}][status]"
                                value="1"
                                checked
                                class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                            >

                            <span class="text-sm font-medium text-gray-700">
                                Hoạt động
                            </span>
                        </label>
                    </div>

                    <div class="mt-5 overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Kho
                                    </th>

                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Địa chỉ
                                    </th>

                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Số lượng
                                    </th>

                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Tồn tối thiểu
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                ${buildInventoryRows(currentIndex)}
                            </tbody>
                        </table>
                    </div>
                `;

                skuList.appendChild(row);
                skuIndex++;
                refreshSectionNumbers('[data-sku-row]', 'SKU');
                updateVariantSelects();
            });

            document.addEventListener('click', function (event) {
                const removeVideoButton = event.target.closest('[data-remove-video]');

                if (removeVideoButton) {
                    removeVideoButton.closest('[data-video-row]')?.remove();
                    refreshSectionNumbers('[data-video-row]', 'Video');
                    return;
                }

                const removeVariantButton = event.target.closest('[data-remove-variant]');

                if (removeVariantButton) {
                    removeVariantButton.closest('[data-variant-row]')?.remove();
                    refreshSectionNumbers('[data-variant-row]', 'Biến thể');
                    updateVariantSelects();
                    return;
                }

                const removeSkuButton = event.target.closest('[data-remove-sku]');

                if (removeSkuButton) {
                    const skuRows = document.querySelectorAll('[data-sku-row]');

                    if (skuRows.length <= 1) {
                        window.alert('Sản phẩm phải có ít nhất một SKU.');
                        return;
                    }

                    removeSkuButton.closest('[data-sku-row]')?.remove();
                    refreshSectionNumbers('[data-sku-row]', 'SKU');
                }
            });

            variantList.addEventListener('input', function (event) {
                if (event.target.matches('input[name$="[name]"]')) {
                    updateVariantSelects();
                }
            });

            updateVariantSelects();

            document.getElementById('product-create-form')?.addEventListener('submit', function () {
                this.querySelectorAll('button[type="submit"]').forEach(function (button) {
                    button.disabled = true;
                    button.textContent = 'Đang lưu...';
                });
            });
        });
    </script>
@endpush
