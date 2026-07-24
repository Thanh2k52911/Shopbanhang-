@extends('client.layouts.master')

@section(
    'title',
    'Chi tiết yêu cầu ' . $returnRequest->return_code . ' - Cosmetic Shop'
)

@php
    $statusLabel =
        $statuses[$returnRequest->status]
        ?? $returnRequest->status;

    $requestTypeLabel =
        $requestTypes[$returnRequest->request_type]
        ?? $returnRequest->request_type;

    $statusClass = match ($returnRequest->status) {
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

    $refundStatusLabels = [
        'pending' => 'Chờ xử lý',
        'processing' => 'Đang xử lý',
        'completed' => 'Đã hoàn tiền',
        'failed' => 'Hoàn tiền thất bại',
        'cancelled' => 'Đã hủy',
    ];

    $refundMethodLabels = [
        'original_payment' => 'Phương thức thanh toán gốc',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'cash' => 'Tiền mặt',
        'store_credit' => 'Ví cửa hàng',
        'coupon' => 'Mã giảm giá',
    ];
@endphp

@section('content')
    <section class="order-detail-page">
        <div class="client-container">

            {{-- Breadcrumb --}}
            <nav class="order-detail-page__breadcrumb">
                <a href="{{ route('home') }}">
                    Trang chủ
                </a>

                <span>/</span>

                <a href="{{ route('account.return-requests.index') }}">
                    Yêu cầu trả hàng / hoàn tiền
                </a>

                <span>/</span>

                <span>
                    {{ $returnRequest->return_code }}
                </span>
            </nav>

            {{-- Header --}}
            <header class="order-detail-page__header">

                <div>
                    <p class="home-section__eyebrow">
                        Chi tiết yêu cầu
                    </p>

                    <h1>
                        {{ $returnRequest->return_code }}
                    </h1>

                    <p>
                        Tạo lúc
                        {{ $returnRequest->created_at?->format(
                            'd/m/Y H:i'
                        ) }}
                    </p>
                </div>

                <span
                    class="order-status-badge {{ $statusClass }}"
                >
                    {{ $statusLabel }}
                </span>

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
                        Loại yêu cầu
                    </h2>

                    <strong>
                        {{ $requestTypeLabel }}
                    </strong>

                    <p>
                        Trạng thái:
                        {{ $statusLabel }}
                    </p>
                </section>

                <section class="order-detail-card">
                    <h2>
                        Số tiền yêu cầu
                    </h2>

                    <strong>
                        {{ number_format(
                            (float) $returnRequest->requested_amount,
                            0,
                            ',',
                            '.'
                        ) }}đ
                    </strong>

                    <p>
                        Giá trị khách hàng đề nghị.
                    </p>
                </section>

                <section class="order-detail-card">
                    <h2>
                        Số tiền được duyệt
                    </h2>

                    <strong>
                        @if (
                            ! is_null($returnRequest->approved_amount)
                            && (float) $returnRequest->approved_amount > 0
                        )
                            {{ number_format(
                                (float) $returnRequest->approved_amount,
                                0,
                                ',',
                                '.'
                            ) }}đ
                        @else
                            Chưa duyệt
                        @endif
                    </strong>

                    <p>
                        Phí gửi trả:
                        {{ number_format(
                            (float) $returnRequest->return_shipping_fee,
                            0,
                            ',',
                            '.'
                        ) }}đ
                    </p>
                </section>

            </div>

            <div class="order-detail-page__bottom">

                {{-- Cột nội dung chính --}}
                <div>

                    {{-- Nội dung yêu cầu --}}
                    <section class="order-detail-card">
                        <h2>
                            Nội dung yêu cầu
                        </h2>

                        <dl>
                            <div>
                                <dt>
                                    Lý do
                                </dt>

                                <dd>
                                    {{ $returnRequest->reason }}
                                </dd>
                            </div>

                            @if ($returnRequest->description)
                                <div>
                                    <dt>
                                        Mô tả chi tiết
                                    </dt>

                                    <dd>
                                        {!! nl2br(
                                            e($returnRequest->description)
                                        ) !!}
                                    </dd>
                                </div>
                            @endif

                            @if ($returnRequest->customer_note)
                                <div>
                                    <dt>
                                        Ghi chú của bạn
                                    </dt>

                                    <dd>
                                        {!! nl2br(
                                            e($returnRequest->customer_note)
                                        ) !!}
                                    </dd>
                                </div>
                            @endif

                            @if ($returnRequest->admin_note)
                                <div>
                                    <dt>
                                        Phản hồi từ cửa hàng
                                    </dt>

                                    <dd>
                                        {!! nl2br(
                                            e($returnRequest->admin_note)
                                        ) !!}
                                    </dd>
                                </div>
                            @endif

                            @if ($returnRequest->rejection_reason)
                                <div>
                                    <dt>
                                        Lý do từ chối
                                    </dt>

                                    <dd>
                                        {!! nl2br(
                                            e($returnRequest->rejection_reason)
                                        ) !!}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </section>

                    {{-- Sản phẩm --}}
                    <section class="order-detail-products">

                        <div class="order-detail-products__heading">
                            <div>
                                <p class="home-section__eyebrow">
                                    Sản phẩm
                                </p>

                                <h2>
                                    Sản phẩm trong yêu cầu
                                </h2>

                                <p>
                                    {{ number_format(
                                        $returnRequest->items->count()
                                    ) }}
                                    dòng sản phẩm.
                                </p>
                            </div>
                        </div>

                        @forelse ($returnRequest->items as $returnItem)
                            @php
                                $orderItem = $returnItem->orderItem;

                                $conditionLabel =
                                    $productConditions[
                                        $returnItem->product_condition
                                    ]
                                    ?? $returnItem->product_condition
                                    ?? 'Chưa cập nhật';
                            @endphp

                            <article class="order-detail-product">

                                <div class="order-detail-product__image">
                                    @if ($orderItem?->image_path)
                                        <img
                                            src="{{ asset(
                                                'images/'
                                                . $orderItem->image_path
                                            ) }}"
                                            alt="{{ $orderItem->product_name }}"
                                        >
                                    @else
                                        <span>
                                            Cosmetic Shop
                                        </span>
                                    @endif
                                </div>

                                <div class="order-detail-product__information">
                                    <h3>
                                        {{ $orderItem?->product_name
                                            ?? 'Sản phẩm không còn tồn tại' }}
                                    </h3>

                                    @if ($orderItem?->sku_code)
                                        <p>
                                            SKU:
                                            {{ $orderItem->sku_code }}
                                        </p>
                                    @endif

                                    @if ($orderItem?->variant_name)
                                        <p>
                                            Phân loại:
                                            {{ $orderItem->variant_name }}
                                        </p>
                                    @endif

                                    <p>
                                        Số lượng yêu cầu:
                                        <strong>
                                            {{ $returnItem->quantity }}
                                        </strong>
                                    </p>

                                    <p>
                                        Tình trạng:
                                        <strong>
                                            {{ $conditionLabel }}
                                        </strong>
                                    </p>

                                    @if ($returnItem->reason)
                                        <p>
                                            Lý do:
                                            {{ $returnItem->reason }}
                                        </p>
                                    @endif

                                    @if ($returnItem->description)
                                        <p>
                                            Mô tả:
                                            {{ $returnItem->description }}
                                        </p>
                                    @endif

                                    @if ($returnItem->inspection_note)
                                        <p>
                                            Kết quả kiểm tra:
                                            {{ $returnItem->inspection_note }}
                                        </p>
                                    @endif
                                </div>

                                <div class="order-detail-product__price">
                                    <span>
                                        Tiền yêu cầu
                                    </span>

                                    <strong>
                                        {{ number_format(
                                            (float) $returnItem
                                                ->requested_refund_amount,
                                            0,
                                            ',',
                                            '.'
                                        ) }}đ
                                    </strong>

                                    @if (
                                        ! is_null(
                                            $returnItem
                                                ->approved_refund_amount
                                        )
                                        && (float) $returnItem
                                            ->approved_refund_amount > 0
                                    )
                                        <span>
                                            Được duyệt:
                                            {{ number_format(
                                                (float) $returnItem
                                                    ->approved_refund_amount,
                                                0,
                                                ',',
                                                '.'
                                            ) }}đ
                                        </span>
                                    @endif
                                </div>

                            </article>

                        @empty

                            <div class="order-detail-card">
                                <p>
                                    Yêu cầu chưa có sản phẩm.
                                </p>
                            </div>

                        @endforelse

                    </section>

                    {{-- Ảnh bằng chứng --}}
                    <section class="order-detail-card">
                        <h2>
                            Ảnh bằng chứng
                        </h2>

                        @if ($returnRequest->images->isNotEmpty())
                            <div class="order-review-preview">

                                @foreach ($returnRequest->images as $image)
                                    <a
                                        href="{{ asset(
                                            'storage/' . $image->image_path
                                        ) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="order-review-preview__item"
                                    >
                                        <img
                                            src="{{ asset(
                                                'storage/' . $image->image_path
                                            ) }}"
                                            alt="{{ $image->caption
                                                ?: 'Ảnh bằng chứng' }}"
                                        >
                                    </a>
                                @endforeach

                            </div>
                        @else
                            <p>
                                Yêu cầu chưa có ảnh bằng chứng.
                            </p>
                        @endif
                    </section>

                    {{-- Refund --}}
                    <section class="order-detail-card">
                        <h2>
                            Thông tin hoàn tiền
                        </h2>

                        @forelse ($returnRequest->refunds as $refund)
                            <dl>
                                <div>
                                    <dt>
                                        Mã hoàn tiền
                                    </dt>

                                    <dd>
                                        {{ $refund->refund_code }}
                                    </dd>
                                </div>

                                <div>
                                    <dt>
                                        Số tiền
                                    </dt>

                                    <dd>
                                        {{ number_format(
                                            (float) $refund->amount,
                                            0,
                                            ',',
                                            '.'
                                        ) }}đ
                                    </dd>
                                </div>

                                <div>
                                    <dt>
                                        Phương thức
                                    </dt>

                                    <dd>
                                        {{ $refundMethodLabels[
                                            $refund->method
                                        ] ?? $refund->method }}
                                    </dd>
                                </div>

                                <div>
                                    <dt>
                                        Trạng thái
                                    </dt>

                                    <dd>
                                        {{ $refundStatusLabels[
                                            $refund->status
                                        ] ?? $refund->status }}
                                    </dd>
                                </div>

                                @if ($refund->provider_transaction_id)
                                    <div>
                                        <dt>
                                            Mã giao dịch
                                        </dt>

                                        <dd>
                                            {{ $refund
                                                ->provider_transaction_id }}
                                        </dd>
                                    </div>
                                @endif

                                @if ($refund->completed_at)
                                    <div>
                                        <dt>
                                            Hoàn tất lúc
                                        </dt>

                                        <dd>
                                            {{ $refund->completed_at->format(
                                                'd/m/Y H:i'
                                            ) }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        @empty
                            <p>
                                Chưa có giao dịch hoàn tiền liên quan.
                            </p>
                        @endforelse
                    </section>

                </div>

                {{-- Cột bên phải --}}
                <aside>

                    {{-- Đơn hàng --}}
                    <section class="order-detail-summary">
                        <h2>
                            Đơn hàng liên quan
                        </h2>

                        @if ($returnRequest->order)
                            <dl>
                                <div>
                                    <dt>
                                        Mã đơn
                                    </dt>

                                    <dd>
                                        <a
                                            href="{{ route(
                                                'account.orders.show',
                                                $returnRequest
                                                    ->order
                                                    ->order_code
                                            ) }}"
                                        >
                                            {{ $returnRequest
                                                ->order
                                                ->order_code }}
                                        </a>
                                    </dd>
                                </div>

                                <div>
                                    <dt>
                                        Trạng thái đơn
                                    </dt>

                                    <dd>
                                        {{ $returnRequest
                                            ->order
                                            ->order_status }}
                                    </dd>
                                </div>

                                <div>
                                    <dt>
                                        Thanh toán
                                    </dt>

                                    <dd>
                                        {{ $returnRequest
                                            ->order
                                            ->payment_status }}
                                    </dd>
                                </div>

                                <div>
                                    <dt>
                                        Vận chuyển
                                    </dt>

                                    <dd>
                                        {{ $returnRequest
                                            ->order
                                            ->shipping_status }}
                                    </dd>
                                </div>

                                <div class="order-detail-summary__total">
                                    <dt>
                                        Tổng tiền đơn
                                    </dt>

                                    <dd>
                                        {{ number_format(
                                            (float) $returnRequest
                                                ->order
                                                ->total_amount,
                                            0,
                                            ',',
                                            '.'
                                        ) }}đ
                                    </dd>
                                </div>
                            </dl>
                        @else
                            <p>
                                Không tìm thấy đơn hàng.
                            </p>
                        @endif
                    </section>

                    {{-- Timeline --}}
                    <section class="order-timeline">
                        <h2>
                            Lịch sử xử lý
                        </h2>

                        @forelse (
                            $returnRequest->statusHistories
                                ->sortByDesc('created_at')
                            as $history
                        )
                            <article class="order-timeline__item">
                                <span class="order-timeline__dot"></span>

                                <div>
                                    <strong>
                                        {{ $statuses[
                                            $history->to_status
                                        ] ?? $history->to_status }}
                                    </strong>

                                    @if ($history->from_status)
                                        <p>
                                            Chuyển từ
                                            {{ $statuses[
                                                $history->from_status
                                            ] ?? $history->from_status }}
                                        </p>
                                    @endif

                                    @if ($history->note)
                                        <p>
                                            {{ $history->note }}
                                        </p>
                                    @endif

                                    <time>
                                        {{ $history->created_at?->format(
                                            'd/m/Y H:i'
                                        ) }}
                                    </time>
                                </div>
                            </article>
                        @empty
                            <p>
                                Chưa có lịch sử xử lý.
                            </p>
                        @endforelse
                    </section>

                    {{-- Thao tác --}}
                    <section class="order-detail-summary">
                        <h2>
                            Thao tác
                        </h2>

                        @if ($returnRequest->canBeCancelled())
                            <form
                                action="{{ route(
                                    'account.return-requests.cancel',
                                    $returnRequest->return_code
                                ) }}"
                                method="POST"
                                class="order-cancel-form"
                                onsubmit="return confirm(
                                    'Bạn có chắc chắn muốn hủy yêu cầu này?'
                                );"
                            >
                                @csrf
                                @method('PATCH')

                                <p>
                                    Bạn chỉ nên hủy khi chưa gửi sản phẩm
                                    về cửa hàng.
                                </p>

                                <button
                                    type="submit"
                                    class="order-detail-summary__cancel"
                                >
                                    Hủy yêu cầu
                                </button>
                            </form>
                        @elseif (
                            $returnRequest->status === 'completed'
                        )
                            <div
                                class="order-message order-message--success"
                            >
                                Yêu cầu đã được xử lý hoàn tất.
                            </div>
                        @elseif (
                            $returnRequest->status === 'rejected'
                        )
                            <div
                                class="order-message order-message--error"
                            >
                                Yêu cầu đã bị từ chối.
                            </div>
                        @elseif (
                            $returnRequest->status === 'cancelled'
                        )
                            <div class="order-message">
                                Yêu cầu đã được hủy.
                            </div>
                        @else
                            <div class="order-message">
                                Yêu cầu đang được cửa hàng xử lý.
                            </div>
                        @endif

                        <a
                            href="{{ route(
                                'account.return-requests.index'
                            ) }}"
                            class="order-review-toggle"
                        >
                            Quay lại danh sách
                        </a>
                    </section>

                </aside>

            </div>

        </div>
    </section>
@endsection
