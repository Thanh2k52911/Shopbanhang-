@extends('admin.layouts.master')
@section('title','Sửa nhân viên')
@section('page-title','Sửa nhân viên')
@section('content')<div class="space-y-4"><form method="POST" action="{{ route('admin.staff.update',$staff) }}">@csrf @method('PUT') @include('admin.staff.form')</form>@if(!$staff->is(auth()->user()))<form method="POST" action="{{ route('admin.staff.destroy',$staff) }}" onsubmit="return confirm('Xóa nhân viên này?')">@csrf @method('DELETE')<button class="rounded-lg bg-red-600 px-4 py-2 text-white">Xóa nhân viên</button></form>@endif</div>@endsection
