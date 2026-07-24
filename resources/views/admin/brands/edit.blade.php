@extends('admin.layouts.master')

@section('title', 'Chỉnh sửa thương hiệu')
@section('page-title', 'Chỉnh sửa thương hiệu')
@section('page-description', 'Cập nhật thông tin, thumbnail, quốc gia, website và trạng thái thương hiệu.')

@section('content')
<form
    id="brand-edit-form"
    method="POST"
    action="{{ route('admin.brands.update', $brand->id) }}"
    enctype="multipart/form-data"
    class="space-y-6"
>
    @csrf
    @method('PUT')

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Chỉnh sửa: {{ $brand->name }}
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                ID: {{ $brand->id }} · {{ $brand->slug }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('admin.brands.show', $brand->id) }}"
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
                        Thông tin cơ bản
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Cập nhật tên, slug, quốc gia, website và mô tả.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Tên thương hiệu <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name', $brand->name) }}"
                            required
                            maxlength="120"
                            autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
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
                            value="{{ old('slug', $brand->slug) }}"
                            maxlength="255"
                            autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="country" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Quốc gia
                        </label>

                        <input
                            id="country"
                            type="text"
                            name="country"
                            value="{{ old('country', $brand->country) }}"
                            maxlength="100"
                            placeholder="Ví dụ: Pháp"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('country')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label for="website" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Website
                        </label>

                        <input
                            id="website"
                            type="url"
                            name="website"
                            value="{{ old('website', $brand->website) }}"
                            maxlength="255"
                            placeholder="https://example.com"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >

                        @error('website')
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
                            rows="7"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"
                        >{{ old('description', $brand->description) }}</textarea>

                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <div class="mb-5 border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Thumbnail thương hiệu
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Thay thumbnail hiện tại hoặc xóa ảnh cũ.
                    </p>
                </div>

                @if ($brand->thumbnail)
                    <div
                        id="current-thumbnail-wrapper"
                        class="mb-5 overflow-hidden rounded-xl border border-gray-200 bg-gray-50 p-3"
                    >
                        <img
                            src="{{ asset('storage/' . ltrim($brand->thumbnail, '/')) }}"
                            alt="{{ $brand->name }}"
                            class="mx-auto aspect-square w-full max-w-sm rounded-lg object-cover"
                        >

                        <label class="mt-3 flex items-center gap-2 text-sm font-medium text-red-600">
                            <input
                                id="delete-thumbnail"
                                type="checkbox"
                                name="delete_thumbnail"
                                value="1"
                                @checked(old('delete_thumbnail'))
                                class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                            >
                            Xóa thumbnail hiện tại
                        </label>
                    </div>
                @endif

                <div class="space-y-4">
                    <div>
                        <label for="thumbnail" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Chọn thumbnail mới
                        </label>

                        <input
                            id="thumbnail"
                            type="file"
                            name="thumbnail"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-pink-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-pink-700 hover:file:bg-pink-100"
                        >

                        <p class="mt-2 text-xs text-gray-500">
                            JPG, JPEG, PNG hoặc WEBP, tối đa 5 MB.
                        </p>

                        @error('thumbnail')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div
                        id="thumbnail-preview-wrapper"
                        class="hidden overflow-hidden rounded-xl border border-gray-200 bg-gray-50 p-3"
                    >
                        <img
                            id="thumbnail-preview"
                            src=""
                            alt="Xem trước thumbnail mới"
                            class="mx-auto aspect-square w-full max-w-sm rounded-lg object-cover"
                        >

                        <div class="mt-3 flex items-center justify-between gap-3">
                            <p
                                id="thumbnail-filename"
                                class="min-w-0 truncate text-xs text-gray-500"
                            ></p>

                            <button
                                id="remove-thumbnail-preview"
                                type="button"
                                class="shrink-0 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100"
                            >
                                Bỏ ảnh mới
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Trạng thái
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Kiểm soát khả năng hiển thị của thương hiệu.
                </p>

                <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4 transition hover:bg-gray-50">
                    <input type="hidden" name="status" value="0">

                    <input
                        type="checkbox"
                        name="status"
                        value="1"
                        @checked(old('status', $brand->status))
                        class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                    >

                    <span>
                        <span class="block text-sm font-semibold text-gray-900">
                            Kích hoạt thương hiệu
                        </span>

                        <span class="mt-1 block text-xs leading-5 text-gray-500">
                            Cho phép thương hiệu xuất hiện trên website khách hàng.
                        </span>
                    </span>
                </label>

                @error('status')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
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
                        value="{{ old('sort_order', $brand->sort_order) }}"
                        min="0"
                        step="1"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                    >

                    @error('sort_order')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <section class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                <h3 class="font-bold text-blue-900">
                    Lưu ý
                </h3>

                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-blue-800">
                    <li>Nếu chọn ảnh mới, ảnh cũ sẽ được xóa sau khi cập nhật thành công.</li>
                    <li>Website phải có đầy đủ http:// hoặc https://.</li>
                    <li>Không thể xóa thương hiệu khi vẫn còn sản phẩm đang liên kết.</li>
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
                            {{ \Carbon\Carbon::parse($brand->created_at)->format('d/m/Y H:i') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Cập nhật gần nhất
                        </dt>

                        <dd class="mt-1 font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($brand->updated_at)->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ route('admin.brands.show', $brand->id) }}"
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
        'brand-edit-form'
    );

    const thumbnailInput = document.getElementById(
        'thumbnail'
    );

    const previewWrapper = document.getElementById(
        'thumbnail-preview-wrapper'
    );

    const previewImage = document.getElementById(
        'thumbnail-preview'
    );

    const previewFilename = document.getElementById(
        'thumbnail-filename'
    );

    const removePreviewButton = document.getElementById(
        'remove-thumbnail-preview'
    );

    const deleteThumbnailCheckbox =
        document.getElementById(
            'delete-thumbnail'
        );

    const clearThumbnailPreview = function () {
        if (thumbnailInput) {
            thumbnailInput.value = '';
        }

        if (previewImage) {
            previewImage.removeAttribute('src');
        }

        if (previewFilename) {
            previewFilename.textContent = '';
        }

        previewWrapper?.classList.add('hidden');
    };

    thumbnailInput?.addEventListener(
        'change',
        function () {
            const file =
                thumbnailInput.files?.[0];

            if (!file) {
                clearThumbnailPreview();
                return;
            }

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp',
            ];

            if (!allowedTypes.includes(file.type)) {
                window.alert(
                    'Thumbnail phải có định dạng JPG, JPEG, PNG hoặc WEBP.'
                );

                clearThumbnailPreview();

                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                window.alert(
                    'Thumbnail không được lớn hơn 5 MB.'
                );

                clearThumbnailPreview();

                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                if (previewImage) {
                    previewImage.src =
                        event.target.result;
                }

                if (previewFilename) {
                    previewFilename.textContent =
                        file.name;
                }

                previewWrapper?.classList.remove(
                    'hidden'
                );

                if (deleteThumbnailCheckbox) {
                    deleteThumbnailCheckbox.checked =
                        false;
                }
            };

            reader.readAsDataURL(file);
        }
    );

    removePreviewButton?.addEventListener(
        'click',
        clearThumbnailPreview
    );

    deleteThumbnailCheckbox?.addEventListener(
        'change',
        function () {
            if (deleteThumbnailCheckbox.checked) {
                clearThumbnailPreview();
            }
        }
    );

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
