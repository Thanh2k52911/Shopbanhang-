@extends('client.layouts.master')

@section(
    'title',
    'Chi tiết đơn ' . $order->order_code . ' - Cosmetic Shop'
)

@php
    $statusLabels = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'processing' => 'Đang xử lý',
        'packed' => 'Đã đóng gói',
        'shipping' => 'Đang giao hàng',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];
@endphp

@section('content')
    <section class="order-detail-page">
        <div class="client-container">
            <nav class="order-detail-page__breadcrumb">
                <a href="{{ route('home') }}">
                    Trang chủ
                </a>

                <span>/</span>

                <a href="{{ route('account.orders.index') }}">
                    Đơn hàng của tôi
                </a>

                <span>/</span>

                <span>{{ $order->order_code }}</span>
            </nav>

            <header class="order-detail-page__header">
                @if (session('order_success'))
    <div class="order-message order-message--success">
        {{ session('order_success') }}
    </div>
@endif

@if ($errors->has('order'))
    <div class="order-message order-message--error">
        {{ $errors->first('order') }}
    </div>
@endif
                <div>
                    <p class="home-section__eyebrow">
                        Chi tiết đơn hàng
                    </p>

                    <h1>{{ $order->order_code }}</h1>

                    <p>
                        Đặt lúc
                        {{ \Carbon\Carbon::parse(
                            $order->created_at
                        )->format('d/m/Y H:i') }}
                    </p>
                </div>

                <span
                    class="order-status-badge
                        order-status-badge--{{ $order->order_status }}"
                >
                    {{ $statusLabels[$order->order_status]
                        ?? $order->order_status }}
                </span>
            </header>

            <div class="order-detail-page__overview">
                <section class="order-detail-card">
                    <h2>Thông tin nhận hàng</h2>

                    @if ($address)
                        <p>
                            <strong>
                                {{ $address->receiver_name }}
                            </strong>
                        </p>

                        <p>{{ $address->phone }}</p>

                        @if ($address->email)
                            <p>{{ $address->email }}</p>
                        @endif

                        <p>{{ $address->full_address }}</p>

                        @if ($address->note)
                            <p>
                                Ghi chú:
                                {{ $address->note }}
                            </p>
                        @endif
                    @else
                        <p>Không có thông tin địa chỉ.</p>
                    @endif
                </section>

                <section class="order-detail-card">
                    <h2>Thanh toán</h2>

                    <dl>
                        <div>
                            <dt>Phương thức</dt>
                            <dd>
                                {{ strtoupper(
                                    $order->payment_method
                                ) }}
                            </dd>
                        </div>

                        <div>
                            <dt>Trạng thái</dt>
                            <dd>{{ $order->payment_status }}</dd>
                        </div>

                        @if ($payment)
                            <div>
                                <dt>Mã thanh toán</dt>
                                <dd>{{ $payment->payment_code }}</dd>
                            </div>
                        @endif
                    </dl>
                </section>

                <section class="order-detail-card">
                    <h2>Trạng thái giao hàng</h2>

                    <dl>
                        <div>
                            <dt>Đơn hàng</dt>
                            <dd>
                                {{ $statusLabels[
                                    $order->order_status
                                ] ?? $order->order_status }}
                            </dd>
                        </div>

                        <div>
                            <dt>Vận chuyển</dt>
                            <dd>{{ $order->shipping_status }}</dd>
                        </div>
                    </dl>
                </section>
            </div>

            <section class="order-detail-products">
    <div class="order-detail-products__heading">
        <div>
            <p class="home-section__eyebrow">
                Sản phẩm đã mua
            </p>

            <h2>Sản phẩm trong đơn hàng</h2>
        </div>

        @if ($order->order_status === 'completed')
            <span class="order-review-notice">
                Bạn có thể đánh giá các sản phẩm đã nhận
            </span>
        @endif
    </div>

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

    @foreach ($items as $item)
        @php
            $isCurrentReviewForm =
                (int) old('order_item_id') === (int) $item->id;

            $canReview =
                $order->order_status === 'completed'
                && !$item->is_reviewed
                && !empty($item->product_id);
        @endphp

        <article
            class="order-detail-product"
            data-review-item="{{ $item->id }}"
        >
            @if ($item->product_slug)
                <a
                    href="{{ route(
                        'products.show',
                        $item->product_slug
                    ) }}"
                    class="order-detail-product__image"
                >
                    @if ($item->image_path)
                        <img
                            src="{{ asset(
                                'images/' . $item->image_path
                            ) }}"
                            alt="{{ $item->product_name }}"
                        >
                    @else
                        <span>Cosmetic Shop</span>
                    @endif
                </a>
            @else
                <div class="order-detail-product__image">
                    @if ($item->image_path)
                        <img
                            src="{{ asset(
                                'images/' . $item->image_path
                            ) }}"
                            alt="{{ $item->product_name }}"
                        >
                    @else
                        <span>Cosmetic Shop</span>
                    @endif
                </div>
            @endif

            <div class="order-detail-product__information">
                <h3>
                    @if ($item->product_slug)
                        <a href="{{ route(
                            'products.show',
                            $item->product_slug
                        ) }}">
                            {{ $item->product_name }}
                        </a>
                    @else
                        {{ $item->product_name }}
                    @endif
                </h3>

                @if ($item->sku_code)
                    <p>
                        SKU: {{ $item->sku_code }}
                    </p>
                @endif

                @if ($item->variant_name)
                    <p>
                        Phân loại:
                        {{ $item->variant_name }}
                    </p>
                @endif

                <p>
                    Số lượng:
                    {{ $item->quantity }}
                </p>

                <div class="order-detail-product__review-action">
                    @if ($item->is_reviewed)
                        <span class="order-review-status order-review-status--done">
                            ✓ Đã đánh giá
                        </span>
                    @elseif ($canReview)
                        <button
                            type="button"
                            class="order-review-toggle"
                            data-review-toggle="{{ $item->id }}"
                            aria-expanded="{{ $isCurrentReviewForm
                                ? 'true'
                                : 'false' }}"
                            aria-controls="review-form-{{ $item->id }}"
                        >
                            Đánh giá sản phẩm
                        </button>
                    @elseif (
                        $order->order_status === 'completed'
                        && !$item->product_id
                    )
                        <span class="order-review-status">
                            Sản phẩm không còn tồn tại
                        </span>
                    @endif
                </div>
            </div>

            <div class="order-detail-product__price">
                @if ($item->discount_amount > 0)
                    <del>
                        {{ number_format(
                            $item->original_price,
                            0,
                            ',',
                            '.'
                        ) }}đ
                    </del>
                @endif

                <strong>
                    {{ number_format(
                        $item->unit_price,
                        0,
                        ',',
                        '.'
                    ) }}đ
                </strong>

                <span>
                    Thành tiền:
                    {{ number_format(
                        $item->total_price,
                        0,
                        ',',
                        '.'
                    ) }}đ
                </span>
            </div>

            @if ($canReview)
                <div
                    id="review-form-{{ $item->id }}"
                    class="order-review-panel"
                    data-review-panel="{{ $item->id }}"
                    @if (!$isCurrentReviewForm)
                        hidden
                    @endif
                >
                    <div class="order-review-panel__header">
                        <div>
                            <h4>
                                Đánh giá {{ $item->product_name }}
                            </h4>

                            <p>
                                Chia sẻ trải nghiệm thực tế của bạn
                                về sản phẩm.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="order-review-panel__close"
                            data-review-close="{{ $item->id }}"
                            aria-label="Đóng form đánh giá"
                        >
                            ×
                        </button>
                    </div>

                    <form
                        action="{{ route(
                            'account.reviews.store'
                        ) }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="order-review-form"
                        data-review-form
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="order_item_id"
                            value="{{ $item->id }}"
                        >

                        <fieldset class="order-review-rating">
                            <legend>
                                Mức độ hài lòng
                                <span>*</span>
                            </legend>

                            <div class="order-review-rating__stars">
                                @for ($rating = 5; $rating >= 1; $rating--)
                                    <input
                                        id="rating-{{ $item->id }}-{{ $rating }}"
                                        type="radio"
                                        name="rating"
                                        value="{{ $rating }}"
                                        @checked(
                                            $isCurrentReviewForm
                                            && (int) old('rating') === $rating
                                        )
                                        required
                                    >

                                    <label
                                        for="rating-{{ $item->id }}-{{ $rating }}"
                                        title="{{ $rating }} sao"
                                    >
                                        ★
                                    </label>
                                @endfor
                            </div>

                            <p
                                class="order-review-rating__text"
                                data-rating-text
                            >
                                @if (
                                    $isCurrentReviewForm
                                    && old('rating')
                                )
                                    Đã chọn {{ old('rating') }} sao
                                @else
                                    Chưa chọn số sao
                                @endif
                            </p>

                            @if (
                                $isCurrentReviewForm
                                && $errors->has('rating')
                            )
                                <small class="order-review-form__error">
                                    {{ $errors->first('rating') }}
                                </small>
                            @endif
                        </fieldset>

                        <div class="order-review-form__group">
                            <label
                                for="review-content-{{ $item->id }}"
                            >
                                Nội dung đánh giá
                            </label>

                            <textarea
                                id="review-content-{{ $item->id }}"
                                name="content"
                                rows="5"
                                maxlength="3000"
                                placeholder="Sản phẩm có phù hợp với bạn không? Chất lượng, mùi hương và trải nghiệm sử dụng như thế nào?"
                            >{{ $isCurrentReviewForm
                                ? old('content')
                                : '' }}</textarea>

                            <div class="order-review-form__help">
                                Tối đa 3000 ký tự
                            </div>

                            @if (
                                $isCurrentReviewForm
                                && $errors->has('content')
                            )
                                <small class="order-review-form__error">
                                    {{ $errors->first('content') }}
                                </small>
                            @endif
                        </div>

                        <div class="order-review-upload-grid">
                            <div class="order-review-form__group">
                                <label
                                    for="review-images-{{ $item->id }}"
                                >
                                    Hình ảnh
                                </label>

                                <label
                                    class="order-review-upload"
                                    for="review-images-{{ $item->id }}"
                                >
                                    <strong>Chọn hình ảnh</strong>

                                    <span>
                                        Tối đa 5 ảnh, mỗi ảnh không quá 5MB
                                    </span>
                                </label>

                                <input
                                    id="review-images-{{ $item->id }}"
                                    type="file"
                                    name="images[]"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                    multiple
                                    class="order-review-file-input"
                                    data-review-images
                                >

                                <div
                                    class="order-review-preview"
                                    data-image-preview
                                ></div>

                                @if (
                                    $isCurrentReviewForm
                                    && (
                                        $errors->has('images')
                                        || $errors->has('images.*')
                                    )
                                )
                                    <small class="order-review-form__error">
                                        {{ $errors->first('images')
                                            ?: $errors->first('images.*') }}
                                    </small>
                                @endif
                            </div>

                            <div class="order-review-form__group">
                                <label
                                    for="review-videos-{{ $item->id }}"
                                >
                                    Video
                                </label>

                                <label
                                    class="order-review-upload"
                                    for="review-videos-{{ $item->id }}"
                                >
                                    <strong>Chọn video</strong>

                                    <span>
                                        Tối đa 2 video, mỗi video không quá 50MB
                                    </span>
                                </label>

                                <input
                                    id="review-videos-{{ $item->id }}"
                                    type="file"
                                    name="videos[]"
                                    accept=".mp4,.mov,.avi,.webm,video/mp4,video/quicktime,video/webm"
                                    multiple
                                    class="order-review-file-input"
                                    data-review-videos
                                >

                                <div
                                    class="order-review-preview"
                                    data-video-preview
                                ></div>

                                @if (
                                    $isCurrentReviewForm
                                    && (
                                        $errors->has('videos')
                                        || $errors->has('videos.*')
                                    )
                                )
                                    <small class="order-review-form__error">
                                        {{ $errors->first('videos')
                                            ?: $errors->first('videos.*') }}
                                    </small>
                                @endif
                            </div>
                        </div>

                        @if (
                            $isCurrentReviewForm
                            && $errors->has('order_item_id')
                        )
                            <small class="order-review-form__error">
                                {{ $errors->first('order_item_id') }}
                            </small>
                        @endif

                        <div class="order-review-form__actions">
                            <button
                                type="button"
                                class="order-review-form__cancel"
                                data-review-close="{{ $item->id }}"
                            >
                                Đóng
                            </button>

                            <button
                                type="submit"
                                class="order-review-form__submit"
                                data-review-submit
                            >
                                Gửi đánh giá
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </article>
    @endforeach
