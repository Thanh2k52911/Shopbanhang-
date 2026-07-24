@extends('admin.layouts.master')
@section('title', 'Thêm trang nội dung')
@section('page-title', 'Thêm trang nội dung')
@section('page-description', 'Tạo trang giới thiệu, chính sách hoặc hướng dẫn mới.')
@section('content')
<form method="POST" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><h2 class="text-2xl font-bold text-gray-900">Tạo trang mới</h2><p class="mt-1 text-sm text-gray-500">Nội dung đang bật sẽ có thể truy cập phía khách hàng.</p></div>
        <div class="flex gap-3"><a href="{{ route('admin.pages.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">Quay lại</a><button type="submit" class="rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white">Lưu trang</button></div>
    </div>
    @if ($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4"><ul class="list-disc space-y-1 pl-5 text-sm text-red-600">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @include('admin.pages.partials.form')
</form>
@endsection
