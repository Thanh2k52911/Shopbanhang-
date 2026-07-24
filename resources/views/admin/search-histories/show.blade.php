@extends('admin.layouts.master')
@section('title','Chi tiết tìm kiếm')
@section('page-title','Chi tiết tìm kiếm')
@section('content')
<div class="rounded-xl border bg-white p-6"><dl class="grid gap-5 md:grid-cols-2">@foreach ([
'Từ khóa'=>$searchHistory->keyword,
'Người dùng'=>$searchHistory->user?->name ?? 'Khách',
'Email'=>$searchHistory->user?->email ?? '—',
'Session ID'=>$searchHistory->session_id ?? '—',
'Số kết quả'=>number_format($searchHistory->result_count),
'Sản phẩm đã click'=>$searchHistory->clickedProduct?->name ?? '—',
'IP'=>$searchHistory->ip_address ?? '—',
'Thời gian'=>$searchHistory->created_at?->format('d/m/Y H:i:s'),
] as $label=>$value)<div><dt class="text-sm text-gray-500">{{ $label }}</dt><dd class="mt-1 break-words font-medium">{{ $value }}</dd></div>@endforeach</dl><div class="mt-6"><p class="text-sm text-gray-500">Bộ lọc</p><pre class="mt-2 whitespace-pre-wrap rounded-lg bg-gray-50 p-4 text-sm">{{ json_encode($searchHistory->filters ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></div></div>
@endsection
