@extends('admin.layouts.master')

@section('title', 'Thêm danh mục')
@section('page-title', 'Thêm danh mục')
@section('page-description', 'Tạo danh mục mới, khai báo danh mục cha, thumbnail và trạng thái hiển thị.')

@section('content')
<form id="category-create-form" method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Tạo danh mục mới</h2>
            <p class="mt-1 text-sm text-gray-500">
                Các trường có dấu <span class="font-semibold text-red-500">*</span> là bắt buộc.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.categories.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Quay lại
            </a>

            <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700 disabled:cursor-not-allowed disabled:opacity-60">
                Lưu danh mục
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

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <div class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <div class="mb-5 border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-900">Thông tin cơ bản</h3>
                    <p class="mt-1 text-sm text-gray-500">Khai báo tên, slug và danh mục cha.</p>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Tên danh mục <span class="text-red-500">*</span>
                        </label>

                        <input id="name" type="text" name="name" value="{{ old('name') }}" required maxlength="150"
                               autocomplete="off" placeholder="Ví dụ: Chăm sóc da"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">

                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="slug" class="mb-1.5 block text-sm font-medium text-gray-700">Slug</label>

                        <input id="slug" type="text" name="slug" value="{{ old('slug') }}" maxlength="180"
                               autocomplete="off" placeholder="Để trống để hệ thống tự tạo"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">

                        <p class="mt-1 text-xs text-gray-500">
                            Ví dụ: <span class="font-medium">cham-soc-da</span>
                        </p>

                        @error('slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="parent_id" class="mb-1.5 block text-sm font-medium text-gray-700">Danh mục cha</label>

                        <select id="parent_id" name="parent_id"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
                            <option value="">-- Không có, đây là danh mục gốc --</option>

                            @foreach ($parentCategories as $parentCategory)
                                <option value="{{ $parentCategory->id }}"
                                        @selected((string) old('parent_id') === (string) $parentCategory->id)>
                                    {{ $parentCategory->parent_id ? '— ' : '' }}{{ $parentCategory->name }}
                                </option>
                            @endforeach
                        </select>

                        <p class="mt-1 text-xs text-gray-500">
                            Chọn danh mục cha nếu muốn tạo danh mục con.
                        </p>

                        @error('parent_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700">Mô tả</label>

                        <textarea id="description" name="description" rows="7"
                                  placeholder="Nhập mô tả cho danh mục..."
                                  class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500">{{ old('description') }}</textarea>

                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <div class="mb-5 border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-900">Thumbnail danh mục</h3>
                    <p class="mt-1 text-sm text-gray-500">Hỗ trợ JPG, JPEG, PNG và WEBP, tối đa 5 MB.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="thumbnail" class="mb-1.5 block text-sm font-medium text-gray-700">Chọn hình ảnh</label>

                        <input id="thumbnail" type="file" name="thumbnail"
                               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                               class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-pink-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-pink-700 hover:file:bg-pink-100">

                        @error('thumbnail')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="thumbnail-preview-wrapper"
                         class="hidden overflow-hidden rounded-xl border border-gray-200 bg-gray-50 p-3">
                        <img id="thumbnail-preview" src="" alt="Xem trước thumbnail"
                             class="mx-auto aspect-square w-full max-w-sm rounded-lg object-cover">

                        <div class="mt-3 flex items-center justify-between gap-3">
                            <p id="thumbnail-filename" class="min-w-0 truncate text-xs text-gray-500"></p>

                            <button id="remove-thumbnail-preview" type="button"
                                    class="shrink-0 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                Bỏ ảnh
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Trạng thái</h3>
                <p class="mt-1 text-sm text-gray-500">Kiểm soát khả năng hiển thị của danh mục.</p>

                <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4 transition hover:bg-gray-50">
                    <input type="hidden" name="status" value="0">

                    <input type="checkbox" name="status" value="1" @checked(old('status', 1))
                           class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500">

                    <span>
                        <span class="block text-sm font-semibold text-gray-900">Hiển thị danh mục</span>
                        <span class="mt-1 block text-xs leading-5 text-gray-500">
                            Cho phép danh mục xuất hiện trên website khách hàng.
                        </span>
                    </span>
                </label>

                @error('status')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Thứ tự hiển thị</h3>
                <p class="mt-1 text-sm text-gray-500">Số nhỏ hơn sẽ được ưu tiên hiển thị trước.</p>

                <div class="mt-4">
                    <label for="sort_order" class="mb-1.5 block text-sm font-medium text-gray-700">Sort order</label>

                    <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                           min="0" step="1"
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">

                    @error('sort_order')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <section class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                <h3 class="font-bold text-blue-900">Gợi ý</h3>

                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-blue-800">
                    <li>Danh mục gốc phù hợp cho nhóm lớn như “Chăm sóc da”.</li>
                    <li>Danh mục con phù hợp cho nhóm nhỏ như “Sữa rửa mặt”.</li>
                    <li>Thumbnail nên sử dụng ảnh vuông để hiển thị đẹp hơn.</li>
                </ul>
            </section>
        </aside>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.categories.index') }}"
           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
            Hủy
        </a>

        <button type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700 disabled:cursor-not-allowed disabled:opacity-60">
            Lưu danh mục
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('category-create-form');
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const thumbnailInput = document.getElementById('thumbnail');
    const previewWrapper = document.getElementById('thumbnail-preview-wrapper');
    const previewImage = document.getElementById('thumbnail-preview');
    const previewFilename = document.getElementById('thumbnail-filename');
    const removePreviewButton = document.getElementById('remove-thumbnail-preview');

    let slugWasEdited = Boolean(slugInput?.value.trim());

    const createSlug = function (value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/Đ/g, 'D')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    };

    slugInput?.addEventListener('input', function () {
        slugWasEdited = slugInput.value.trim() !== '';
    });

    nameInput?.addEventListener('input', function () {
        if (!slugInput || slugWasEdited) {
            return;
        }

        slugInput.value = createSlug(nameInput.value);
    });

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

    thumbnailInput?.addEventListener('change', function () {
        const file = thumbnailInput.files?.[0];

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
            window.alert('Thumbnail phải có định dạng JPG, JPEG, PNG hoặc WEBP.');
            clearThumbnailPreview();
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            window.alert('Thumbnail không được lớn hơn 5 MB.');
            clearThumbnailPreview();
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {
            if (previewImage) {
                previewImage.src = event.target.result;
            }

            if (previewFilename) {
                previewFilename.textContent = file.name;
            }

            previewWrapper?.classList.remove('hidden');
        };

        reader.readAsDataURL(file);
    });

    removePreviewButton?.addEventListener('click', clearThumbnailPreview);

    form?.addEventListener('submit', function () {
        form.querySelectorAll('button[type="submit"]').forEach(function (button) {
            button.disabled = true;
            button.textContent = 'Đang lưu...';
        });
    });
});
</script>
@endpush
