@extends('admin.layouts.master')

@section('title', 'Chi tiết khách hàng')
@section('page-title', 'Chi tiết khách hàng')
@section('page-description', 'Quản lý hồ sơ, trạng thái tài khoản, đơn hàng và lịch sử thay đổi.')

@section('content')
@php
    $statusClasses = match ($customer->status) {
        'active' => 'bg-green-100 text-green-700',
        'inactive' => 'bg-orange-100 text-orange-700',
        'blocked' => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-700',
    };

    $statusLabel = match ($customer->status) {
        'active' => 'Đang hoạt động',
        'inactive' => 'Tạm ngừng',
        'blocked' => 'Đã khóa',
        default => $customer->status,
    };
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h2>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $statusLabel }}</span>
            </div>
            <p class="mt-1 text-sm text-gray-500">{{ $customer->email }}</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.customers.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">Quay lại</a>
            <a href="{{ route('admin.customers.edit', $customer->id) }}" class="rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white">Chỉnh sửa</a>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <ul class="list-disc space-y-1 pl-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="admin-responsive-sidebar-layout grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_360px]">
        <div class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">Thông tin tài khoản</h3>

                <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div><dt class="text-sm text-gray-500">Họ tên</dt><dd class="mt-1 font-semibold text-gray-900">{{ $customer->name }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Email</dt><dd class="mt-1 font-semibold text-gray-900">{{ $customer->email }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Xác minh</dt><dd class="mt-1 font-semibold text-gray-900">{{ $customer->email_verified_at ? 'Đã xác minh' : 'Chưa xác minh' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Ngày đăng ký</dt><dd class="mt-1 font-semibold text-gray-900">{{ \Carbon\Carbon::parse($customer->created_at)->format('d/m/Y H:i') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Đăng nhập cuối</dt><dd class="mt-1 font-semibold text-gray-900">{{ $customer->last_login_at ? \Carbon\Carbon::parse($customer->last_login_at)->format('d/m/Y H:i') : 'Chưa có' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">IP đăng nhập cuối</dt><dd class="mt-1 font-semibold text-gray-900">{{ $customer->last_login_ip ?: 'Chưa có' }}</dd></div>

                    @if ($customer->blocked_at)
                        <div><dt class="text-sm text-gray-500">Khóa lúc</dt><dd class="mt-1 font-semibold text-red-700">{{ \Carbon\Carbon::parse($customer->blocked_at)->format('d/m/Y H:i') }}</dd></div>
                    @endif

                    @if ($customer->blocked_reason)
                        <div class="md:col-span-2">
                            <dt class="text-sm text-gray-500">Lý do hiện tại</dt>
                            <dd class="mt-2 whitespace-pre-line text-gray-800">{{ $customer->blocked_reason }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">Địa chỉ giao hàng</h3>

                <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                    @forelse ($addresses as $address)
                        <article class="rounded-xl border border-gray-200 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <h4 class="font-semibold text-gray-900">{{ $address->receiver_name }}</h4>
                                @if ((int) $address->is_default === 1)
                                    <span class="rounded-full bg-pink-100 px-2.5 py-1 text-xs font-semibold text-pink-700">Mặc định</span>
                                @endif
                            </div>
                            <p class="mt-2 text-sm font-medium text-gray-700">{{ $address->phone }}</p>
                            <p class="mt-2 text-sm leading-6 text-gray-600">
                                {{ $address->address }},
                                {{ $address->ward }},
                                {{ $address->district }},
                                {{ $address->province }}
                            </p>
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">Khách hàng chưa có địa chỉ.</p>
                    @endforelse
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-900">Lịch sử trạng thái</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Thời gian</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Thay đổi</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Lý do</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Người thực hiện</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($statusHistories as $history)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">{{ \Carbon\Carbon::parse($history->created_at)->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold text-gray-900">{{ $history->old_status ?: 'Chưa có' }} → {{ $history->new_status }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600">{{ $history->reason ?: 'Không có' }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-700">{{ $history->creator_name ?: 'Hệ thống' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-12 text-center text-gray-500">Chưa có lịch sử thay đổi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-900">Đơn hàng gần đây</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Mã đơn</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ngày đặt</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Trạng thái</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Tổng tiền</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($orders as $order)
                                <tr>
                                    <td class="px-5 py-4"><a href="{{ route('admin.orders.show', $order->id) }}" class="font-semibold text-blue-600 hover:underline">{{ $order->order_code }}</a></td>
                                    <td class="px-5 py-4 text-sm text-gray-600">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-4 text-center text-sm text-gray-700">{{ $order->order_status }}</td>
                                    <td class="px-5 py-4 text-right font-semibold text-orange-600">{{ number_format((float) $order->total_amount, 0, ',', '.') }}đ</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-12 text-center text-gray-500">Chưa có đơn hàng.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($orders->hasPages())
                    <div class="border-t border-gray-200 px-5 py-4">{{ $orders->links() }}</div>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Đổi trạng thái</h3>

                <form method="POST" action="{{ route('admin.customers.update-status', $customer->id) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Trạng thái mới</label>
                        <select name="status" id="customer-status" required class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                            <option value="active">Đang hoạt động</option>
                            <option value="inactive">Tạm ngừng</option>
                            <option value="blocked">Khóa tài khoản</option>
                        </select>
                    </div>

                    <div id="status-reason-wrapper">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Lý do</label>
                        <textarea name="reason" id="status-reason" rows="4" maxlength="2000" class="w-full rounded-lg border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"></textarea>
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white">Cập nhật trạng thái</button>
                </form>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Loyalty</h3>

                @if ($loyaltyAccount)
                    <dl class="mt-4 space-y-4 text-sm">
                        <div><dt class="text-gray-500">Hạng</dt><dd class="mt-1 font-semibold text-gray-900">{{ $loyaltyAccount->tier_name ?: 'Chưa có hạng' }}</dd></div>
                        <div><dt class="text-gray-500">Điểm khả dụng</dt><dd class="mt-1 font-semibold text-pink-600">{{ number_format((int) $loyaltyAccount->available_points) }}</dd></div>
                        <div><dt class="text-gray-500">Điểm chờ</dt><dd class="mt-1 font-semibold text-orange-600">{{ number_format((int) $loyaltyAccount->pending_points) }}</dd></div>
                        <div><dt class="text-gray-500">Tổng điểm đã nhận</dt><dd class="mt-1 font-semibold text-gray-900">{{ number_format((int) $loyaltyAccount->lifetime_earned_points) }}</dd></div>
                        <div><dt class="text-gray-500">Tổng điểm đã dùng</dt><dd class="mt-1 font-semibold text-gray-900">{{ number_format((int) $loyaltyAccount->lifetime_redeemed_points) }}</dd></div>
                        <div><dt class="text-gray-500">Chi tiêu trọn đời</dt><dd class="mt-1 font-semibold text-gray-900">{{ number_format((float) $loyaltyAccount->lifetime_spending, 0, ',', '.') }}đ</dd></div>
                    </dl>
                @else
                    <p class="mt-4 text-sm text-gray-500">Chưa có tài khoản Loyalty.</p>
                @endif
            </section>


            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Hoạt động khác</h3>

                <dl class="mt-4 space-y-4 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Đánh giá</dt>
                        <dd class="font-semibold text-yellow-600">{{ number_format($activityStatistics['reviews']) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Sản phẩm yêu thích</dt>
                        <dd class="font-semibold text-pink-600">{{ number_format($activityStatistics['favorites']) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Hỏi đáp</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($activityStatistics['questions']) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Coupon đã lưu</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($activityStatistics['saved_coupons']) }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Thống kê đơn</h3>

                <dl class="mt-4 space-y-4 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Tổng đơn</dt><dd class="font-semibold text-gray-900">{{ number_format((int) ($orderStatistics->total_orders ?? 0)) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Hoàn thành</dt><dd class="font-semibold text-green-700">{{ number_format((int) ($orderStatistics->completed_orders ?? 0)) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Đã hủy</dt><dd class="font-semibold text-red-700">{{ number_format((int) ($orderStatistics->cancelled_orders ?? 0)) }}</dd></div>
                    <div><dt class="text-gray-500">Chi tiêu hoàn thành</dt><dd class="mt-1 font-semibold text-orange-600">{{ number_format((float) ($orderStatistics->completed_spending ?? 0), 0, ',', '.') }}đ</dd></div>
                </dl>
            </section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const status = document.getElementById('customer-status');
    const wrapper = document.getElementById('status-reason-wrapper');
    const reason = document.getElementById('status-reason');

    const updateReason = function () {
        const needsReason = status?.value !== 'active';
        wrapper?.classList.toggle('hidden', !needsReason);

        if (reason) {
            reason.required = needsReason;

            if (!needsReason) {
                reason.value = '';
            }
        }
    };

    status?.addEventListener('change', updateReason);
    updateReason();
});
</script>
@endpush
