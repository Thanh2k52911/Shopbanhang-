@extends('admin.layouts.master')

@section('title', 'Chỉnh sửa sản phẩm')
@section('page-title', 'Chỉnh sửa sản phẩm')
@section('page-description', 'Cập nhật thông tin, hình ảnh, video, biến thể, SKU và tồn kho.')

@section('content')
@php
    $formVariants = old('variants');

    if ($formVariants === null) {
        $formVariants = $variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'name' => $variant->name,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'compare_price' => $variant->compare_price,
                'weight' => $variant->weight,
                'status' => (int) $variant->status,
                'value_ids' => $variant->value_ids,
            ];
        })->values()->all();
    }

    $formVideos = old('videos');

    if ($formVideos === null) {
        $formVideos = $videos->map(function ($video) {
            return [
                'id' => $video->id,
                'title' => $video->title,
                'video_url' => $video->video_url,
                'type' => $video->type,
                'sort_order' => $video->sort_order,
            ];
        })->values()->all();
    }

    $formSkus = old('skus');

    if ($formSkus === null) {
        $variantIndexById = collect($formVariants)
            ->mapWithKeys(fn ($variant, $index) => [(string) ($variant['id'] ?? '') => $index]);

        $formSkus = $skus->map(function ($sku) use ($variantIndexById) {
            return [
                'id' => $sku->id,
                'variant_index' => $sku->variant_id
                    ? $variantIndexById->get((string) $sku->variant_id)
                    : null,
                'sku_code' => $sku->sku_code,
                'barcode' => $sku->barcode,
                'price' => $sku->price,
                'cost_price' => $sku->cost_price,
                'weight' => $sku->weight,
                'status' => (int) $sku->status,
                'inventories' => collect($sku->inventories)->map(function ($inventory) {
                    return [
                        'id' => $inventory->id,
                        'warehouse_id' => $inventory->warehouse_id,
                        'quantity' => $inventory->quantity,
                        'minimum_stock' => $inventory->minimum_stock,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }
@endphp

<form
    id="product-edit-form"
    method="POST"
    action="{{ route('admin.products.update', $product->id) }}"
    enctype="multipart/form-data"
    class="space-y-6"
>
    @csrf
    @method('PUT')

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">
                Chỉnh sửa: {{ $product->name }}
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                ID: {{ $product->id }} · {{ $product->slug }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a
                href="{{ route('admin.products.show', $product->id) }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
            >
                Quay lại
            </a>

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-pink-700"
            >
                Lưu thay đổi
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="font-semibold text-red-700">Dữ liệu chưa hợp lệ.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
        <div class="mb-5 border-b border-gray-100 pb-4">
            <h3 class="text-lg font-bold text-gray-900">Thông tin cơ bản</h3>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div class="lg:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Tên sản phẩm <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $product->name) }}"
                    required
                    maxlength="255"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Slug</label>
                <input
                    type="text"
                    name="slug"
                    value="{{ old('slug', $product->slug) }}"
                    maxlength="255"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Danh mục <span class="text-red-500">*</span>
                </label>
                <select
                    name="category_id"
                    required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">-- Chọn danh mục --</option>
                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected((string) old('category_id', $product->category_id) === (string) $category->id)
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Thương hiệu</label>
                <select
                    name="brand_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">-- Không chọn --</option>
                    @foreach ($brands as $brand)
                        <option
                            value="{{ $brand->id }}"
                            @selected((string) old('brand_id', $product->brand_id) === (string) $brand->id)
                        >
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Nhà cung cấp</label>
                <select
                    name="supplier_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">-- Không chọn --</option>
                    @foreach ($suppliers as $supplier)
                        <option
                            value="{{ $supplier->id }}"
                            @selected((string) old('supplier_id', $product->supplier_id) === (string) $supplier->id)
                        >
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Loại da</label>
                <input
                    type="text"
                    name="skin_type"
                    value="{{ old('skin_type', $product->skin_type) }}"
                    maxlength="255"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Xuất xứ</label>
                <input
                    type="text"
                    name="origin"
                    value="{{ old('origin', $product->origin) }}"
                    maxlength="255"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div class="lg:col-span-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-4">
                    <input type="hidden" name="status" value="0">
                    <input
                        type="checkbox"
                        name="status"
                        value="1"
                        @checked(old('status', $product->status))
                        class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                    >
                    <span class="text-sm font-semibold text-gray-800">Đang bán</span>
                </label>

                <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-4">
                    <input type="hidden" name="is_featured" value="0">
                    <input
                        type="checkbox"
                        name="is_featured"
                        value="1"
                        @checked(old('is_featured', $product->is_featured))
                        class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                    >
                    <span class="text-sm font-semibold text-gray-800">Sản phẩm nổi bật</span>
                </label>
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
        <div class="mb-5 border-b border-gray-100 pb-4">
            <h3 class="text-lg font-bold text-gray-900">Nội dung sản phẩm</h3>
        </div>

        <div class="space-y-5">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Mô tả ngắn</label>
                <textarea
                    name="short_description"
                    rows="3"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"
                >{{ old('short_description', $product->short_description) }}</textarea>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Mô tả chi tiết</label>
                <textarea
                    name="description"
                    rows="8"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"
                >{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Thành phần</label>
                    <textarea
                        name="ingredient"
                        rows="6"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"
                    >{{ old('ingredient', $product->ingredient) }}</textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Hướng dẫn sử dụng</label>
                    <textarea
                        name="usage"
                        rows="6"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"
                    >{{ old('usage', $product->usage) }}</textarea>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
        <div class="mb-5 border-b border-gray-100 pb-4">
            <h3 class="text-lg font-bold text-gray-900">Hình ảnh sản phẩm</h3>
            <p class="mt-1 text-sm text-gray-500">
                Chọn ảnh cần xóa, chọn một ảnh cũ làm ảnh đại diện hoặc tải ảnh mới.
            </p>
        </div>

        @if ($images->isNotEmpty())
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($images as $image)
                    <article class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                        <img
                            src="{{ asset('storage/' . ltrim($image->image_path, '/')) }}"
                            alt="{{ $product->name }}"
                            class="aspect-square w-full object-cover"
                        >

                        <div class="space-y-2 p-3">
                            <label class="flex items-center gap-2 text-xs text-gray-700">
                                <input
                                    type="radio"
                                    name="existing_thumbnail_id"
                                    value="{{ $image->id }}"
                                    @checked(
                                        (string) old(
                                            'existing_thumbnail_id',
                                            $images->firstWhere('is_thumbnail', 1)?->id
                                        ) === (string) $image->id
                                    )
                                    class="border-gray-300 text-pink-600 focus:ring-pink-500"
                                >
                                Ảnh đại diện
                            </label>

                            <label class="flex items-center gap-2 text-xs text-red-600">
                                <input
                                    type="checkbox"
                                    name="delete_image_ids[]"
                                    value="{{ $image->id }}"
                                    @checked(in_array($image->id, old('delete_image_ids', [])))
                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                >
                                Xóa ảnh
                            </label>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500">Sản phẩm chưa có ảnh.</p>
        @endif

        <div class="mt-5">
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Tải thêm ảnh mới</label>
            <input
                id="new-images"
                type="file"
                name="images[]"
                multiple
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm"
            >
            <input type="hidden" id="new-thumbnail-index" name="thumbnail_index" value="{{ old('thumbnail_index', '') }}">
            <div id="new-image-preview" class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5"></div>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Video sản phẩm</h3>
                <p class="mt-1 text-sm text-gray-500">Cập nhật URL video, thay file cũ hoặc tải video mới từ máy.</p>
            </div>
            <button
                id="add-video"
                type="button"
                class="rounded-lg border border-pink-200 bg-pink-50 px-4 py-2 text-sm font-semibold text-pink-700 hover:bg-pink-100"
            >
                + Thêm video
            </button>
        </div>

        <div id="video-list" class="mt-5 space-y-4">
            @foreach ($formVideos as $videoIndex => $video)
                <div data-video-row class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <input type="hidden" name="videos[{{ $videoIndex }}][id]" value="{{ $video['id'] ?? '' }}">
                    <div class="mb-4 flex items-center justify-between">
                        <strong class="text-sm text-gray-800">Video #{{ $loop->iteration }}</strong>
                        <button type="button" data-remove-video class="text-sm font-semibold text-red-600">Xóa</button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                        <input
                            type="text"
                            name="videos[{{ $videoIndex }}][title]"
                            value="{{ $video['title'] ?? '' }}"
                            placeholder="Tiêu đề"
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                        >
                        <input
                            type="url"
                            name="videos[{{ $videoIndex }}][video_url]"
                            value="{{ \Illuminate\Support\Str::startsWith(
                                $video['video_url'] ?? '',
                                ['http://', 'https://']
                            ) ? ($video['video_url'] ?? '') : '' }}"
                            placeholder="https://..."
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm lg:col-span-2"
                        >

                        @if (
                            ! empty($video['video_url'])
                            && ! \Illuminate\Support\Str::startsWith(
                                $video['video_url'],
                                ['http://', 'https://']
                            )
                        )
                            <div class="lg:col-span-4 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-700">
                                File hiện tại:
                                <a
                                    href="{{ asset('storage/' . ltrim($video['video_url'], '/')) }}"
                                    target="_blank"
                                    class="font-semibold underline"
                                >
                                    {{ basename($video['video_url']) }}
                                </a>
                            </div>
                        @endif

                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                Thay bằng file video mới
                            </label>

                            <input
                                type="file"
                                name="videos[{{ $videoIndex }}][video_file]"
                                accept=".mp4,.webm,.mov,video/mp4,video/webm,video/quicktime"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm"
                            >
                        </div>

                        <select
                            name="videos[{{ $videoIndex }}][type]"
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
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

    <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Biến thể</h3>
                <p class="mt-1 text-sm text-gray-500">Sửa, thêm hoặc xóa biến thể.</p>
            </div>
            <button
                id="add-variant"
                type="button"
                class="rounded-lg border border-pink-200 bg-pink-50 px-4 py-2 text-sm font-semibold text-pink-700 hover:bg-pink-100"
            >
                + Thêm biến thể
            </button>
        </div>

        <div id="variant-list" class="mt-5 space-y-4">
            @foreach ($formVariants as $variantIndex => $variant)
                <div
                    data-variant-row
                    data-variant-index="{{ $variantIndex }}"
                    class="rounded-lg border border-gray-200 bg-gray-50 p-4"
                >
                    <input type="hidden" name="variants[{{ $variantIndex }}][id]" value="{{ $variant['id'] ?? '' }}">

                    <div class="mb-4 flex items-center justify-between">
                        <strong class="text-sm text-gray-800">Biến thể #{{ $loop->iteration }}</strong>
                        <button type="button" data-remove-variant class="text-sm font-semibold text-red-600">Xóa</button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                        <input
                            type="text"
                            name="variants[{{ $variantIndex }}][name]"
                            value="{{ $variant['name'] ?? '' }}"
                            placeholder="Tên biến thể"
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm xl:col-span-2"
                        >
                        <input
                            type="text"
                            name="variants[{{ $variantIndex }}][sku]"
                            value="{{ $variant['sku'] ?? '' }}"
                            placeholder="Mã biến thể"
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                        >
                        <input
                            type="number"
                            name="variants[{{ $variantIndex }}][price]"
                            value="{{ $variant['price'] ?? '' }}"
                            min="0"
                            step="0.01"
                            placeholder="Giá"
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                        >
                        <input
                            type="number"
                            name="variants[{{ $variantIndex }}][compare_price]"
                            value="{{ $variant['compare_price'] ?? '' }}"
                            min="0"
                            step="0.01"
                            placeholder="Giá so sánh"
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                        >
                        <input
                            type="number"
                            name="variants[{{ $variantIndex }}][weight]"
                            value="{{ $variant['weight'] ?? '' }}"
                            min="0"
                            placeholder="Khối lượng"
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                        >

                        <div class="md:col-span-2 xl:col-span-4">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                @foreach ($variantAttributes as $attribute)
                                    <div class="rounded-lg border border-gray-200 bg-white p-3">
                                        <p class="mb-2 text-xs font-semibold text-gray-700">{{ $attribute->name }}</p>
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
                            <span class="text-sm text-gray-700">Hoạt động</span>
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">SKU và tồn kho</h3>
                <p class="mt-1 text-sm text-gray-500">Sản phẩm phải có ít nhất một SKU.</p>
            </div>
            <button
                id="add-sku"
                type="button"
                class="rounded-lg border border-pink-200 bg-pink-50 px-4 py-2 text-sm font-semibold text-pink-700 hover:bg-pink-100"
            >
                + Thêm SKU
            </button>
        </div>

        <div id="sku-list" class="mt-5 space-y-5">
            @foreach ($formSkus as $skuIndex => $sku)
                <div data-sku-row class="rounded-xl border border-gray-200 p-4 sm:p-5">
                    <input type="hidden" name="skus[{{ $skuIndex }}][id]" value="{{ $sku['id'] ?? '' }}">

                    <div class="mb-4 flex items-center justify-between">
                        <strong class="text-sm text-gray-900">SKU #{{ $loop->iteration }}</strong>
                        <button type="button" data-remove-sku class="text-sm font-semibold text-red-600">Xóa</button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <select
                            name="skus[{{ $skuIndex }}][variant_index]"
                            data-variant-select
                            data-selected="{{ $sku['variant_index'] ?? '' }}"
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                        >
                            <option value="">Không gắn biến thể</option>
                        </select>

                        <input
                            type="text"
                            name="skus[{{ $skuIndex }}][sku_code]"
                            value="{{ $sku['sku_code'] ?? '' }}"
                            required
                            placeholder="Mã SKU"
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                        >
                        <input
                            type="text"
                            name="skus[{{ $skuIndex }}][barcode]"
                            value="{{ $sku['barcode'] ?? '' }}"
                            placeholder="Barcode"
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                        >
                        <input
                            type="number"
                            name="skus[{{ $skuIndex }}][price]"
                            value="{{ $sku['price'] ?? '' }}"
                            min="0"
                            step="0.01"
                            required
                            placeholder="Giá bán"
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                        >
                        <input
                            type="number"
                            name="skus[{{ $skuIndex }}][cost_price]"
                            value="{{ $sku['cost_price'] ?? '' }}"
                            min="0"
                            step="0.01"
                            placeholder="Giá vốn"
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                        >
                        <input
                            type="number"
                            name="skus[{{ $skuIndex }}][weight]"
                            value="{{ $sku['weight'] ?? '' }}"
                            min="0"
                            placeholder="Khối lượng"
                            class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm"
                        >

                        <label class="flex items-center gap-2 self-end pb-2">
                            <input type="hidden" name="skus[{{ $skuIndex }}][status]" value="0">
                            <input
                                type="checkbox"
                                name="skus[{{ $skuIndex }}][status]"
                                value="1"
                                @checked($sku['status'] ?? 1)
                                class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                            >
                            <span class="text-sm text-gray-700">Hoạt động</span>
                        </label>
                    </div>

                    <div class="mt-5 overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Kho</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Địa chỉ</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Số lượng</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Tồn tối thiểu</th>
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
                                                name="skus[{{ $skuIndex }}][inventories][{{ $warehouseIndex }}][id]"
                                                value="{{ $oldInventory['id'] ?? '' }}"
                                            >
                                            <input
                                                type="hidden"
                                                name="skus[{{ $skuIndex }}][inventories][{{ $warehouseIndex }}][warehouse_id]"
                                                value="{{ $warehouse->id }}"
                                            >
                                        </td>
                                        <td class="px-4 py-3 text-gray-500">{{ $warehouse->address ?: '—' }}</td>
                                        <td class="px-4 py-3">
                                            <input
                                                type="number"
                                                name="skus[{{ $skuIndex }}][inventories][{{ $warehouseIndex }}][quantity]"
                                                value="{{ $oldInventory['quantity'] ?? 0 }}"
                                                min="0"
                                                class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm"
                                            >
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                type="number"
                                                name="skus[{{ $skuIndex }}][inventories][{{ $warehouseIndex }}][minimum_stock]"
                                                value="{{ $oldInventory['minimum_stock'] ?? 10 }}"
                                                min="0"
                                                class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm"
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

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ route('admin.products.show', $product->id) }}"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
        >
            Hủy
        </a>
        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-pink-700"
        >
            Lưu thay đổi
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const warehouses = @json($warehouses);
    const variantAttributes = @json($variantAttributes);
    const groupedVariantValues = @json($variantValues->map(fn ($items) => $items->values()));

    const videoList = document.getElementById('video-list');
    const variantList = document.getElementById('variant-list');
    const skuList = document.getElementById('sku-list');

    let videoIndex = {{ count($formVideos) }};
    let variantIndex = {{ count($formVariants) }};
    let skuIndex = {{ count($formSkus) }};

    const escapeHtml = value => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    function refreshNumbers(selector, label) {
        document.querySelectorAll(selector).forEach((row, index) => {
            const strong = row.querySelector('strong');
            if (strong) strong.textContent = `${label} #${index + 1}`;
        });
    }

    function updateVariantSelects() {
        const options = Array.from(
            variantList.querySelectorAll('[data-variant-row]')
        ).map(row => {
            const index = row.dataset.variantIndex;
            const input = row.querySelector(`input[name="variants[${index}][name]"]`);

            return {
                index,
                name: input?.value?.trim() || `Biến thể ${Number(index) + 1}`,
            };
        });

        document.querySelectorAll('[data-variant-select]').forEach(select => {
            const selected = select.value || select.dataset.selected || '';
            select.innerHTML = '<option value="">Không gắn biến thể</option>';

            options.forEach(item => {
                const option = document.createElement('option');
                option.value = item.index;
                option.textContent = item.name;
                option.selected = String(selected) === String(item.index);
                select.appendChild(option);
            });

            select.dataset.selected = '';
        });
    }

    const newImages = document.getElementById('new-images');
    const preview = document.getElementById('new-image-preview');
    const thumbnailIndex = document.getElementById('new-thumbnail-index');

    newImages?.addEventListener('change', function () {
        preview.innerHTML = '';

        Array.from(newImages.files).forEach((file, index) => {
            const reader = new FileReader();

            reader.onload = event => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'overflow-hidden rounded-lg border-2 border-transparent bg-gray-50';
                button.innerHTML = `
                    <img src="${event.target.result}" class="h-32 w-full object-cover">
                    <span class="block truncate px-3 py-2 text-xs">${escapeHtml(file.name)}</span>
                `;

                button.addEventListener('click', () => {
                    thumbnailIndex.value = index;
                    document.querySelectorAll('input[name="existing_thumbnail_id"]').forEach(item => {
                        item.checked = false;
                    });
                    preview.querySelectorAll('button').forEach(item => {
                        item.classList.remove('border-pink-500');
                        item.classList.add('border-transparent');
                    });
                    button.classList.remove('border-transparent');
                    button.classList.add('border-pink-500');
                });

                preview.appendChild(button);
            };

            reader.readAsDataURL(file);
        });
    });

    document.getElementById('add-video')?.addEventListener('click', function () {
        const row = document.createElement('div');
        row.dataset.videoRow = '';
        row.className = 'rounded-lg border border-gray-200 bg-gray-50 p-4';
        row.innerHTML = `
            <div class="mb-4 flex items-center justify-between">
                <strong class="text-sm text-gray-800">Video mới</strong>
                <button type="button" data-remove-video class="text-sm font-semibold text-red-600">Xóa</button>
            </div>
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                <input type="text" name="videos[${videoIndex}][title]" placeholder="Tiêu đề" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                <input type="url" name="videos[${videoIndex}][video_url]" placeholder="https://..." class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm lg:col-span-2">
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
                <select name="videos[${videoIndex}][type]" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="intro">Giới thiệu</option>
                    <option value="tutorial">Hướng dẫn</option>
                    <option value="review">Đánh giá</option>
                </select>
            </div>
            <input type="hidden" name="videos[${videoIndex}][sort_order]" value="${videoIndex}">
        `;
        videoList.appendChild(row);
        videoIndex++;
        refreshNumbers('[data-video-row]', 'Video');
    });

    function attributeHtml(index) {
        return variantAttributes.map(attribute => {
            const values = groupedVariantValues[attribute.id] || [];

            return `
                <div class="rounded-lg border border-gray-200 bg-white p-3">
                    <p class="mb-2 text-xs font-semibold text-gray-700">${escapeHtml(attribute.name)}</p>
                    <div class="max-h-28 space-y-2 overflow-y-auto">
                        ${values.map(value => `
                            <label class="flex items-center gap-2 text-xs text-gray-600">
                                <input type="checkbox" name="variants[${index}][value_ids][]" value="${value.id}" class="rounded border-gray-300 text-pink-600">
                                ${escapeHtml(value.value)}
                            </label>
                        `).join('')}
                    </div>
                </div>
            `;
        }).join('');
    }

    document.getElementById('add-variant')?.addEventListener('click', function () {
        const index = variantIndex;
        const row = document.createElement('div');
        row.dataset.variantRow = '';
        row.dataset.variantIndex = index;
        row.className = 'rounded-lg border border-gray-200 bg-gray-50 p-4';
        row.innerHTML = `
            <div class="mb-4 flex items-center justify-between">
                <strong class="text-sm text-gray-800">Biến thể mới</strong>
                <button type="button" data-remove-variant class="text-sm font-semibold text-red-600">Xóa</button>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                <input type="text" name="variants[${index}][name]" placeholder="Tên biến thể" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm xl:col-span-2">
                <input type="text" name="variants[${index}][sku]" placeholder="Mã biến thể" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                <input type="number" name="variants[${index}][price]" min="0" step="0.01" placeholder="Giá" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                <input type="number" name="variants[${index}][compare_price]" min="0" step="0.01" placeholder="Giá so sánh" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                <input type="number" name="variants[${index}][weight]" min="0" placeholder="Khối lượng" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                <div class="md:col-span-2 xl:col-span-4">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        ${attributeHtml(index)}
                    </div>
                </div>
                <label class="flex items-center gap-2 self-end pb-2">
                    <input type="hidden" name="variants[${index}][status]" value="0">
                    <input type="checkbox" name="variants[${index}][status]" value="1" checked class="rounded border-gray-300 text-pink-600">
                    <span class="text-sm text-gray-700">Hoạt động</span>
                </label>
            </div>
        `;
        variantList.appendChild(row);
        variantIndex++;
        refreshNumbers('[data-variant-row]', 'Biến thể');
        updateVariantSelects();
    });

    function inventoryRows(index) {
        return warehouses.map((warehouse, warehouseIndex) => `
            <tr>
                <td class="px-4 py-3 font-medium text-gray-800">
                    ${escapeHtml(warehouse.name)}
                    <input type="hidden" name="skus[${index}][inventories][${warehouseIndex}][warehouse_id]" value="${warehouse.id}">
                </td>
                <td class="px-4 py-3 text-gray-500">${escapeHtml(warehouse.address || '—')}</td>
                <td class="px-4 py-3">
                    <input type="number" name="skus[${index}][inventories][${warehouseIndex}][quantity]" value="0" min="0" class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </td>
                <td class="px-4 py-3">
                    <input type="number" name="skus[${index}][inventories][${warehouseIndex}][minimum_stock]" value="10" min="0" class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </td>
            </tr>
        `).join('');
    }

    document.getElementById('add-sku')?.addEventListener('click', function () {
        const index = skuIndex;
        const row = document.createElement('div');
        row.dataset.skuRow = '';
        row.className = 'rounded-xl border border-gray-200 p-4 sm:p-5';
        row.innerHTML = `
            <div class="mb-4 flex items-center justify-between">
                <strong class="text-sm text-gray-900">SKU mới</strong>
                <button type="button" data-remove-sku class="text-sm font-semibold text-red-600">Xóa</button>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <select name="skus[${index}][variant_index]" data-variant-select class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="">Không gắn biến thể</option>
                </select>
                <input type="text" name="skus[${index}][sku_code]" required placeholder="Mã SKU" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                <input type="text" name="skus[${index}][barcode]" placeholder="Barcode" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                <input type="number" name="skus[${index}][price]" min="0" step="0.01" required placeholder="Giá bán" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                <input type="number" name="skus[${index}][cost_price]" min="0" step="0.01" placeholder="Giá vốn" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                <input type="number" name="skus[${index}][weight]" min="0" placeholder="Khối lượng" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                <label class="flex items-center gap-2 self-end pb-2">
                    <input type="hidden" name="skus[${index}][status]" value="0">
                    <input type="checkbox" name="skus[${index}][status]" value="1" checked class="rounded border-gray-300 text-pink-600">
                    <span class="text-sm text-gray-700">Hoạt động</span>
                </label>
            </div>
            <div class="mt-5 overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Kho</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Địa chỉ</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Số lượng</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Tồn tối thiểu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">${inventoryRows(index)}</tbody>
                </table>
            </div>
        `;
        skuList.appendChild(row);
        skuIndex++;
        refreshNumbers('[data-sku-row]', 'SKU');
        updateVariantSelects();
    });

    document.addEventListener('click', function (event) {
        const videoButton = event.target.closest('[data-remove-video]');
        if (videoButton) {
            videoButton.closest('[data-video-row]')?.remove();
            refreshNumbers('[data-video-row]', 'Video');
            return;
        }

        const variantButton = event.target.closest('[data-remove-variant]');
        if (variantButton) {
            variantButton.closest('[data-variant-row]')?.remove();
            refreshNumbers('[data-variant-row]', 'Biến thể');
            updateVariantSelects();
            return;
        }

        const skuButton = event.target.closest('[data-remove-sku]');
        if (skuButton) {
            if (document.querySelectorAll('[data-sku-row]').length <= 1) {
                alert('Sản phẩm phải có ít nhất một SKU.');
                return;
            }

            skuButton.closest('[data-sku-row]')?.remove();
            refreshNumbers('[data-sku-row]', 'SKU');
        }
    });

    variantList.addEventListener('input', function (event) {
        if (event.target.matches('input[name$="[name]"]')) {
            updateVariantSelects();
        }
    });

    updateVariantSelects();

    document.getElementById('product-edit-form')?.addEventListener('submit', function () {
        this.querySelectorAll('button[type="submit"]').forEach(button => {
            button.disabled = true;
            button.textContent = 'Đang lưu...';
        });
    });
});
</script>
@endpush
