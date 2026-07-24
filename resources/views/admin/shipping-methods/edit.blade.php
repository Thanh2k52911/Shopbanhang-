@extends('admin.layouts.master')

@section('title', 'Sửa phương thức vận chuyển')
@section('page-title', 'Sửa phương thức vận chuyển')
@section('page-description', 'Cập nhật phí, thời gian giao và trạng thái sử dụng.')

@section('content')
<div class="mx-auto max-w-5xl">
    <form method="POST" action="{{ route('admin.shipping-methods.update', $shippingMethod) }}" class="rounded-xl border border-gray-200 bg-white p-6">
        @csrf
        @method('PUT')
        @include('admin.shipping-methods.partials.form')
    </form>
</div>
@endsection
