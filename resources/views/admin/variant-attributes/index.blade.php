@extends('admin.layouts.master')

@section('title', 'Thuộc tính biến thể')
@section('page-title', 'Thuộc tính biến thể')
@section('page-description', 'Quản lý thuộc tính và giá trị dùng chung cho biến thể sản phẩm.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Thuộc tính biến thể</h2>
            <p class="mt-1 text-sm text-gray-500">Ví dụ: Dung tích, Màu sắc, Tone màu, Loại da.</p>
        </div>
        <a href="{{ route('admin.variant-attributes.create') }}" class="rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-pink-700">+ Thêm thuộc tính</a>
    </div>

    @foreach (['success' => 'green', 'error' => 'red'] as $key => $color)
        @if (session($key))
            <div class="rounded-xl border border-{{ $color }}-200 bg-{{ $color }}-50 px-4 py-3 text-sm font-medium text-{{ $color }}-700">{{ session($key) }}</div>
        @endif
    @endforeach

    <div class="grid gap-4 sm:grid-cols-3">
        <article class="rounded-xl border bg-white p-5"><p class="text-sm text-gray-500">Thuộc tính</p><strong class="mt-2 block text-2xl">{{ number_format($statistics['attributes']) }}</strong></article>
        <article class="rounded-xl border bg-white p-5"><p class="text-sm text-gray-500">Tổng giá trị</p><strong class="mt-2 block text-2xl text-pink-600">{{ number_format($statistics['values']) }}</strong></article>
        <article class="rounded-xl border bg-white p-5"><p class="text-sm text-gray-500">Giá trị đang dùng</p><strong class="mt-2 block text-2xl text-green-600">{{ number_format($statistics['used_values']) }}</strong></article>
    </div>

    <section class="rounded-xl border bg-white p-5">
        <form method="GET" class="flex flex-col gap-3 sm:flex-row">
            <input name="keyword" value="{{ request('keyword') }}" placeholder="Tìm tên thuộc tính..." class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
            <button class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white">Tìm kiếm</button>
            <a href="{{ route('admin.variant-attributes.index') }}" class="rounded-lg border px-5 py-2.5 text-center text-sm font-semibold">Đặt lại</a>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Thuộc tính</th><th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Giá trị</th><th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Đang dùng</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Thao tác</th></tr></thead>
                <tbody class="divide-y">
                    @forelse ($attributes as $attribute)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $attribute->name }}</td>
                            <td class="px-5 py-4 text-center">{{ number_format($attribute->values_count) }}</td>
                            <td class="px-5 py-4 text-center">{{ number_format($attribute->used_values_count) }}</td>
                            <td class="px-5 py-4"><div class="flex justify-end gap-3 text-sm font-semibold"><a class="text-blue-600" href="{{ route('admin.variant-attributes.show', $attribute) }}">Chi tiết</a><a class="text-amber-600" href="{{ route('admin.variant-attributes.edit', $attribute) }}">Sửa</a></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-12 text-center text-gray-500">Chưa có thuộc tính nào.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($attributes->hasPages())<div class="border-t px-5 py-4">{{ $attributes->links() }}</div>@endif
    </section>
</div>
@endsection
