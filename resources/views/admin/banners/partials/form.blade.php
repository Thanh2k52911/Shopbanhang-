@php
    $isEdit = isset($banner);
@endphp

<div class="banner-admin-form grid grid-cols-1 gap-6 2xl:grid-cols-[minmax(0,1fr)_320px]">
    <div class="space-y-6">
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900">
                Thông tin banner
            </h3>

            <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Tên quản trị *
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $banner->name ?? '') }}"
                        required
                        maxlength="255"
                        class="admin-input"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Tiêu đề
                    </label>

                    <input
                        type="text"
                        name="title"
                        value="{{ old('title', $banner->title ?? '') }}"
                        maxlength="255"
                        class="admin-input"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Nội dung nút
                    </label>

                    <input
                        type="text"
                        name="button_text"
                        value="{{ old('button_text', $banner->button_text ?? '') }}"
                        maxlength="100"
                        placeholder="Ví dụ: Mua ngay"
                        class="admin-input"
                    >
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Phụ đề
                    </label>

                    <textarea
                        name="subtitle"
                        rows="4"
                        maxlength="1000"
                        class="admin-textarea"
                    >{{ old('subtitle', $banner->subtitle ?? '') }}</textarea>
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Liên kết
                    </label>

                    <input
                        type="text"
                        name="link_url"
                        value="{{ old('link_url', $banner->link_url ?? '') }}"
                        maxlength="2048"
                        placeholder="/products hoặc https://example.com"
                        class="admin-input"
                    >
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900">
                Hình ảnh
            </h3>

            <div class="mt-5 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Ảnh desktop {{ $isEdit ? '' : '*' }}
                    </label>

                    @if ($isEdit && $banner->desktop_image)
                        <img
                            src="{{ asset('storage/' . ltrim($banner->desktop_image, '/')) }}"
                            alt="{{ $banner->name }}"
                            class="mb-3 aspect-[16/7] w-full rounded-xl border border-gray-200 object-cover"
                        >
                    @endif

                    <input
                        id="desktop_image"
                        type="file"
                        name="desktop_image"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        {{ $isEdit ? '' : 'required' }}
                        class="admin-file-input"
                    >

                    <img
                        id="desktop-preview"
                        class="mt-3 hidden aspect-[16/7] w-full rounded-xl border border-gray-200 object-cover"
                        alt="Xem trước ảnh desktop"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Ảnh mobile
                    </label>

                    @if ($isEdit && $banner->mobile_image)
                        <img
                            src="{{ asset('storage/' . ltrim($banner->mobile_image, '/')) }}"
                            alt="{{ $banner->name }}"
                            class="mb-3 aspect-[4/5] w-full max-w-xs rounded-xl border border-gray-200 object-cover"
                        >

                        <label class="mb-3 flex items-center gap-2 text-sm font-medium text-red-600">
                            <input
                                type="checkbox"
                                name="delete_mobile_image"
                                value="1"
                                @checked(old('delete_mobile_image'))
                                class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                            >
                            Xóa ảnh mobile hiện tại
                        </label>
                    @endif

                    <input
                        id="mobile_image"
                        type="file"
                        name="mobile_image"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        class="admin-file-input"
                    >

                    <img
                        id="mobile-preview"
                        class="mt-3 hidden aspect-[4/5] w-full max-w-xs rounded-xl border border-gray-200 object-cover"
                        alt="Xem trước ảnh mobile"
                    >
                </div>
            </div>

            <p class="mt-4 text-xs text-gray-500">
                Hỗ trợ JPG, JPEG, PNG, WEBP; tối đa 10 MB mỗi ảnh.
            </p>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900">
                Lịch hiển thị
            </h3>

            <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Bắt đầu
                    </label>

                    <input
                        id="start_at"
                        type="datetime-local"
                        name="start_at"
                        value="{{ old(
                            'start_at',
                            isset($banner) && $banner->start_at
                                ? \Carbon\Carbon::parse($banner->start_at)->format('Y-m-d\TH:i')
                                : ''
                        ) }}"
                        class="admin-input"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Kết thúc
                    </label>

                    <input
                        id="end_at"
                        type="datetime-local"
                        name="end_at"
                        value="{{ old(
                            'end_at',
                            isset($banner) && $banner->end_at
                                ? \Carbon\Carbon::parse($banner->end_at)->format('Y-m-d\TH:i')
                                : ''
                        ) }}"
                        class="admin-input"
                    >
                </div>
            </div>

            <p id="date-error" class="mt-3 hidden text-sm text-red-600">
                Thời gian kết thúc phải sau thời gian bắt đầu.
            </p>
        </section>
    </div>

    <aside class="space-y-6">
        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900">
                Vị trí hiển thị
            </h3>

            <div class="mt-4 space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Vị trí *
                    </label>

                    <select
                        name="position"
                        required
                        class="admin-select"
                    >
                        @foreach ($positions as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(old('position', $banner->position ?? 'home_slider') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Mở liên kết *
                    </label>

                    <select
                        name="target"
                        required
                        class="admin-select"
                    >
                        @foreach ($targets as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(old('target', $banner->target ?? '_self') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Thứ tự
                    </label>

                    <input
                        type="number"
                        name="sort_order"
                        value="{{ old('sort_order', $banner->sort_order ?? 0) }}"
                        min="0"
                        step="1"
                        class="admin-input"
                    >
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900">
                Trạng thái
            </h3>

            <label class="mt-4 flex gap-3 rounded-lg border border-gray-200 p-4">
                <input type="hidden" name="status" value="0">

                <input
                    type="checkbox"
                    name="status"
                    value="1"
                    @checked(old('status', $banner->status ?? 1))
                    class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                >

                <span>
                    <strong class="block text-sm text-gray-900">
                        Kích hoạt banner
                    </strong>

                    <span class="mt-1 block text-xs text-gray-500">
                        Banner chỉ hiển thị khi đang bật và nằm trong thời gian hiệu lực.
                    </span>
                </span>
            </label>
        </section>

        <section class="rounded-xl border border-blue-200 bg-blue-50 p-5">
            <h3 class="font-bold text-blue-900">
                Gợi ý kích thước
            </h3>

            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-blue-800">
                <li>Desktop: tỷ lệ ngang rộng, ví dụ 1920 × 700.</li>
                <li>Mobile: tỷ lệ dọc, ví dụ 800 × 1000.</li>
                <li>Nên tối ưu ảnh trước khi tải lên.</li>
            </ul>
        </section>
    </aside>
</div>
