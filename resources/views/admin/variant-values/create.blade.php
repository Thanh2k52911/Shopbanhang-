@extends('admin.layouts.master')
@section('title', 'Thêm giá trị thuộc tính')
@section('page-title', 'Thêm giá trị cho '.$variantAttribute->name)
@section('content')
<div class="mx-auto max-w-2xl rounded-xl border bg-white p-6"><form method="POST" action="{{ route('admin.variant-values.store', $variantAttribute) }}">@csrf @include('admin.variant-values.partials.form')</form></div>
@endsection
