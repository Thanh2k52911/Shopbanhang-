@extends('admin.layouts.master')
@section('title','Vai trò và phân quyền')
@section('page-title','Vai trò và phân quyền')
@section('content')
<div class="space-y-6"><div class="flex justify-between"><div><h2 class="text-2xl font-bold">Vai trò</h2><p class="text-sm text-gray-500">Quản lý nhóm quyền truy cập hệ thống.</p></div><a class="rounded-lg bg-pink-600 px-4 py-2 text-white" href="{{ route('admin.roles.create') }}">+ Thêm vai trò</a></div>
@if(session('success'))<div class="rounded-lg bg-green-50 p-3 text-green-700">{{ session('success') }}</div>@endif
<form><input class="rounded-lg border-gray-300" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm vai trò"><button class="ml-2 rounded-lg bg-gray-900 px-4 py-2 text-white">Tìm</button></form>
<div class="overflow-hidden rounded-xl border bg-white"><table class="min-w-full divide-y"><thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Vai trò</th><th>Người dùng</th><th>Quyền</th><th></th></tr></thead><tbody class="divide-y">@foreach($roles as $role)<tr><td class="px-4 py-3"><strong>{{ $role->display_name }}</strong><div class="text-sm text-gray-500">{{ $role->name }}</div></td><td class="text-center">{{ $role->users_count }}</td><td class="text-center">{{ $role->permissions_count }}</td><td class="px-4 text-right"><a class="text-pink-600" href="{{ route('admin.roles.edit',$role) }}">Sửa quyền</a></td></tr>@endforeach</tbody></table></div>{{ $roles->links() }}</div>
@endsection
