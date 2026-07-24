@extends('admin.layouts.master')

@section('title', 'Chi tiết phương thức vận chuyển')
@section('page-title', 'Chi tiết phương thức vận chuyển')
@section('page-description', 'Thông tin cấu hình và các vận đơn gần đây.')

@section('content')
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $shippingMethod->name }}</h2>
            <p class="mt-1 text-sm text-gray-500">Mã: {{ $shippingMethod->code }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.shipping-methods.edit', $shippingMethod) }}" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Chỉnh sửa</a>
            <a href="{{ route('admin.shipping-methods.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Quay lại</a>
        </div>
    </div>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-xl border border-gray-200 bg-white p-5"><p class="text-sm text-gray-500">Phí cơ bản</p><strong class="mt-2 block text-xl text-gray-900">{{ number_format((float) $shippingMethod->base_fee) }}₫</strong></article>
        <article class="rounded-xl border border-gray-200 bg-white p-5"><p class="text-sm text-gray-500">Miễn phí từ</p><strong class="mt-2 block text-xl text-pink-600">{{ $shippingMethod->free_shipping_minimum !== null ? number_format((float) $shippingMethod->free_shipping_minimum).'₫' : 'Không áp dụng' }}</strong></article>
        <article class="rounded-xl border border-gray-200 bg-white p-5"><p class="text-sm text-gray-500">Thời gian dự kiến</p><strong class="mt-2 block text-xl text-gray-900">{{ $shippingMethod->estimated_delivery_text }}</strong></article>
        <article class="rounded-xl border border-gray-200 bg-white p-5"><p class="text-sm text-gray-500">Số vận đơn</p><strong class="mt-2 block text-xl text-gray-900">{{ number_format($shippingMethod->shipments_count) }}</strong></article>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6">
        <dl class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div><dt class="text-sm text-gray-500">Nhà cung cấp</dt><dd class="mt-1 font-semibold text-gray-900">{{ $shippingMethod->provider ?: 'Nội bộ/Chưa xác định' }}</dd></div>
            <div><dt class="text-sm text-gray-500">Trạng thái</dt><dd class="mt-1"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $shippingMethod->status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $shippingMethod->status ? 'Đang hoạt động' : 'Đang tắt' }}</span></dd></div>
            <div><dt class="text-sm text-gray-500">Thứ tự</dt><dd class="mt-1 font-semibold text-gray-900">{{ $shippingMethod->sort_order }}</dd></div>
            <div><dt class="text-sm text-gray-500">Cập nhật</dt><dd class="mt-1 font-semibold text-gray-900">{{ optional($shippingMethod->updated_at)->format('d/m/Y H:i') }}</dd></div>
            <div class="md:col-span-2"><dt class="text-sm text-gray-500">Mô tả</dt><dd class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $shippingMethod->description ?: 'Chưa có mô tả.' }}</dd></div>
        </dl>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-lg font-bold text-gray-900">Vận đơn gần đây</h3></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Mã vận đơn</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Đơn hàng</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Khách hàng</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Trạng thái</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recentShipments as $shipment)
                        <tr>
                            <td class="px-5 py-4 text-sm font-semibold text-gray-900">{{ $shipment->shipment_code }}</td>
                            <td class="px-5 py-4 text-sm"><a href="{{ route('admin.orders.show', $shipment->order_id) }}" class="font-semibold text-blue-600">{{ $shipment->order?->order_code }}</a></td>
                            <td class="px-5 py-4 text-sm text-gray-700">{{ $shipment->order?->customer_name }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700">{{ $shipment->status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">Chưa có vận đơn sử dụng phương thức này.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-xl border border-red-200 bg-red-50 p-5">
        <h3 class="font-bold text-red-800">Xóa phương thức</h3>
        <p class="mt-1 text-sm text-red-700">Chỉ xóa được khi chưa phát sinh vận đơn. Nếu đã sử dụng, hãy tắt trạng thái.</p>
        <form method="POST" action="{{ route('admin.shipping-methods.destroy', $shippingMethod) }}" class="mt-4" onsubmit="return confirm('Xóa phương thức vận chuyển này?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Xóa phương thức</button>
        </form>
    </section>
</div>
@endsection
