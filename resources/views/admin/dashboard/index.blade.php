@extends('admin.layouts.master')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('page-description', 'Theo dõi nhanh tình trạng hoạt động của cửa hàng.')

@section('content')

    {{-- =========================
        Thống kê chính
    ========================== --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Tổng đơn hàng --}}
        <div class="admin-card p-5">
            <div class="flex items-start justify-between gap-4">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Tổng đơn hàng
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ number_format($totalOrders) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Tất cả đơn hàng trong hệ thống
                    </p>
                </div>

                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                >
                    <svg
                        class="h-6 w-6"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3.75 4.5h16.5v15H3.75v-15zM7.5 8.25h9M7.5 12h9M7.5 15.75h5.25"
                        />
                    </svg>
                </div>

            </div>
        </div>

        {{-- Đơn chờ xác nhận --}}
        <div class="admin-card p-5">
            <div class="flex items-start justify-between gap-4">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Đơn chờ xác nhận
                    </p>

                    <p class="mt-2 text-3xl font-bold text-amber-600">
                        {{ number_format($pendingOrders) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Cần Admin kiểm tra và xử lý
                    </p>
                </div>

                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600"
                >
                    <svg
                        class="h-6 w-6"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 6v6l4 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>

            </div>
        </div>

        {{-- Đơn hoàn thành --}}
        <div class="admin-card p-5">
            <div class="flex items-start justify-between gap-4">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Đơn hoàn thành
                    </p>

                    <p class="mt-2 text-3xl font-bold text-green-600">
                        {{ number_format($completedOrders) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        {{ number_format($cancelledOrders) }} đơn đã bị hủy
                    </p>
                </div>

                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-600"
                >
                    <svg
                        class="h-6 w-6"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4.5 12.75l4.5 4.5 10.5-10.5"
                        />
                    </svg>
                </div>

            </div>
        </div>

        {{-- Tổng doanh thu --}}
        <div class="admin-card p-5">
            <div class="flex items-start justify-between gap-4">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Tổng doanh thu
                    </p>

                    <p class="mt-2 text-2xl font-bold text-pink-600">
                        {{ number_format((float) $totalRevenue, 0, ',', '.') }}₫
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Chỉ tính đơn đã hoàn thành
                    </p>
                </div>

                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-pink-50 text-pink-600"
                >
                    <svg
                        class="h-6 w-6"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 6v12m3-9.75C15 7.007 13.657 6 12 6s-3 1.007-3 2.25 1.343 2.25 3 2.25 3 1.007 3 2.25S13.657 15 12 15s-3-1.007-3-2.25"
                        />
                    </svg>
                </div>

            </div>
        </div>

    </div>

    {{-- =========================
        Doanh thu theo thời gian
    ========================== --}}
    <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-3">

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Doanh thu hôm nay
            </p>

            <p class="mt-3 text-2xl font-bold text-gray-900">
                {{ number_format((float) $todayRevenue, 0, ',', '.') }}₫
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Tính theo ngày hoàn thành đơn
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Doanh thu tháng này
            </p>

            <p class="mt-3 text-2xl font-bold text-gray-900">
                {{ number_format((float) $monthRevenue, 0, ',', '.') }}₫
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Tháng {{ now()->month }}/{{ now()->year }}
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Tổng khách hàng
            </p>

            <p class="mt-3 text-2xl font-bold text-gray-900">
                {{ number_format($totalCustomers) }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Tài khoản có vai trò customer
            </p>
        </div>

    </div>

    {{-- =========================
        Hàng hóa và tồn kho
    ========================== --}}
    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Tổng sản phẩm
            </p>

            <p class="mt-2 text-2xl font-bold text-gray-900">
                {{ number_format($totalProducts) }}
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Sản phẩm đang hoạt động
            </p>

            <p class="mt-2 text-2xl font-bold text-green-600">
                {{ number_format($activeProducts) }}
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Tồn kho thấp
            </p>

            <p class="mt-2 text-2xl font-bold text-amber-600">
                {{ number_format($lowStockItems) }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Số bản ghi kho dưới mức tối thiểu
            </p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-gray-500">
                Hết hàng
            </p>

            <p class="mt-2 text-2xl font-bold text-red-600">
                {{ number_format($outOfStockItems) }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Số lượng khả dụng bằng 0
            </p>
        </div>

    </div>

    {{-- =========================
        Công việc chờ xử lý
    ========================== --}}
    <div class="mt-6">

        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-900">
                Công việc đang chờ xử lý
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Những nghiệp vụ Admin cần kiểm tra trong hệ thống.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

            <div class="admin-card p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Đánh giá chờ duyệt
                        </p>

                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            {{ number_format($pendingReviews) }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-yellow-50 text-yellow-600"
                    >
                        <svg
                            class="h-6 w-6"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M11.48 3.499a.75.75 0 011.04 0l2.125 2.151 2.99.435a.75.75 0 01.416 1.279l-2.163 2.108.51 2.978a.75.75 0 01-1.088.79L12 11.49l-2.675 1.75a.75.75 0 01-1.088-.79l.51-2.978-2.163-2.108A.75.75 0 017 6.085l2.99-.435 1.49-2.151z"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="admin-card p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Câu hỏi chờ trả lời
                        </p>

                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            {{ number_format($pendingQuestions) }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-50 text-purple-600"
                    >
                        <svg
                            class="h-6 w-6"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8.625 9.75a3.375 3.375 0 116.75 0c0 1.86-1.5 2.625-2.625 3.375-.75.5-.75 1.125-.75 1.875M12 18h.008"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="admin-card p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Yêu cầu trả hàng
                        </p>

                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            {{ number_format($pendingReturnRequests) }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-600"
                    >
                        <svg
                            class="h-6 w-6"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 15l-3-3m0 0l3-3m-3 3h9a4.5 4.5 0 010 9H9"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="admin-card p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Liên hệ mới
                        </p>

                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            {{ number_format($newContactMessages) }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                    >
                        <svg
                            class="h-6 w-6"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21.75 6.75l-9.75 6-9.75-6M3 5.25h18v13.5H3V5.25z"
                            />
                        </svg>
                    </div>
                </div>
            </div>

        </div>

    </div>

    {{-- =========================
        Đơn hàng gần nhất
    ========================== --}}
    <div class="mt-6 admin-card overflow-hidden">

        <div
            class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    Đơn hàng mới nhất
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    8 đơn hàng được tạo gần đây nhất.
                </p>
            </div>

            <span class="text-sm font-medium text-pink-600">
                Quản lý đơn hàng sẽ làm ở bước tiếp theo
            </span>
        </div>

        <div class="overflow-x-auto">

            <table class="admin-table">

                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Trạng thái đơn</th>
                        <th>Thanh toán</th>
                        <th>Vận chuyển</th>
                        <th>Tổng tiền</th>
                        <th>Ngày tạo</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($recentOrders as $order)

                        <tr>

                            <td>
                                <span class="font-semibold text-gray-900">
                                    {{ $order->order_code }}
                                </span>
                            </td>

                            <td>
                                <div>
                                    <p class="font-medium text-gray-900">
                                        {{ $order->user?->name ?? $order->customer_name }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $order->user?->email ?? $order->customer_email }}
                                    </p>
                                </div>
                            </td>

                            <td>
                                @switch($order->order_status)
                                    @case('pending')
                                        <span class="admin-badge admin-badge-warning">
                                            Chờ xác nhận
                                        </span>
                                        @break

                                    @case('confirmed')
                                        <span class="admin-badge admin-badge-info">
                                            Đã xác nhận
                                        </span>
                                        @break

                                    @case('processing')
                                        <span class="admin-badge admin-badge-info">
                                            Đang xử lý
                                        </span>
                                        @break

                                    @case('packed')
                                        <span class="admin-badge admin-badge-info">
                                            Đã đóng gói
                                        </span>
                                        @break

                                    @case('shipping')
                                        <span class="admin-badge admin-badge-info">
                                            Đang giao
                                        </span>
                                        @break

                                    @case('completed')
                                        <span class="admin-badge admin-badge-success">
                                            Hoàn thành
                                        </span>
                                        @break

                                    @case('cancelled')
                                        <span class="admin-badge admin-badge-danger">
                                            Đã hủy
                                        </span>
                                        @break

                                    @default
                                        <span class="admin-badge admin-badge-neutral">
                                            {{ $order->order_status }}
                                        </span>
                                @endswitch
                            </td>

                            <td>
                                @switch($order->payment_status)
                                    @case('paid')
                                        <span class="admin-badge admin-badge-success">
                                            Đã thanh toán
                                        </span>
                                        @break

                                    @case('pending')
                                        <span class="admin-badge admin-badge-warning">
                                            Chờ thanh toán
                                        </span>
                                        @break

                                    @case('failed')
                                        <span class="admin-badge admin-badge-danger">
                                            Thất bại
                                        </span>
                                        @break

                                    @case('refunded')
                                        <span class="admin-badge admin-badge-info">
                                            Đã hoàn tiền
                                        </span>
                                        @break

                                    @case('cancelled')
                                        <span class="admin-badge admin-badge-danger">
                                            Đã hủy
                                        </span>
                                        @break

                                    @default
                                        <span class="admin-badge admin-badge-neutral">
                                            {{ $order->payment_status }}
                                        </span>
                                @endswitch
                            </td>

                            <td>
                                @switch($order->shipping_status)
                                    @case('pending')
                                        <span class="admin-badge admin-badge-warning">
                                            Chờ xử lý
                                        </span>
                                        @break

                                    @case('ready')
                                        <span class="admin-badge admin-badge-info">
                                            Sẵn sàng giao
                                        </span>
                                        @break

                                    @case('shipping')
                                        <span class="admin-badge admin-badge-info">
                                            Đang giao
                                        </span>
                                        @break

                                    @case('delivered')
                                        <span class="admin-badge admin-badge-success">
                                            Đã giao
                                        </span>
                                        @break

                                    @case('cancelled')
                                        <span class="admin-badge admin-badge-danger">
                                            Đã hủy
                                        </span>
                                        @break

                                    @default
                                        <span class="admin-badge admin-badge-neutral">
                                            {{ $order->shipping_status }}
                                        </span>
                                @endswitch
                            </td>

                            <td>
                                <span class="font-semibold text-gray-900">
                                    {{ number_format((float) $order->total_amount, 0, ',', '.') }}₫
                                </span>
                            </td>

                            <td>
                                <div>
                                    <p class="text-gray-700">
                                        {{ $order->created_at?->format('d/m/Y') }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $order->created_at?->format('H:i') }}
                                    </p>
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="7"
                                class="py-12 text-center"
                            >
                                <div class="flex flex-col items-center">

                                    <svg
                                        class="h-12 w-12 text-gray-300"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M3.75 4.5h16.5v15H3.75v-15zM7.5 8.25h9M7.5 12h9"
                                        />
                                    </svg>

                                    <p class="mt-3 font-medium text-gray-700">
                                        Chưa có đơn hàng
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        Các đơn hàng mới sẽ hiển thị tại đây.
                                    </p>

                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

@endsection
