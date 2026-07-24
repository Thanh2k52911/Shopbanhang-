@extends('admin.layouts.master')

@section('title', 'Tạo kiện vận chuyển')
@section('page-title', 'Tạo kiện vận chuyển')
@section('page-description', 'Hệ thống tự lấy phương thức, người nhận, phí, COD và mã vận đơn cho đơn ' . $order->order_code)

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <a
            href="{{ route('admin.orders.show', $order) }}"
            class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 transition hover:text-pink-600"
        >
            ← Quay lại chi tiết đơn hàng
        </a>

        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
            Chế độ vận chuyển mô phỏng miễn phí
        </span>
    </div>

    @if ($errors->has('shipment'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first('shipment') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <form
                action="{{ route('admin.shipments.store', $order) }}"
                method="POST"
                class="admin-card p-6"
            >
                @csrf

                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4">
                    <h2 class="font-semibold text-green-800">
                        Thông tin đã được tạo tự động
                    </h2>
                    <p class="mt-1 text-sm text-green-700">
                        Hệ thống dựa trên phương thức khách chọn lúc thanh toán, địa chỉ nhận hàng, số lượng và trọng lượng SKU. Mã vận đơn bên dưới là mã mô phỏng; sau này có thể thay bằng mã thật từ API GHN/GHTK/Viettel Post.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Phương thức vận chuyển</label>
                        <input class="admin-input bg-gray-50" value="{{ $order->shippingMethod?->name ?: ($shippingMethods->firstWhere('id', $automaticShipment['shipping_method_id'])?->name ?? 'Tự động chọn') }}" readonly>
                        <input type="hidden" name="shipping_method_id" value="{{ old('shipping_method_id', $automaticShipment['shipping_method_id']) }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Đơn vị vận chuyển</label>
                        <input class="admin-input bg-gray-50" value="{{ $automaticShipment['carrier_name'] }}" readonly>
                        <input type="hidden" name="carrier_name" value="{{ old('carrier_name', $automaticShipment['carrier_name']) }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Dịch vụ vận chuyển</label>
                        <input class="admin-input bg-gray-50" value="{{ $automaticShipment['service_name'] }}" readonly>
                        <input type="hidden" name="service_name" value="{{ old('service_name', $automaticShipment['service_name']) }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Mã vận đơn tự động</label>
                        <input class="admin-input bg-gray-50 font-semibold text-pink-600" value="{{ $automaticShipment['tracking_code'] }}" readonly>
                        <input type="hidden" name="tracking_code" value="{{ old('tracking_code', $automaticShipment['tracking_code']) }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Phí vận chuyển</label>
                        <div class="admin-input flex items-center bg-gray-50 font-semibold text-gray-900">
                            {{ number_format((float) $order->shipping_fee, 0, ',', '.') }}₫
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Ngày giao dự kiến</label>
                        <input
                            type="date"
                            name="estimated_delivery_at"
                            value="{{ old('estimated_delivery_at', $automaticShipment['estimated_delivery_at']) }}"
                            class="admin-input"
                        >
                    </div>

                    <div>
                        <label for="weight" class="mb-2 block text-sm font-medium text-gray-700">Trọng lượng tự tính (gram)</label>
                        <input id="weight" type="number" name="weight" min="1" value="{{ old('weight', $automaticShipment['weight']) }}" class="admin-input">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Tiền COD</label>
                        <div class="admin-input flex items-center bg-gray-50 font-semibold text-gray-900">
                            {{ number_format($order->payment_method === 'cod' && $order->payment_status !== 'paid' ? (float) $order->total_amount : 0, 0, ',', '.') }}₫
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <p class="mb-2 text-sm font-medium text-gray-700">Kích thước kiện hàng (cm)</p>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <input type="number" name="length" min="1" value="{{ old('length', $automaticShipment['length']) }}" class="admin-input" placeholder="Dài">
                            <input type="number" name="width" min="1" value="{{ old('width', $automaticShipment['width']) }}" class="admin-input" placeholder="Rộng">
                            <input type="number" name="height" min="1" value="{{ old('height', $automaticShipment['height']) }}" class="admin-input" placeholder="Cao">
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="note" class="mb-2 block text-sm font-medium text-gray-700">Ghi chú vận chuyển</label>
                        <textarea id="note" name="note" rows="4" class="admin-textarea" placeholder="Ghi chú đóng gói, giao hàng...">{{ old('note') }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap justify-end gap-3 border-t border-gray-200 pt-5">
                    <a href="{{ route('admin.orders.show', $order) }}" class="admin-btn admin-btn-secondary">Hủy</a>
                    <button type="submit" class="admin-btn admin-btn-primary">
                        Tạo kiện bằng dữ liệu tự động
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="admin-card p-5">
                <h2 class="text-base font-semibold text-gray-900">Người nhận</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="text-gray-500">Họ tên</dt><dd class="font-medium text-gray-900">{{ $order->shippingAddress?->receiver_name ?: $order->customer_name }}</dd></div>
                    <div><dt class="text-gray-500">Số điện thoại</dt><dd class="font-medium text-gray-900">{{ $order->shippingAddress?->phone ?: $order->customer_phone }}</dd></div>
                    <div><dt class="text-gray-500">Địa chỉ</dt><dd class="font-medium text-gray-900">{{ $order->shippingAddress?->formatted_address ?: 'Chưa có địa chỉ' }}</dd></div>
                </dl>
            </div>

            <div class="admin-card p-5">
                <h2 class="text-base font-semibold text-gray-900">Thông tin đơn hàng</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Mã đơn</dt><dd class="font-semibold text-gray-900">{{ $order->order_code }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Kho xử lý</dt><dd class="font-medium text-gray-900">{{ $order->warehouse?->name }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Tổng sản phẩm</dt><dd class="font-medium text-gray-900">{{ $order->total_quantity }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Tổng tiền</dt><dd class="font-bold text-pink-600">{{ number_format((float) $order->total_amount, 0, ',', '.') }}₫</dd></div>
                </dl>
            </div>

            <form action="{{ route('admin.shipments.store-automatic', $order) }}" method="POST" class="admin-card p-5">
                @csrf
                <h2 class="font-semibold text-gray-900">Tạo nhanh một chạm</h2>
                <p class="mt-2 text-sm text-gray-500">
                    Bỏ qua bước kiểm tra form và tạo kiện ngay bằng toàn bộ dữ liệu hệ thống đã tính.
                </p>
                <button type="submit" class="admin-btn admin-btn-primary mt-4 w-full justify-center">
                    Tạo kiện tự động ngay
                </button>
            </form>
        </div>
    </div>
@endsection
