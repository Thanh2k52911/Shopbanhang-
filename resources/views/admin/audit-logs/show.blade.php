@extends('admin.layouts.master')

@section('title', 'Chi tiết nhật ký')
@section('page-title', 'Chi tiết nhật ký')
@section('page-description', 'Xem thông tin request, người thao tác và thay đổi dữ liệu.')

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

    $actionClass = $actionClasses[$log->action]
        ?? 'bg-gray-100 text-gray-700';

    $formatValue = static function (mixed $value): string {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode(
                $value,
                JSON_UNESCAPED_UNICODE
                | JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES
            );
        }

        return (string) $value;
    };

    $allChangedKeys = collect(array_keys($log->old_values_array))
        ->merge(array_keys($log->new_values_array))
        ->unique()
        ->values();
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">
                    Nhật ký #{{ $log->id }}
                </h2>

                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $actionClass }}">
                    {{ $actionLabels[$log->action] ?? $log->action }}
                </span>
            </div>

            <p class="mt-1 text-sm text-gray-500">
                {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
            </p>
        </div>

        <a
            href="{{ route('admin.audit-logs.index') }}"
            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700"
        >
            Quay lại
        </a>
    </div>

    <div class="admin-responsive-sidebar-layout grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_380px]">
        <div class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">
                    Nội dung nhật ký
                </h3>

                <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <dt class="text-sm text-gray-500">
                            Hành động
                        </dt>

                        <dd class="mt-1 font-semibold text-gray-900">
                            {{ $actionLabels[$log->action] ?? $log->action }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Đối tượng
                        </dt>

                        <dd class="mt-1 font-semibold text-gray-900">
                            {{ $log->auditable_type
                                ? class_basename($log->auditable_type)
                                : 'Không có' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            ID đối tượng
                        </dt>

                        <dd class="mt-1 font-semibold text-gray-900">
                            {{ $log->auditable_id ?: 'Không có' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Route
                        </dt>

                        <dd class="mt-1 break-all font-mono text-sm text-gray-700">
                            {{ $log->route_name ?: 'Không có' }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-6 border-t border-gray-200 pt-6">
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        Mô tả
                    </h4>

                    <p class="mt-3 whitespace-pre-line text-base leading-7 text-gray-700">
                        {{ $log->description ?: 'Không có mô tả.' }}
                    </p>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">
                    Dữ liệu thay đổi
                </h3>

                @if ($allChangedKeys->isEmpty())
                    <p class="mt-4 text-sm text-gray-500">
                        Nhật ký này không có dữ liệu trước và sau.
                    </p>
                @else
                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                        Trường
                                    </th>

                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                        Giá trị cũ
                                    </th>

                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                        Giá trị mới
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                @foreach ($allChangedKeys as $key)
                                    @php
                                        $oldValue = $log->old_values_array[$key] ?? null;
                                        $newValue = $log->new_values_array[$key] ?? null;

                                        $hasChanged = $formatValue($oldValue)
                                            !== $formatValue($newValue);
                                    @endphp

                                    <tr class="{{ $hasChanged ? 'bg-yellow-50' : '' }}">
                                        <td class="px-4 py-3 align-top">
                                            <code class="text-xs font-semibold text-gray-900">
                                                {{ $key }}
                                            </code>
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            <pre class="max-w-[420px] whitespace-pre-wrap break-words rounded-lg bg-red-50 p-3 text-xs leading-5 text-red-700">{{ $formatValue($oldValue) }}</pre>
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            <pre class="max-w-[420px] whitespace-pre-wrap break-words rounded-lg bg-green-50 p-3 text-xs leading-5 text-green-700">{{ $formatValue($newValue) }}</pre>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">
                    Request
                </h3>

                <dl class="mt-5 space-y-5">
                    <div>
                        <dt class="text-sm text-gray-500">
                            HTTP method
                        </dt>

                        <dd class="mt-1">
                            <span class="rounded bg-gray-100 px-2 py-1 font-mono text-xs font-semibold text-gray-700">
                                {{ $log->request_method ?: 'N/A' }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            URL
                        </dt>

                        <dd class="mt-1 break-all font-mono text-sm text-blue-600">
                            {{ $log->url ?: 'Không có' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            IP address
                        </dt>

                        <dd class="mt-1 font-mono text-sm text-gray-900">
                            {{ $log->ip_address ?: 'Không có' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            User agent
                        </dt>

                        <dd class="mt-1 break-all rounded-lg bg-gray-50 p-3 font-mono text-xs leading-5 text-gray-700">
                            {{ $log->user_agent ?: 'Không có' }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">
                    Nhật ký liên quan
                </h3>

                <div class="mt-5 space-y-3">
                    @forelse ($relatedLogs as $related)
                        <a
                            href="{{ route('admin.audit-logs.show', $related->id) }}"
                            class="block rounded-lg border border-gray-200 p-4 hover:bg-gray-50"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <strong class="text-gray-900">
                                        {{ $related->user_name ?: 'Hệ thống / Khách' }}
                                    </strong>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $related->user_email ?: 'Không có email' }}
                                    </p>
                                </div>

                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                    {{ $actionLabels[$related->action] ?? $related->action }}
                                </span>
                            </div>

                            <p class="mt-3 line-clamp-2 text-sm text-gray-600">
                                {{ $related->description ?: 'Không có mô tả.' }}
                            </p>

                            <p class="mt-2 text-xs text-gray-400">
                                {{ \Carbon\Carbon::parse($related->created_at)->format('d/m/Y H:i:s') }}
                            </p>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">
                            Không có nhật ký liên quan.
                        </p>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Người thao tác
                </h3>

                <div class="mt-4">
                    @if ($log->user_id)
                        <div class="flex items-start gap-4">
                            @if ($log->user_avatar)
                                <img
                                    src="{{ asset('storage/' . ltrim($log->user_avatar, '/')) }}"
                                    alt="{{ $log->user_name }}"
                                    class="h-14 w-14 rounded-full border border-gray-200 object-cover"
                                >
                            @else
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-pink-100 text-xl font-bold text-pink-600">
                                    {{ mb_strtoupper(mb_substr($log->user_name ?: 'U', 0, 1)) }}
                                </div>
                            @endif

                            <div>
                                <strong class="text-gray-900">
                                    {{ $log->user_name ?: 'Tài khoản đã xóa' }}
                                </strong>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $log->user_email ?: 'Không có email' }}
                                </p>

                                @if ($log->user_status)
                                    <p class="mt-2 text-xs text-gray-500">
                                        Trạng thái:
                                        <strong class="text-gray-900">
                                            {{ $log->user_status }}
                                        </strong>
                                    </p>
                                @endif
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">
                            Thao tác được thực hiện bởi hệ thống hoặc khách chưa đăng nhập.
                        </p>
                    @endif
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Thời gian
                </h3>

                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">
                            Ngày tạo
                        </dt>

                        <dd class="mt-1 font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">
                            Cập nhật gần nhất
                        </dt>

                        <dd class="mt-1 font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($log->updated_at)->format('d/m/Y H:i:s') }}
                        </dd>
                    </div>
                </dl>
            </section>

            @if ($log->user_id)
                <section class="rounded-xl border border-gray-200 bg-white p-5">
                    <h3 class="text-lg font-bold text-gray-900">
                        Đăng nhập gần nhất
                    </h3>

                    <dl class="mt-4 space-y-4 text-sm">
                        <div>
                            <dt class="text-gray-500">
                                Thời gian
                            </dt>

                            <dd class="mt-1 font-semibold text-gray-900">
                                {{ $log->last_login_at
                                    ? \Carbon\Carbon::parse($log->last_login_at)->format('d/m/Y H:i:s')
                                    : 'Chưa có' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-gray-500">
                                IP
                            </dt>

                            <dd class="mt-1 font-mono text-sm text-gray-900">
                                {{ $log->last_login_ip ?: 'Chưa có' }}
                            </dd>
                        </div>
                    </dl>
                </section>
            @endif
        </aside>
    </div>
</div>
@endsection
