@extends('admin.layouts.master')

@section('title', 'Chỉnh sửa khách hàng')
@section('page-title', 'Chỉnh sửa khách hàng')
@section('page-description', 'Cập nhật tên, email và trạng thái xác minh.')

@section('content')
<form method="POST" action="{{ route('admin.customers.update', $customer->id) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Chỉnh sửa: {{ $customer->name }}</h2>
            <p class="mt-1 text-sm text-gray-500">ID: {{ $customer->id }}</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.customers.show', $customer->id) }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">Quay lại</a>
            <button class="rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white">Lưu thay đổi</button>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <ul class="list-disc space-y-1 pl-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="admin-responsive-sidebar-layout grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_340px]">
        <section class="rounded-xl border border-gray-200 bg-white p-6">
            <h3 class="text-lg font-bold text-gray-900">Thông tin tài khoản</h3>

            <div class="mt-5 space-y-5">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Họ tên *</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required maxlength="255"
                           class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" required maxlength="255"
                           class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
                </div>

                <label class="flex gap-3 rounded-lg border border-gray-200 p-4">
                    <input type="hidden" name="email_verified" value="0">
                    <input type="checkbox" name="email_verified" value="1"
                           @checked(old('email_verified', $customer->email_verified_at ? 1 : 0))
                           class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                    <span>
                        <strong class="block text-sm text-gray-900">Email đã xác minh</strong>
                        <span class="mt-1 block text-xs text-gray-500">Bỏ chọn để đưa email về trạng thái chưa xác minh.</span>
                    </span>
                </label>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Trạng thái hiện tại</h3>
                <p class="mt-4 font-semibold text-gray-900">{{ $customer->status }}</p>
                <p class="mt-2 text-xs text-gray-500">Thay đổi trạng thái tại trang chi tiết khách hàng.</p>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Đăng nhập cuối</h3>
                <dl class="mt-4 space-y-4 text-sm">
                    <div><dt class="text-gray-500">Thời gian</dt><dd class="mt-1 font-medium text-gray-900">{{ $customer->last_login_at ? \Carbon\Carbon::parse($customer->last_login_at)->format('d/m/Y H:i') : 'Chưa có' }}</dd></div>
                    <div><dt class="text-gray-500">IP</dt><dd class="mt-1 font-medium text-gray-900">{{ $customer->last_login_ip ?: 'Chưa có' }}</dd></div>
                </dl>
            </section>
        </aside>
    </div>
</form>
@endsection
