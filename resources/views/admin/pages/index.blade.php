@extends('admin.layouts.master')

@section('title', 'Quản lý trang nội dung')
@section('page-title', 'Quản lý trang nội dung')
@section('page-description', 'Tạo và quản lý trang giới thiệu, chính sách và hướng dẫn.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Trang nội dung</h2>
            <p class="mt-1 text-sm text-gray-500">Các trang đang bật có thể hiển thị ở header, footer và phía khách hàng.</p>
        </div>

        <a href="{{ route('admin.pages.create') }}" class="inline-flex justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-pink-700">
            + Thêm trang
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['Tổng trang', $statistics['total'], 'text-gray-900'],
            ['Đang bật', $statistics['active'], 'text-green-600'],
            ['Đang tắt', $statistics['inactive'], 'text-gray-500'],
            ['Hiện header', $statistics['header'], 'text-blue-600'],
            ['Hiện footer', $statistics['footer'], 'text-pink-600'],
        ] as [$label, $value, $class])
            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <strong class="mt-2 block text-2xl {{ $class }}">{{ number_format($value) }}</strong>
            </article>
        @endforeach
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form method="GET" action="{{ route('admin.pages.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="xl:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Tìm kiếm</label>
                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tiêu đề, slug hoặc tiêu đề SEO..." class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Loại trang</label>
                <select name="page_type" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    @foreach ($pageTypes as $value => $label)
                        <option value="{{ $value }}" @selected(request('page_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Trạng thái</label>
                <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="1" @selected((string) request('status') === '1')>Đang bật</option>
                    <option value="0" @selected((string) request('status') === '0')>Đang tắt</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Vị trí</label>
                <select name="placement" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="header" @selected(request('placement') === 'header')>Header</option>
                    <option value="footer" @selected(request('placement') === 'footer')>Footer</option>
                    <option value="none" @selected(request('placement') === 'none')>Không hiển thị menu</option>
                </select>
            </div>
            <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-5">
                <button type="submit" class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white">Lọc dữ liệu</button>
                <a href="{{ route('admin.pages.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Trang</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Loại</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Vị trí</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Trạng thái</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pages as $page)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.pages.show', $page) }}" class="font-semibold text-gray-900 hover:text-pink-600">{{ $page->title }}</a>
                                <p class="mt-1 text-xs text-gray-500">/{{ $page->slug }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $pageTypes[$page->page_type] ?? $page->page_type }}</td>
                            <td class="px-5 py-4 text-center text-xs">
                                @if ($page->show_in_header)<span class="rounded-full bg-blue-100 px-2.5 py-1 font-semibold text-blue-700">Header</span>@endif
                                @if ($page->show_in_footer)<span class="rounded-full bg-pink-100 px-2.5 py-1 font-semibold text-pink-700">Footer</span>@endif
                                @if (! $page->show_in_header && ! $page->show_in_footer)<span class="text-gray-400">—</span>@endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $page->status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $page->status ? 'Đang bật' : 'Đang tắt' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex gap-2">
                                    <a href="{{ route('admin.pages.show', $page) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700">XEM</a>
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="rounded-lg border border-pink-200 px-3 py-2 text-xs font-bold text-pink-600">SỬA</a>
                                    <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Chuyển trang này vào thùng rác?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-600">XÓA</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-gray-500">Chưa có trang nội dung.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pages->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">{{ $pages->links() }}</div>
        @endif
    </section>
</div>
@endsection
