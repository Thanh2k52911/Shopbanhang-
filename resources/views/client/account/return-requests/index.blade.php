@extends('client.layouts.master')

@section(
    'title',
    'Yêu cầu trả hàng / hoàn tiền - Cosmetic Shop'
)

@section('content')
    <section class="order-detail-page">
        <div class="client-container">

            {{-- Breadcrumb --}}
            <nav class="order-detail-page__breadcrumb">
                <a href="{{ route('home') }}">
                    Trang chủ
                </a>

                <span>/</span>

                <a href="{{ route('account.index') }}">
                    Tài khoản
                </a>

                <span>/</span>

                <span>
                    Yêu cầu trả hàng / hoàn tiền
                </span>
            </nav>

            {{-- Header --}}
            <header class="order-detail-page__header">
                <div>
                    <p class="home-section__eyebrow">
                        Hỗ trợ sau mua
                    </p>

                    <h1>
                        Yêu cầu trả hàng / hoàn tiền
                    </h1>

                    <p>
                        Theo dõi toàn bộ yêu cầu trả hàng, đổi sản phẩm
                        và hoàn tiền của bạn.
                    </p>
                </div>

                <a
                    href="{{ route('account.orders.index') }}"
                    class="order-review-toggle"
                >
                    Xem đơn hàng
                </a>
            </header>

            {{-- Thông báo --}}
            @if (session('success'))
                <div class="order-message order-message--success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="order-message order-message--error">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->has('return_request'))
                <div class="order-message order-message--error">
                    {{ $errors->first('return_request') }}
                </div>
            @endif

            {{-- Tổng quan --}}
            <div class="order-detail-page__overview">

                <section class="order-detail-card">
                    <h2>
                        Tổng yêu cầu
                    </h2>

                    <strong>
                        {{ number_format($returnRequests->total()) }}
                    </strong>

                    <p>
                        Tổng số yêu cầu đã gửi.
                    </p>
                </section>

                <section class="order-detail-card">
                    <h2>
                        Đang xử lý
                    </h2>

                    <strong>
                        {{
                            number_format(
                                $returnRequests
                                    ->getCollection()
                                    ->whereIn('status', [
                                        'pending',
                                        'approved',
                                        'waiting_for_return',
                                        'returning',
                                        'received',
                                        'inspecting',
                                        'processing',
                                    ])
                                    ->count()
                            )
                        }}
                    </strong>

                    <p>
                        Số yêu cầu đang hiển thị trên trang này.
                    </p>
                </section>

                <section class="order-detail-card">
                    <h2>
                        Đã hoàn tất
                    </h2>

                    <strong>
                        {{
                            number_format(
                                $returnRequests
                                    ->getCollection()
                                    ->where('status', 'completed')
                                    ->count()
                            )
                        }}
                    </strong>

                    <p>
                        Yêu cầu hoàn tất trên trang hiện tại.
                    </p>
                </section>

            </div>

            {{-- Danh sách --}}
            <section class="order-detail-products">

                <div class="order-detail-products__heading">
                    <div>
                        <p class="home-section__eyebrow">
                            Danh sách
                        </p>

                        <h2>
                            Các yêu cầu của bạn
                        </h2>

                        <p>
                            Tìm thấy
                            {{ number_format($returnRequests->total()) }}
                            yêu cầu.
                        </p>
                    </div>
                </div>

                @forelse ($returnRequests as $returnRequest)

                    @php
                        $statusLabel =
                            $statuses[$returnRequest->status]
                            ?? $returnRequest->status;

                        $requestTypeLabel =
                            $requestTypes[$returnRequest->request_type]
                            ?? $returnRequest->request_type;

                        $statusClass = match (
                            $returnRequest->status
                        ) {
                            'pending' =>
                                'order-status-badge--pending',

                            'approved',
                            'waiting_for_return',
                            'returning',
                            'received',
                            'inspecting',
                            'processing' =>
                                'order-status-badge--processing',

                            'completed' =>
                                'order-status-badge--completed',

                            'rejected',
                            'cancelled' =>
                                'order-status-badge--cancelled',

                            default =>
                                'order-status-badge--pending',
                        };
                    @endphp

                    <article class="order-detail-product">

                        {{-- Thông tin chính --}}
                        <div class="order-detail-product__information">

                            <div>
                                <span
                                    class="order-status-badge {{ $statusClass }}"
                                >
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            <h3>
                                <a
                                    href="{{ route(
                                        'account.return-requests.show',
                                        $returnRequest->return_code
                                    ) }}"
                                >
                                    {{ $returnRequest->return_code }}
                                </a>
                            </h3>

                            <p>
                                Loại yêu cầu:
                                <strong>
                                    {{ $requestTypeLabel }}
                                </strong>
                            </p>

                            @if ($returnRequest->order)
                                <p>
                                    Đơn hàng:
                                    <a
                                        href="{{ route(
                                            'account.orders.show',
                                            $returnRequest->order->order_code
                                        ) }}"
                                    >
                                        {{ $returnRequest->order->order_code }}
                                    </a>
                                </p>
                            @endif

                            <p>
                                Lý do:
                                {{ $returnRequest->reason }}
                            </p>

                            <p>
                                {{ number_format(
                                    $returnRequest->items_count
                                ) }}
                                sản phẩm,
                                {{ number_format(
                                    $returnRequest->images_count
                                ) }}
                                ảnh bằng chứng
                            </p>

                            <p>
                                Tạo lúc
                                {{ $returnRequest->created_at?->format(
                                    'd/m/Y H:i'
                                ) }}
                            </p>

                        </div>

                        {{-- Giá trị yêu cầu --}}
                        <div class="order-detail-product__price">

                            <span>
                                Số tiền yêu cầu
                            </span>

                            <strong>
                                {{ number_format(
                                    (float) $returnRequest->requested_amount,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </strong>

                            @if (
                                ! is_null(
                                    $returnRequest->approved_amount
                                )
                                && (float) $returnRequest->approved_amount > 0
                            )
                                <span>
                                    Được duyệt:
                                    {{ number_format(
                                        (float) $returnRequest->approved_amount,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ
                                </span>
                            @endif

                            <a
                                href="{{ route(
                                    'account.return-requests.show',
                                    $returnRequest->return_code
                                ) }}"
                                class="order-review-toggle"
                            >
                                Xem chi tiết
                            </a>

                        </div>

                    </article>

                @empty

                    <div class="order-detail-card">
                        <h2>
                            Chưa có yêu cầu
                        </h2>

                        <p>
                            Bạn chưa gửi yêu cầu trả hàng, đổi sản phẩm
                            hoặc hoàn tiền nào.
                        </p>

                        <a
                            href="{{ route('account.orders.index') }}"
                            class="order-review-toggle"
                        >
                            Xem đơn hàng đã mua
                        </a>
                    </div>

                @endforelse

            </section>

            {{-- Phân trang --}}
            @if ($returnRequests->hasPages())
                <div class="mt-6">
                    {{ $returnRequests->links() }}
                </div>
            @endif

        </div>
    </section>
@endsection
