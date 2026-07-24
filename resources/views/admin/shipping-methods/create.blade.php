@extends('admin.layouts.master')

@section('title', 'Thêm phương thức vận chuyển')
@section('page-title', 'Thêm phương thức vận chuyển')
@section('page-description', 'Tạo cấu hình phí và thời gian giao hàng mới.')

@section('content')
<div class="mx-auto max-w-5xl">
    <form method="POST" action="{{ route('admin.shipping-methods.store') }}" class="rounded-xl border border-gray-200 bg-white p-6">
        @csrf
        @include('admin.shipping-methods.partials.form')
    </form>
</div>
@endsection
