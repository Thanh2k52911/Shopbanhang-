@extends('admin.layouts.master')
@section('title','Sửa vai trò')
@section('page-title','Sửa vai trò')
@section('content')<div class="space-y-4"><form method="POST" action="{{ route('admin.roles.update',$role) }}">@csrf @method('PUT') @include('admin.roles.partials.form')</form>@if(!in_array($role->name,['super_admin','admin','customer']))<form method="POST" action="{{ route('admin.roles.destroy',$role) }}" onsubmit="return confirm('Xóa vai trò này?')">@csrf @method('DELETE')<button class="rounded-lg bg-red-600 px-4 py-2 text-white">Xóa vai trò</button></form>@endif</div>@endsection
