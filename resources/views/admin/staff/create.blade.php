@extends('admin.layouts.master')
@section('title','Thêm nhân viên')
@section('page-title','Thêm nhân viên')
@section('content')<form method="POST" action="{{ route('admin.staff.store') }}">@csrf @include('admin.staff.form')</form>@endsection
