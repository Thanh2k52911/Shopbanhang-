@extends('admin.layouts.master')
@section('title', $page->title)
@section('page-title', 'Chi tiết trang nội dung')
@section('page-description', 'Xem trước nội dung và cấu hình hiển thị.')
@section('content')
<div class="space-y-6">
    @if (session('success'))<div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>@endif
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><h2 class="text-2xl font-bold text-gray-900">{{ $page->title }}</h2><p class="mt-1 text-sm text-gray-500">/{{ $page->slug }}</p></div>
        <div class="flex flex-wrap gap-3">
            @if ($page->status)<a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="rounded-lg border border-blue-200 px-4 py-2.5 text-sm font-semibold text-blue-600">Xem phía khách</a>@endif
            <a href="{{ route('admin.pages.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">Danh sách</a>
            <a href="{{ route('admin.pages.edit', $page) }}" class="rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white">Chỉnh sửa</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <article class="rounded-xl border border-gray-200 bg-white p-6 xl:col-span-2">
            @if ($page->thumbnail)<img src="{{ asset('storage/' . $page->thumbnail) }}" alt="{{ $page->title }}" class="mb-6 max-h-80 w-full rounded-xl object-cover">@endif
            <div class="prose max-w-none">{!! $page->content ?: '<p class="text-gray-500">Trang chưa có nội dung.</p>' !!}</div>
        </article>
        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-6 text-sm">
                <h3 class="mb-4 font-bold text-gray-900">Cấu hình</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Loại</dt><dd class="font-medium text-gray-900">{{ $pageTypes[$page->page_type] ?? $page->page_type }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Trạng thái</dt><dd class="font-medium {{ $page->status ? 'text-green-600' : 'text-gray-500' }}">{{ $page->status ? 'Đang bật' : 'Đang tắt' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Header</dt><dd>{{ $page->show_in_header ? 'Có' : 'Không' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Footer</dt><dd>{{ $page->show_in_footer ? 'Có' : 'Không' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Thứ tự</dt><dd>{{ $page->sort_order }}</dd></div>
                </dl>
            </section>
            <section class="rounded-xl border border-gray-200 bg-white p-6 text-sm">
                <h3 class="mb-4 font-bold text-gray-900">SEO và lịch sử</h3>
                <p><span class="text-gray-500">Meta title:</span> {{ $page->meta_title ?: '—' }}</p>
                <p class="mt-3"><span class="text-gray-500">Người tạo:</span> {{ $page->creator?->name ?: 'Hệ thống' }}</p>
                <p class="mt-3"><span class="text-gray-500">Cập nhật bởi:</span> {{ $page->updater?->name ?: 'Hệ thống' }}</p>
                <p class="mt-3"><span class="text-gray-500">Cập nhật lúc:</span> {{ optional($page->updated_at)->format('d/m/Y H:i') }}</p>
            </section>
        </aside>
    </div>
</div>
@endsection
