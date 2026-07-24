@extends('admin.layouts.master')
@section('title','Chi tiết đăng nhập')
@section('page-title','Chi tiết đăng nhập')
@section('content')
<div class="rounded-xl border bg-white p-6"><dl class="grid gap-5 md:grid-cols-2">
@foreach ([
'Tài khoản'=>($loginHistory->user?->name ?? 'Không xác định'),
'Email'=>($loginHistory->email ?? $loginHistory->user?->email ?? '—'),
'Trạng thái'=>($loginHistory->is_success ? 'Thành công':'Thất bại'),
'Lý do thất bại'=>($loginHistory->failure_reason ?: '—'),
'IP'=>($loginHistory->ip_address ?: '—'),
'Thiết bị'=>($loginHistory->device ?: '—'),
'Trình duyệt'=>($loginHistory->browser ?: '—'),
'Nền tảng'=>($loginHistory->platform ?: '—'),
'Vị trí'=>collect([$loginHistory->city,$loginHistory->country])->filter()->join(', ') ?: '—',
'Đăng nhập lúc'=>optional($loginHistory->logged_in_at)->format('d/m/Y H:i:s'),
'Đăng xuất lúc'=>optional($loginHistory->logged_out_at)->format('d/m/Y H:i:s') ?? 'Chưa ghi nhận',
'Session ID'=>($loginHistory->session_id ?: '—'),
] as $label=>$value)<div><dt class="text-sm text-gray-500">{{ $label }}</dt><dd class="mt-1 break-words font-medium">{{ $value }}</dd></div>@endforeach
</dl><div class="mt-6"><p class="text-sm text-gray-500">User agent</p><pre class="mt-2 whitespace-pre-wrap rounded-lg bg-gray-50 p-4 text-sm">{{ $loginHistory->user_agent ?: '—' }}</pre></div></div>
@endsection
