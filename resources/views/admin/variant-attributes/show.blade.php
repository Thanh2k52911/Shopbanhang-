@extends('admin.layouts.master')
@section('title', $variantAttribute->name)
@section('page-title', 'Chi tiết thuộc tính')
@section('content')
<div class="space-y-6">
    @if (session('success'))<div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>@endif

    <section class="rounded-xl border bg-white p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="text-sm text-gray-500">Thuộc tính</p><h2 class="mt-1 text-2xl font-bold">{{ $variantAttribute->name }}</h2></div>
            <div class="flex flex-wrap gap-3"><a href="{{ route('admin.variant-values.create', $variantAttribute) }}" class="rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white">+ Thêm giá trị</a><a href="{{ route('admin.variant-attributes.edit', $variantAttribute) }}" class="rounded-lg border px-4 py-2.5 text-sm font-semibold">Sửa thuộc tính</a></div>
        </div>
    </section>

    <section class="overflow-hidden rounded-xl border bg-white">
        <div class="border-b px-5 py-4"><h3 class="font-bold">Các giá trị</h3></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y"><thead class="bg-gray-50"><tr><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Giá trị</th><th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Số biến thể dùng</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Thao tác</th></tr></thead>
            <tbody class="divide-y">
                @forelse($variantAttribute->values as $value)
                    <tr><td class="px-5 py-4 font-medium">{{ $value->value }}</td><td class="px-5 py-4 text-center">{{ number_format($value->variants_count) }}</td><td class="px-5 py-4"><div class="flex justify-end gap-3"><a class="text-sm font-semibold text-amber-600" href="{{ route('admin.variant-values.edit', $value) }}">Sửa</a><form method="POST" action="{{ route('admin.variant-values.destroy', $value) }}" onsubmit="return confirm('Xóa giá trị này?')">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-600">Xóa</button></form></div></td></tr>
                @empty<tr><td colspan="3" class="px-5 py-12 text-center text-gray-500">Chưa có giá trị.</td></tr>@endforelse
            </tbody></table>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.variant-attributes.destroy', $variantAttribute) }}" onsubmit="return confirm('Xóa thuộc tính và toàn bộ giá trị chưa sử dụng?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600">Xóa thuộc tính</button></form>
</div>
@endsection
