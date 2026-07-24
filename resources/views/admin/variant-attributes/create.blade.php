@extends('admin.layouts.master')
@section('title', 'Thêm thuộc tính')
@section('page-title', 'Thêm thuộc tính biến thể')
@section('content')
<div class="mx-auto max-w-2xl rounded-xl border bg-white p-6">
    <form method="POST" action="{{ route('admin.variant-attributes.store') }}">@csrf
        @include('admin.variant-attributes.partials.form')
    </form>
</div>
@endsection
