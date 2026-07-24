@php($editing = isset($page))
<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <section class="space-y-5 rounded-xl border border-gray-200 bg-white p-6 xl:col-span-2">
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Tiêu đề <span class="text-red-500">*</span></label>
            <input id="title" type="text" name="title" value="{{ old('title', $page->title ?? '') }}" required maxlength="255" class="w-full rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Slug <span class="text-red-500">*</span></label>
            <input id="slug" type="text" name="slug" value="{{ old('slug', $page->slug ?? '') }}" required maxlength="255" class="w-full rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500">
            <p class="mt-1 text-xs text-gray-500">Để trống lúc nhập tiêu đề, hệ thống sẽ tự tạo slug.</p>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nội dung HTML</label>
            <textarea name="content" rows="18" class="w-full rounded-lg border-gray-300 font-mono text-sm focus:border-pink-500 focus:ring-pink-500">{{ old('content', $page->content ?? '') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Chỉ quản trị viên đáng tin cậy nên nhập HTML tại đây.</p>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Ảnh đại diện</label>
            <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            @if ($editing && $page->thumbnail)
                <div class="mt-3 flex items-center gap-4">
                    <img src="{{ asset('storage/' . $page->thumbnail) }}" alt="{{ $page->title }}" class="h-24 w-40 rounded-lg border object-cover">
                    <label class="inline-flex items-center gap-2 text-sm text-red-600"><input type="checkbox" name="remove_thumbnail" value="1"> Xóa ảnh hiện tại</label>
                </div>
            @endif
        </div>
    </section>

    <aside class="space-y-6">
        <section class="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
            <h3 class="font-bold text-gray-900">Hiển thị</h3>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Loại trang</label>
                <select name="page_type" required class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    @foreach ($pageTypes as $value => $label)
                        <option value="{{ $value }}" @selected(old('page_type', $page->page_type ?? 'normal') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Thứ tự</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order ?? 0) }}" min="0" class="w-full rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500">
            </div>
            <label class="flex items-center gap-3 text-sm text-gray-700"><input type="checkbox" name="status" value="1" @checked(old('status', $page->status ?? true))> Đang hoạt động</label>
            <label class="flex items-center gap-3 text-sm text-gray-700"><input type="checkbox" name="show_in_header" value="1" @checked(old('show_in_header', $page->show_in_header ?? false))> Hiển thị ở header</label>
            <label class="flex items-center gap-3 text-sm text-gray-700"><input type="checkbox" name="show_in_footer" value="1" @checked(old('show_in_footer', $page->show_in_footer ?? false))> Hiển thị ở footer</label>
        </section>

        <section class="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
            <h3 class="font-bold text-gray-900">SEO</h3>
            <div><label class="mb-1.5 block text-sm font-semibold text-gray-700">Meta title</label><input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title ?? '') }}" maxlength="255" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"></div>
            <div><label class="mb-1.5 block text-sm font-semibold text-gray-700">Meta description</label><textarea name="meta_description" rows="4" maxlength="1000" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">{{ old('meta_description', $page->meta_description ?? '') }}</textarea></div>
            <div><label class="mb-1.5 block text-sm font-semibold text-gray-700">Meta keywords</label><input type="text" name="meta_keywords" value="{{ old('meta_keywords', $page->meta_keywords ?? '') }}" maxlength="500" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"></div>
            <div><label class="mb-1.5 block text-sm font-semibold text-gray-700">Template</label><input type="text" name="template" value="{{ old('template', $page->template ?? '') }}" maxlength="100" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"></div>
        </section>
    </aside>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const title = document.getElementById('title');
    const slug = document.getElementById('slug');
    let slugEdited = slug?.value.trim() !== '';

    slug?.addEventListener('input', function () { slugEdited = slug.value.trim() !== ''; });
    title?.addEventListener('input', function () {
        if (slugEdited || !slug) return;
        slug.value = title.value
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .toLowerCase().replace(/đ/g, 'd')
            .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    });
});
</script>
@endpush
