@extends('admin.layouts.master')
@section('title','Thêm vai trò')
@section('page-title','Thêm vai trò')
@section('content')<form method="POST" action="{{ route('admin.roles.store') }}">@csrf @include('admin.roles.partials.form')</form>@endsection
