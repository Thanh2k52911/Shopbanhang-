@extends('admin.layouts.master')

@section('title', 'Phương thức vận chuyển')
@section('page-title', 'Phương thức vận chuyển')
@section('page-description', 'Quản lý phí giao hàng, mức miễn phí và thời gian giao dự kiến.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Phương thức vận chuyển</h2>
            <p class="mt-1 text-sm text-gray-500">Cấu hình các hình thức giao hàng được dùng tại checkout và khi tạo vận đơn.</p>
        </div>

        <a href="{{ route('admin.shipping-methods.create') }}" class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700">
            + Thêm phương thức
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Tổng phương thức', 'value' => $statistics['total'], 'class' => 'text-gray-900'],
            ['label' => 'Đang hoạt động', 'value' => $statistics['active'], 'class' => 'text-green-600'],
            ['label' => 'Đang tắt', 'value' => $statistics['inactive'], 'class' => 'text-gray-500'],
            ['label' => 'Có mức miễn phí', 'value' => $statistics['free_shipping'], 'class' => 'text-pink-600'],
        ] as $card)
            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
                <strong class="mt-2 block text-2xl {{ $card['class'] }}">{{ number_format($card['value']) }}</strong>
            </article>
        @endforeach
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form method="GET" action="{{ route('admin.shipping-methods.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="xl:col-span-2">
                <label for="keyword" class="mb-1.5 block text-sm font-medium text-gray-700">Tìm kiếm</label>
                <input id="keyword" type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tên, mã hoặc nhà cung cấp..." class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
            </div>

            <div>
                <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700">Trạng thái</label>
                <select id="status" name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="1" @selected((string) request('status') === '1')>Đang hoạt động</option>
                    <option value="0" @selected((string) request('status') === '0')>Đang tắt</option>
                </select>
            </div>

            <div>
                <label for="sort" class="mb-1.5 block text-sm font-medium text-gray-700">Sắp xếp</label>
                <select id="sort" name="sort" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Mặc định</option>
                    <option value="name_asc" @selected(request('sort') === 'name_asc')>Tên A → Z</option>
                    <option value="name_desc" @selected(request('sort') === 'name_desc')>Tên Z → A</option>
                    <option value="fee_asc" @selected(request('sort') === 'fee_asc')>Phí thấp nhất</option>
                    <option value="fee_desc" @selected(request('sort') === 'fee_desc')>Phí cao nhất</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                </select>
            </div>

            <div class="flex flex-wrap items-end gap-3 md:col-span-2 xl:col-span-4">
                <button type="submit" class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-800">Lọc dữ liệu</button>
                <a href="{{ route('admin.shipping-methods.index') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Phương thức</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Phí giao hàng</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Thời gian</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Vận đơn</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Trạng thái</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($shippingMethods as $method)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.shipping-methods.show', $method) }}" class="font-semibold text-gray-900 hover:text-pink-600">{{ $method->name }}</a>
                                <div class="mt-1 text-xs text-gray-500">{{ $method->code }}{{ $method->provider ? ' · '.$method->provider : '' }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700">
                                <div class="font-semibold">{{ number_format((float) $method->base_fee) }}₫</div>
                                <div class="mt-1 text-xs text-gray-500">
                                    @if ($method->free_shipping_minimum !== null)
                                        Miễn phí từ {{ number_format((float) $method->free_shipping_minimum) }}₫
                                    @else
                                        Không có mức miễn phí
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700">{{ $method->estimated_delivery_text }}</td>
                            <td class="px-5 py-4 text-sm font-semibold text-gray-700">{{ number_format($method->shipments_count) }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $method->status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $method->status ? 'Đang hoạt động' : 'Đang tắt' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right text-sm">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.shipping-methods.edit', $method) }}" class="font-semibold text-blue-600 hover:text-blue-700">Sửa</a>
                                    <form method="POST" action="{{ route('admin.shipping-methods.toggle', $method) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="font-semibold {{ $method->status ? 'text-amber-600' : 'text-green-600' }}">
                                            {{ $method->status ? 'Tắt' : 'Bật' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500">Chưa có phương thức vận chuyển.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($shippingMethods->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">{{ $shippingMethods->links() }}</div>
        @endif
    </section>
</div>
@endsection
