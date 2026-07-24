@extends('admin.layouts.master')
@section('title','Lịch sử đăng nhập')
@section('page-title','Lịch sử đăng nhập')
@section('content')
<div class="space-y-6">
<div class="grid gap-4 md:grid-cols-4">
@foreach ([['Tổng lượt',$statistics->total ?? 0],['Thành công',$statistics->success_count ?? 0],['Thất bại',$statistics->failed_count ?? 0],['IP khác nhau',$statistics->unique_ips ?? 0]] as [$label,$value])
<div class="rounded-xl border bg-white p-5"><p class="text-sm text-gray-500">{{ $label }}</p><p class="mt-2 text-2xl font-bold">{{ number_format((int)$value) }}</p></div>
@endforeach
</div>
<form method="GET" class="grid gap-3 rounded-xl border bg-white p-4 md:grid-cols-5">
<input name="keyword" value="{{ request('keyword') }}" placeholder="Email, tên, IP, thiết bị..." class="rounded-lg border-gray-300 md:col-span-2">
<select name="status" class="rounded-lg border-gray-300"><option value="">Tất cả trạng thái</option><option value="success" @selected(request('status')==='success')>Thành công</option><option value="failed" @selected(request('status')==='failed')>Thất bại</option></select>
<input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border-gray-300">
<input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border-gray-300">
<div><button class="rounded-lg bg-pink-600 px-4 py-2 text-white">Lọc</button></div>
</form>
<div class="overflow-hidden rounded-xl border bg-white"><div class="overflow-x-auto"><table class="min-w-full divide-y"><thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Tài khoản</th><th class="px-4 py-3 text-left">Trạng thái</th><th class="px-4 py-3 text-left">IP / Thiết bị</th><th class="px-4 py-3 text-left">Thời gian</th><th></th></tr></thead><tbody class="divide-y">
@forelse($histories as $history)<tr><td class="px-4 py-3"><div class="font-medium">{{ $history->user?->name ?? 'Không xác định' }}</div><div class="text-sm text-gray-500">{{ $history->email ?? $history->user?->email }}</div></td><td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs {{ $history->is_success ? 'bg-green-100 text-green-700':'bg-red-100 text-red-700' }}">{{ $history->is_success ? 'Thành công':'Thất bại' }}</span></td><td class="px-4 py-3 text-sm"><div>{{ $history->ip_address ?: '—' }}</div><div class="text-gray-500">{{ collect([$history->device,$history->browser,$history->platform])->filter()->join(' · ') ?: '—' }}</div></td><td class="px-4 py-3 text-sm">{{ optional($history->logged_in_at)->format('d/m/Y H:i:s') }}</td><td class="px-4 py-3"><a class="text-pink-600" href="{{ route('admin.login-histories.show',$history) }}">Chi tiết</a></td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-gray-500">Chưa có dữ liệu.</td></tr>@endforelse
</tbody></table></div></div>
{{ $histories->links() }}
</div>
@endsection