</section>

            {{-- Yêu cầu trả hàng / đổi hàng / hoàn tiền --}}
            <section class="order-detail-card">
                <div class="order-detail-products__heading">
                    <div>
                        <p class="home-section__eyebrow">
                            Hỗ trợ sau mua
                        </p>

                        <h2>Trả hàng, đổi hàng và hoàn tiền</h2>

                        <p>
                            Theo dõi các yêu cầu đã gửi hoặc tạo yêu cầu mới
                            cho đơn hàng này.
                        </p>
                    </div>

                    @if (! empty($activeReturnRequest))
                        <a
                            href="{{ route(
                                'account.return-requests.show',
                                $activeReturnRequest->return_code
                            ) }}"
                            class="order-review-toggle"
                        >
                            Xem yêu cầu đang xử lý
                        </a>
                    @elseif (! empty($canCreateReturnRequest))
                        <a
                            href="{{ route(
                                'account.return-requests.create',
                                $order->order_code
                            ) }}"
                            class="order-review-toggle"
                        >
                            Yêu cầu trả hàng / hoàn tiền
                        </a>
                    @endif
                </div>

                @if (! empty($returnRequests) && $returnRequests->isNotEmpty())
                    <div class="order-return-request-list">
                        @foreach ($returnRequests as $returnRequest)
                            @php
                                $returnStatusLabel =
                                    $returnRequestStatuses[
                                        $returnRequest->status
                                    ] ?? $returnRequest->status;

                                $returnTypeLabel =
                                    $returnRequestTypes[
                                        $returnRequest->request_type
                                    ] ?? $returnRequest->request_type;
                            @endphp

                            <article class="order-detail-product">
                                <div class="order-detail-product__information">
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
                                        <strong>{{ $returnTypeLabel }}</strong>
                                    </p>

                                    <p>
                                        Trạng thái:
                                        <strong>{{ $returnStatusLabel }}</strong>
                                    </p>

                                    <p>
                                        Tạo lúc
                                        {{ \Carbon\Carbon::parse(
                                            $returnRequest->created_at
                                        )->format('d/m/Y H:i') }}
                                    </p>
                                </div>

                                <div class="order-detail-product__price">
                                    <strong>
                                        {{ number_format(
                                            (float) $returnRequest->requested_amount,
                                            0,
                                            ',',
                                            '.'
                                        ) }}đ
                                    </strong>

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
                        @endforeach
                    </div>
                @elseif (! empty($canCreateReturnRequest))
                    <div class="order-message">
                        Đơn hàng đã hoàn thành. Bạn có thể gửi yêu cầu trả hàng,
                        đổi sản phẩm hoặc hoàn tiền nếu cần hỗ trợ.
                    </div>
                @else
                    <div class="order-message">
                        Chức năng này chỉ áp dụng cho đơn hàng đã giao thành công
                        và hoàn tất.
                    </div>
                @endif
            </section>

            <div class="order-detail-page__bottom">
                <section class="order-timeline">
                    <h2>Lịch sử đơn hàng</h2>

                    @foreach ($histories as $history)
                        <article class="order-timeline__item">
                            <span class="order-timeline__dot"></span>

                            <div>
                                <strong>
                                    {{ $statusLabels[
                                        $history->to_status
                                    ] ?? $history->to_status }}
                                </strong>

                                @if ($history->note)
                                    <p>{{ $history->note }}</p>
                                @endif

                                <time>
                                    {{ \Carbon\Carbon::parse(
                                        $history->occurred_at
                                            ?? $history->created_at
                                    )->format('d/m/Y H:i') }}
                                </time>
                            </div>
                        </article>
                    @endforeach
                </section>

                <aside class="order-detail-summary">
                    <h2>Tóm tắt thanh toán</h2>

                    <dl>
                        <div>
                            <dt>Tạm tính</dt>
                            <dd>
                                {{ number_format(
                                    $order->subtotal,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </dd>
                        </div>

                        <div>
                            <dt>Giảm sản phẩm</dt>
                            <dd>
                                -{{ number_format(
                                    $order->product_discount,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </dd>
                        </div>

                        <div>
                            <dt>Giảm voucher</dt>
                            <dd>
                                -{{ number_format(
                                    $order->coupon_discount,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </dd>
                        </div>

                        <div>
                            <dt>Phí vận chuyển</dt>
                            <dd>
                                {{ number_format(
                                    $order->shipping_fee,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </dd>
                        </div>

                        <div class="order-detail-summary__total">
                            <dt>Tổng thanh toán</dt>
                            <dd>
                                {{ number_format(
                                    $order->total_amount,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </dd>
                        </div>
                    </dl>

                   @if ($order->order_status === 'pending')
    <form
        action="{{ route(
            'account.orders.cancel',
            $order->order_code
        ) }}"
        method="POST"
        class="order-cancel-form"
        onsubmit="return confirm(
            'Bạn có chắc chắn muốn hủy đơn hàng này?'
        );"
    >
        @csrf
        @method('PATCH')

        <label for="cancel-reason">
            Lý do hủy đơn
        </label>

        <textarea
            id="cancel-reason"
            name="cancel_reason"
            rows="3"
            maxlength="500"
            placeholder="Ví dụ: Tôi muốn thay đổi sản phẩm..."
            required
        >{{ old('cancel_reason') }}</textarea>

        @error('cancel_reason')
            <small class="order-cancel-form__error">
                {{ $message }}
            </small>
        @enderror

        <button
            type="submit"
            class="order-detail-summary__cancel"
        >
            Hủy đơn hàng
        </button>
    </form>
@endif
                </aside>
            </div>
        </div>
    </section>
@endsection


@push('styles')
    <style>
        .order-detail-products,
        .order-detail-card,
        .order-timeline,
        .order-detail-summary {
            min-width: 0;
        }

        .order-detail-product {
            display: grid;
            grid-template-columns: 112px minmax(0, 1fr) auto;
            gap: 24px;
            align-items: center;
            position: relative;
        }

        .order-detail-product__image {
            width: 112px;
            height: 112px;
            min-width: 112px;
            overflow: hidden;
            border-radius: 18px;
        }

        .order-detail-product__image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .order-detail-product__information,
        .order-detail-product__price {
            min-width: 0;
        }

        .order-review-panel {
            grid-column: 1 / -1;
            width: 100%;
            max-width: none;
            margin-top: 8px;
            padding: 28px;
            border: 1px solid #f3c4d7;
            border-radius: 22px;
            background: #fff;
            box-sizing: border-box;
        }

        .order-review-panel[hidden] {
            display: none !important;
        }

        .order-review-panel__header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 24px;
        }

        .order-review-panel__header > div {
            min-width: 0;
        }

        .order-review-panel__close {
            flex: 0 0 42px;
            width: 42px;
            height: 42px;
            border-radius: 999px;
        }

        .order-review-form {
            width: 100%;
            min-width: 0;
        }

        .order-review-form__group,
        .order-review-rating {
            min-width: 0;
        }

        .order-review-form textarea,
        .order-review-form input,
        .order-review-form select {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .order-review-form textarea {
            resize: vertical;
            min-height: 150px;
        }

        .order-review-upload-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .order-review-upload {
            min-height: 140px;
            width: 100%;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .order-review-form__actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .order-review-form__cancel,
        .order-review-form__submit {
            min-width: 140px;
            width: auto;
        }

        @media (max-width: 767px) {
            .order-detail-product {
                grid-template-columns: 84px minmax(0, 1fr);
                gap: 16px;
                align-items: start;
            }

            .order-detail-product__image {
                width: 84px;
                height: 84px;
                min-width: 84px;
            }

            .order-detail-product__price {
                grid-column: 1 / -1;
                align-items: flex-start;
            }

            .order-review-panel {
                padding: 20px 16px;
                border-radius: 16px;
            }

            .order-review-upload-grid {
                grid-template-columns: 1fr;
            }

            .order-review-form__actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .order-review-form__cancel,
            .order-review-form__submit {
                width: 100%;
                min-width: 0;
            }
        }
    </style>
@endpush
