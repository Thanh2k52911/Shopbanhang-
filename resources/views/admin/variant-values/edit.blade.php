@extends('admin.layouts.master')
@section('title', 'Sửa giá trị thuộc tính')
@section('page-title', 'Sửa giá trị '.$variantValue->attribute->name)
@section('content')
<div class="mx-auto max-w-2xl rounded-xl border bg-white p-6"><form method="POST" action="{{ route('admin.variant-values.update', $variantValue) }}">@csrf @method('PUT') @include('admin.variant-values.partials.form')</form></div>
@endsection
