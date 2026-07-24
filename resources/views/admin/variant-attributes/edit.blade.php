@extends('admin.layouts.master')
@section('title', 'Sửa thuộc tính')
@section('page-title', 'Sửa thuộc tính biến thể')
@section('content')
<div class="mx-auto max-w-2xl rounded-xl border bg-white p-6">
    <form method="POST" action="{{ route('admin.variant-attributes.update', $variantAttribute) }}">@csrf @method('PUT')
        @include('admin.variant-attributes.partials.form')
    </form>
</div>
@endsection
