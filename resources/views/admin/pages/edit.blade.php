@extends('admin.layouts.master')
@section('title', 'Sửa trang nội dung')
@section('page-title', 'Sửa trang nội dung')
@section('page-description', 'Cập nhật nội dung, SEO và vị trí hiển thị.')
@section('content')
<form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf @method('PUT')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><h2 class="text-2xl font-bold text-gray-900">{{ $page->title }}</h2><p class="mt-1 text-sm text-gray-500">Cập nhật lần cuối: {{ optional($page->updated_at)->format('d/m/Y H:i') }}</p></div>
        <div class="flex gap-3"><a href="{{ route('admin.pages.show', $page) }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">Quay lại</a><button type="submit" class="rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white">Lưu thay đổi</button></div>
    </div>
    @if ($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4"><ul class="list-disc space-y-1 pl-5 text-sm text-red-600">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @include('admin.pages.partials.form')
</form>
@endsection
