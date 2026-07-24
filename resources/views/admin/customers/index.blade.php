@extends('admin.layouts.master')

@section('title', 'Quản lý khách hàng')
@section('page-title', 'Quản lý khách hàng')
@section('page-description', 'Theo dõi hồ sơ, trạng thái, đăng nhập và hoạt động mua hàng.')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Khách hàng</h2>
        <p class="mt-1 text-sm text-gray-500">Danh sách tài khoản có role customer.</p>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ([
            ['Tổng khách hàng', $statistics['total'], 'text-gray-900'],
            ['Đang hoạt động', $statistics['active'], 'text-green-600'],
            ['Tạm ngừng', $statistics['inactive'], 'text-orange-600'],
            ['Đã khóa', $statistics['blocked'], 'text-red-600'],
            ['Đã xác minh', $statistics['verified'], 'text-blue-600'],
            ['Loyalty', $statistics['loyalty_members'], 'text-pink-600'],
        ] as [$label, $value, $class])
            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <strong class="mt-2 block text-2xl {{ $class }}">{{ number_format($value) }}</strong>
            </article>
        @endforeach
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="xl:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Tìm kiếm</label>
                <input type="text" name="keyword" value="{{ request('keyword') }}"
                       placeholder="Tên, email, số điện thoại hoặc IP..."
                       class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Trạng thái</label>
                <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') === 'active')>Đang hoạt động</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Tạm ngừng</option>
                    <option value="blocked" @selected(request('status') === 'blocked')>Đã khóa</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Email</label>
                <select name="verification" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="verified" @selected(request('verification') === 'verified')>Đã xác minh</option>
                    <option value="unverified" @selected(request('verification') === 'unverified')>Chưa xác minh</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Đơn hàng</label>
                <select name="order_status" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="has_orders" @selected(request('order_status') === 'has_orders')>Đã có đơn</option>
                    <option value="no_orders" @selected(request('order_status') === 'no_orders')>Chưa có đơn</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Loyalty</label>
                <select name="loyalty" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="member" @selected(request('loyalty') === 'member')>Thành viên</option>
                    <option value="non_member" @selected(request('loyalty') === 'non_member')>Chưa tham gia</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Sắp xếp</label>
                <select name="sort" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Mới nhất</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                    <option value="name_asc" @selected(request('sort') === 'name_asc')>Tên A → Z</option>
                    <option value="orders_desc" @selected(request('sort') === 'orders_desc')>Nhiều đơn nhất</option>
                    <option value="spending_desc" @selected(request('sort') === 'spending_desc')>Chi tiêu cao nhất</option>
                    <option value="last_order_desc" @selected(request('sort') === 'last_order_desc')>Đặt hàng gần nhất</option>
                    <option value="last_login_desc" @selected(request('sort') === 'last_login_desc')>Đăng nhập gần nhất</option>
                </select>
            </div>

            <div class="flex gap-3 md:col-span-2 xl:col-span-5">
                <button class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white">Lọc dữ liệu</button>
                <a href="{{ route('admin.customers.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-lg font-bold text-gray-900">Danh sách khách hàng</h3>
            <p class="mt-1 text-sm text-gray-500">Hiển thị {{ $customers->count() }} / {{ $customers->total() }} kết quả.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Khách hàng</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Trạng thái</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Đơn</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Chi tiêu</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Đăng nhập cuối</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($customers as $customer)
                        @php
                            $statusClasses = match ($customer->status) {
                                'active' => 'bg-green-100 text-green-700',
                                'inactive' => 'bg-orange-100 text-orange-700',
                                'blocked' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700',
                            };

                            $statusLabel = match ($customer->status) {
                                'active' => 'Hoạt động',
                                'inactive' => 'Tạm ngừng',
                                'blocked' => 'Đã khóa',
                                default => $customer->status,
                            };
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="min-w-[250px]">
                                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="font-semibold text-gray-900 hover:text-pink-600">
                                        {{ $customer->name }}
                                    </a>
                                    <p class="mt-1 text-xs text-gray-500">{{ $customer->email }}</p>
                                    <p class="mt-1 text-xs text-gray-400">
                                        Điểm: {{ number_format((int) ($customer->available_points ?? 0)) }}
                                        · {{ number_format((int) ($customer->addresses_count ?? 0)) }} địa chỉ
                                    </p>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $statusLabel }}</span>
                            </td>

                            <td class="px-5 py-4 text-center font-semibold text-blue-600">{{ number_format((int) $customer->orders_count) }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-orange-600">{{ number_format((float) $customer->completed_spending, 0, ',', '.') }}đ</td>

                            <td class="px-5 py-4 text-sm text-gray-600">
                                <p>{{ $customer->last_login_at ? \Carbon\Carbon::parse($customer->last_login_at)->format('d/m/Y H:i') : 'Chưa đăng nhập' }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ $customer->last_login_ip ?: 'Không có IP' }}</p>
                                <p class="mt-1 text-xs text-gray-400">
                                    Đơn gần nhất:
                                    {{ $customer->last_order_at
                                        ? \Carbon\Carbon::parse($customer->last_order_at)->format('d/m/Y H:i')
                                        : 'Chưa có' }}
                                </p>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700">CHI TIẾT</a>
                                    <a href="{{ route('admin.customers.edit', $customer->id) }}" class="rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 text-xs font-bold text-orange-700">SỬA</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-16 text-center text-gray-500">Không tìm thấy khách hàng.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">{{ $customers->links() }}</div>
        @endif
    </section>
</div>
@endsection
