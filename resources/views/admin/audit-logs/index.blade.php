@extends('admin.layouts.master')

@section('title', 'Nhật ký quản trị')
@section('page-title', 'Nhật ký quản trị')
@section('page-description', 'Theo dõi các thao tác quản trị, thay đổi dữ liệu và lịch sử truy cập hệ thống.')

@section('content')
@php
    $actionLabels = [
        'created' => 'Tạo mới',
        'updated' => 'Cập nhật',
        'deleted' => 'Xóa',
        'login' => 'Đăng nhập',
        'logout' => 'Đăng xuất',
        'approved' => 'Phê duyệt',
        'rejected' => 'Từ chối',
        'cancelled' => 'Hủy',
        'restored' => 'Khôi phục',
        'viewed' => 'Xem',
    ];

    $actionClasses = [
        'created' => 'bg-green-100 text-green-700',
        'updated' => 'bg-blue-100 text-blue-700',
        'deleted' => 'bg-red-100 text-red-700',
        'login' => 'bg-purple-100 text-purple-700',
        'logout' => 'bg-gray-100 text-gray-700',
        'approved' => 'bg-emerald-100 text-emerald-700',
        'rejected' => 'bg-orange-100 text-orange-700',
        'cancelled' => 'bg-red-100 text-red-700',
        'restored' => 'bg-cyan-100 text-cyan-700',
        'viewed' => 'bg-slate-100 text-slate-700',
    ];
@endphp

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">
            Nhật ký quản trị
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            Theo dõi toàn bộ thao tác đã được ghi lại trong hệ thống.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-7">
        @foreach ([
            ['Tổng nhật ký', $statistics->total ?? 0, 'text-gray-900'],
            ['Tạo mới', $statistics->created_count ?? 0, 'text-green-600'],
            ['Cập nhật', $statistics->updated_count ?? 0, 'text-blue-600'],
            ['Xóa', $statistics->deleted_count ?? 0, 'text-red-600'],
            ['Đăng nhập', $statistics->login_count ?? 0, 'text-purple-600'],
            ['Phê duyệt', $statistics->approved_count ?? 0, 'text-emerald-600'],
            ['Từ chối', $statistics->rejected_count ?? 0, 'text-orange-600'],
        ] as [$label, $value, $class])
            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    {{ $label }}
                </p>

                <strong class="mt-2 block text-2xl {{ $class }}">
                    {{ number_format((int) $value) }}
                </strong>
            </article>
        @endforeach
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form
            method="GET"
            action="{{ route('admin.audit-logs.index') }}"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-7"
        >
            <div class="xl:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Tìm kiếm
                </label>

                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    placeholder="Admin, email, hành động, mô tả, route, URL, IP..."
                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Hành động
                </label>

                <select
                    name="action"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả</option>

                    @foreach ($actions as $action)
                        <option
                            value="{{ $action }}"
                            @selected(request('action') === $action)
                        >
                            {{ $actionLabels[$action] ?? $action }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Người thao tác
                </label>

                <select
                    name="user_id"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả</option>

                    @foreach ($users as $user)
                        <option
                            value="{{ $user->id }}"
                            @selected((string) request('user_id') === (string) $user->id)
                        >
                            {{ $user->name }} — {{ $user->email }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Đối tượng
                </label>

                <select
                    name="auditable_type"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả</option>

                    @foreach ($auditableTypes as $type)
                        <option
                            value="{{ $type }}"
                            @selected(request('auditable_type') === $type)
                        >
                            {{ class_basename($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    HTTP method
                </label>

                <select
                    name="request_method"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả</option>

                    @foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method)
                        <option
                            value="{{ $method }}"
                            @selected(request('request_method') === $method)
                        >
                            {{ $method }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Sắp xếp
                </label>

                <select
                    name="sort"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Mới nhất</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                    <option value="action" @selected(request('sort') === 'action')>Theo hành động</option>
                    <option value="user" @selected(request('sort') === 'user')>Theo người dùng</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Từ ngày
                </label>

                <input
                    type="date"
                    name="date_from"
                    value="{{ request('date_from') }}"
                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Đến ngày
                </label>

                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-7">
                <button
                    type="submit"
                    class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white"
                >
                    Lọc dữ liệu
                </button>

                <a
                    href="{{ route('admin.audit-logs.index') }}"
                    class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700"
                >
                    Đặt lại
                </a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-lg font-bold text-gray-900">
                Danh sách nhật ký
            </h3>

            <p class="mt-1 text-sm text-gray-500">
                Hiển thị {{ $logs->count() }} / {{ $logs->total() }} kết quả.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Người thao tác
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Hành động
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Đối tượng
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Mô tả
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Request
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Thời gian
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                            Thao tác
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        @php
                            $actionClass = $actionClasses[$log->action]
                                ?? 'bg-gray-100 text-gray-700';
                        @endphp

                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="min-w-[220px]">
                                    @if ($log->user_id)
                                        <strong class="text-gray-900">
                                            {{ $log->user_name ?: 'Tài khoản đã xóa' }}
                                        </strong>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $log->user_email }}
                                        </p>
                                    @else
                                        <strong class="text-gray-900">
                                            Hệ thống / Khách
                                        </strong>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $actionClass }}">
                                    {{ $actionLabels[$log->action] ?? $log->action }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="min-w-[180px]">
                                    @if ($log->auditable_type)
                                        <p class="font-semibold text-gray-900">
                                            {{ class_basename($log->auditable_type) }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            ID: {{ $log->auditable_id ?: 'Không có' }}
                                        </p>
                                    @else
                                        <span class="text-gray-500">
                                            Không có đối tượng
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="min-w-[300px]">
                                    <p class="line-clamp-3 text-sm leading-6 text-gray-700">
                                        {{ $log->description ?: 'Không có mô tả.' }}
                                    </p>

                                    @if ($log->route_name)
                                        <p class="mt-2 break-all font-mono text-xs text-gray-400">
                                            {{ $log->route_name }}
                                        </p>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="min-w-[170px] text-sm text-gray-600">
                                    <p>
                                        <span class="rounded bg-gray-100 px-2 py-1 font-mono text-xs font-semibold text-gray-700">
                                            {{ $log->request_method ?: 'N/A' }}
                                        </span>
                                    </p>

                                    <p class="mt-2 break-all text-xs text-gray-500">
                                        IP: {{ $log->ip_address ?: 'Không có' }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-600">
                                <p>
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y') }}
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}
                                </p>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <a
                                    href="{{ route('admin.audit-logs.show', $log->id) }}"
                                    class="inline-flex rounded-lg border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50"
                                >
                                    CHI TIẾT
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="7"
                                class="px-5 py-16 text-center text-gray-500"
                            >
                                Không tìm thấy nhật ký.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $logs->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
