@extends('client.layouts.master')

@section(
    'title',
    'Tạo yêu cầu trả hàng / hoàn tiền - Cosmetic Shop'
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

                <a href="{{ route('account.orders.index') }}">
                    Đơn hàng của tôi
                </a>

                <span>/</span>

                <a href="{{ route(
                    'account.orders.show',
                    $order->order_code
                ) }}">
                    {{ $order->order_code }}
                </a>

                <span>/</span>

                <span>Tạo yêu cầu</span>
            </nav>

            {{-- Tiêu đề --}}
            <header class="order-detail-page__header">
                <div>
                    <p class="home-section__eyebrow">
                        Hỗ trợ sau mua
                    </p>

                    <h1>
                        Yêu cầu trả hàng / hoàn tiền
                    </h1>

                    <p>
                        Đơn hàng:
                        <strong>{{ $order->order_code }}</strong>
                    </p>
                </div>

                <a
                    href="{{ route(
                        'account.orders.show',
                        $order->order_code
                    ) }}"
                    class="order-review-toggle"
                >
                    Quay lại đơn hàng
                </a>
            </header>

            {{-- Lỗi chung --}}
            @if ($errors->any())
                <div class="order-message order-message--error">
                    <strong>
                        Vui lòng kiểm tra lại thông tin:
                    </strong>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                action="{{ route(
                    'account.return-requests.store',
                    $order->order_code
                ) }}"
                method="POST"
                enctype="multipart/form-data"
                class="return-request-form"
            >
                @csrf

                {{-- Thông tin yêu cầu --}}
                <section class="order-detail-card">
                    <h2>
                        Thông tin yêu cầu
                    </h2>

                    <div class="return-request-form__grid">

                        {{-- Loại yêu cầu --}}
                        <div class="return-request-form__group">
                            <label for="request-type">
                                Loại yêu cầu
                                <span>*</span>
                            </label>

                            <select
                                id="request-type"
                                name="request_type"
                                required
                                data-return-request-type
                            >
                                <option value="">
                                    Chọn loại yêu cầu
                                </option>

                                @foreach ($requestTypes as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(
                                            old('request_type') === $value
                                        )
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            @error('request_type')
                                <small class="order-review-form__error">
                                    {{ $message }}
                                </small>
                            @enderror
                        </div>

                        {{-- Lý do --}}
                        <div class="return-request-form__group">
                            <label for="return-reason">
                                Lý do
                                <span>*</span>
                            </label>

                            <input
                                id="return-reason"
                                type="text"
                                name="reason"
                                value="{{ old('reason') }}"
                                maxlength="255"
                                placeholder="Ví dụ: Sản phẩm bị lỗi, giao sai sản phẩm..."
                                required
                            >

                            @error('reason')
                                <small class="order-review-form__error">
                                    {{ $message }}
                                </small>
                            @enderror
                        </div>

                    </div>

                    {{-- Mô tả --}}
                    <div class="return-request-form__group">
                        <label for="return-description">
                            Mô tả chi tiết
                        </label>

                        <textarea
                            id="return-description"
                            name="description"
                            rows="5"
                            maxlength="3000"
                            placeholder="Mô tả rõ tình trạng sản phẩm và mong muốn của bạn..."
                        >{{ old('description') }}</textarea>

                        @error('description')
                            <small class="order-review-form__error">
                                {{ $message }}
                            </small>
                        @enderror
                    </div>

                    {{-- Ghi chú --}}
                    <div class="return-request-form__group">
                        <label for="customer-note">
                            Ghi chú thêm
                        </label>

                        <textarea
                            id="customer-note"
                            name="customer_note"
                            rows="3"
                            maxlength="2000"
                            placeholder="Thông tin bổ sung dành cho cửa hàng..."
                        >{{ old('customer_note') }}</textarea>

                        @error('customer_note')
                            <small class="order-review-form__error">
                                {{ $message }}
                            </small>
                        @enderror
                    </div>
                </section>

                {{-- Sản phẩm --}}
                <section class="order-detail-products">
                    <div class="order-detail-products__heading">
                        <div>
                            <p class="home-section__eyebrow">
                                Sản phẩm
                            </p>

                            <h2>
                                Chọn sản phẩm cần xử lý
                            </h2>

                            <p>
                                Chọn ít nhất một sản phẩm và nhập số lượng.
                            </p>
                        </div>
                    </div>

                    @foreach ($items as $item)
                        @php
                            $oldSelected = old(
                                "items.{$item->id}.selected"
                            );

                            $oldQuantity = old(
                                "items.{$item->id}.quantity",
                                1
                            );

                            $oldCondition = old(
                                "items.{$item->id}.product_condition"
                            );
                        @endphp

                        <article
                            class="order-detail-product return-request-item"
                            data-return-item
                        >
                            {{-- Checkbox --}}
                            <div class="return-request-item__check">
                                <input
                                    id="return-item-{{ $item->id }}"
                                    type="checkbox"
                                    name="items[{{ $item->id }}][selected]"
                                    value="1"
                                    @checked($oldSelected)
                                    data-return-item-checkbox
                                >

                                <label
                                    for="return-item-{{ $item->id }}"
                                >
                                    Chọn sản phẩm
                                </label>
                            </div>

                            {{-- Ảnh --}}
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

                            {{-- Thông tin --}}
                            <div class="order-detail-product__information">
                                <h3>
                                    {{ $item->product_name }}
                                </h3>

                                @if ($item->sku_code)
                                    <p>
                                        SKU:
                                        {{ $item->sku_code }}
                                    </p>
                                @endif

                                @if ($item->variant_name)
                                    <p>
                                        Phân loại:
                                        {{ $item->variant_name }}
                                    </p>
                                @endif

                                <p>
                                    Đã mua:
                                    {{ $item->quantity }}
                                </p>

                                <p>
                                    Có thể yêu cầu:
                                    <strong>
                                        {{ $item->available_request_quantity }}
                                    </strong>
                                </p>

                                <p>
                                    Thành tiền:
                                    {{ number_format(
                                        (float) $item->total_price,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ
                                </p>
                            </div>

                            {{-- Form item --}}
                            <div
                                class="return-request-item__fields"
                                data-return-item-fields
                                @if (! $oldSelected)
                                    hidden
                                @endif
                            >
                                <div class="return-request-form__group">
                                    <label
                                        for="return-quantity-{{ $item->id }}"
                                    >
                                        Số lượng
                                        <span>*</span>
                                    </label>

                                    <input
                                        id="return-quantity-{{ $item->id }}"
                                        type="number"
                                        name="items[{{ $item->id }}][quantity]"
                                        value="{{ $oldQuantity }}"
                                        min="1"
                                        max="{{ $item->available_request_quantity }}"
                                    >

                                    @error("items.{$item->id}.quantity")
                                        <small class="order-review-form__error">
                                            {{ $message }}
                                        </small>
                                    @enderror
                                </div>

                                <div class="return-request-form__group">
                                    <label
                                        for="return-condition-{{ $item->id }}"
                                    >
                                        Tình trạng sản phẩm
                                    </label>

                                    <select
                                        id="return-condition-{{ $item->id }}"
                                        name="items[{{ $item->id }}][product_condition]"
                                    >
                                        <option value="">
                                            Chọn tình trạng
                                        </option>

                                        @foreach (
                                            $productConditions
                                            as $value => $label
                                        )
                                            <option
                                                value="{{ $value }}"
                                                @selected(
                                                    $oldCondition === $value
                                                )
                                            >
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error(
                                        "items.{$item->id}.product_condition"
                                    )
                                        <small class="order-review-form__error">
                                            {{ $message }}
                                        </small>
                                    @enderror
                                </div>

                                <div class="return-request-form__group">
                                    <label
                                        for="item-reason-{{ $item->id }}"
                                    >
                                        Lý do riêng
                                    </label>

                                    <input
                                        id="item-reason-{{ $item->id }}"
                                        type="text"
                                        name="items[{{ $item->id }}][reason]"
                                        value="{{ old(
                                            "items.{$item->id}.reason"
                                        ) }}"
                                        maxlength="255"
                                        placeholder="Lý do riêng cho sản phẩm này..."
                                    >
                                </div>

                                <div class="return-request-form__group">
                                    <label
                                        for="item-description-{{ $item->id }}"
                                    >
                                        Mô tả sản phẩm
                                    </label>

                                    <textarea
                                        id="item-description-{{ $item->id }}"
                                        name="items[{{ $item->id }}][description]"
                                        rows="3"
                                        maxlength="2000"
                                        placeholder="Mô tả tình trạng cụ thể..."
                                    >{{ old(
                                        "items.{$item->id}.description"
                                    ) }}</textarea>
                                </div>
                            </div>
                        </article>
                    @endforeach

                    @error('items')
                        <div class="order-message order-message--error">
                            {{ $message }}
                        </div>
                    @enderror
                </section>

                {{-- Ảnh bằng chứng --}}
                <section class="order-detail-card">
                    <h2>
                        Ảnh bằng chứng
                    </h2>

                    <p>
                        Có thể tải tối đa 6 ảnh, mỗi ảnh không quá 5MB.
                    </p>

                    <div class="return-request-form__group">
                        <label
                            class="order-review-upload"
                            for="return-images"
                        >
                            <strong>
                                Chọn ảnh sản phẩm
                            </strong>

                            <span>
                                JPG, JPEG, PNG hoặc WEBP
                            </span>
                        </label>

                        <input
                            id="return-images"
                            type="file"
                            name="images[]"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            multiple
                            class="order-review-file-input"
                            data-return-images
                        >

                        <div
                            class="order-review-preview"
                            data-return-image-preview
                        ></div>

                        @if (
                            $errors->has('images')
                            || $errors->has('images.*')
                        )
                            <small class="order-review-form__error">
                                {{ $errors->first('images')
                                    ?: $errors->first('images.*') }}
                            </small>
                        @endif
                    </div>
                </section>

                {{-- Hướng dẫn --}}
                <section class="order-detail-card">
                    <h2>
                        Lưu ý trước khi gửi yêu cầu
                    </h2>

                    <ul class="return-request-notes">
                        <li>
                            Cung cấp thông tin và hình ảnh rõ ràng để cửa hàng
                            có thể xử lý nhanh hơn.
                        </li>

                        <li>
                            Không gửi sản phẩm về cửa hàng trước khi yêu cầu
                            được chấp thuận.
                        </li>

                        <li>
                            Số tiền hoàn thực tế sẽ do cửa hàng kiểm tra và
                            phê duyệt.
                        </li>

                        <li>
                            Bạn có thể theo dõi trạng thái yêu cầu trong
                            tài khoản.
                        </li>
                    </ul>
                </section>

                {{-- Actions --}}
                <div class="return-request-form__actions">
                    <a
                        href="{{ route(
                            'account.orders.show',
                            $order->order_code
                        ) }}"
                        class="order-review-form__cancel"
                    >
                        Hủy
                    </a>

                    <button
                        type="submit"
                        class="order-review-form__submit"
                        data-return-submit
                    >
                        Gửi yêu cầu
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection


@push('styles')
    <style>
        .return-request-form,
        .return-request-form * {
            box-sizing: border-box;
        }

        .return-request-form {
            width: 100%;
            min-width: 0;
        }

        .return-request-form__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .return-request-form__group {
            min-width: 0;
            margin-bottom: 20px;
        }

        .return-request-form__group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .return-request-form__group input,
        .return-request-form__group select,
        .return-request-form__group textarea {
            display: block;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            border: 1px solid #d8dde7;
            border-radius: 12px;
            padding: 12px 14px;
            background: #fff;
            line-height: 1.5;
        }

        .return-request-form__group textarea {
            resize: vertical;
        }

        .return-request-item {
            display: grid;
            grid-template-columns: 150px 112px minmax(0, 1fr);
            gap: 22px;
            align-items: center;
            position: relative;
        }

        .return-request-item__check {
            display: flex;
            align-items: center;
            gap: 10px;
            align-self: start;
            padding-top: 12px;
        }

        .return-request-item__check input {
            flex: 0 0 auto;
        }

        .return-request-item__fields {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            width: 100%;
            padding-top: 22px;
            border-top: 1px solid #edf0f5;
        }

        .return-request-item__fields[hidden] {
            display: none !important;
        }

        .return-request-item .order-detail-product__image {
            width: 112px;
            height: 112px;
            min-width: 112px;
            border-radius: 18px;
            overflow: hidden;
        }

        .return-request-item .order-detail-product__image img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .return-request-form__actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .return-request-form__actions > * {
            width: auto;
            min-width: 150px;
        }

        @media (max-width: 767px) {
            .return-request-form__grid {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .return-request-item {
                grid-template-columns: 84px minmax(0, 1fr);
                gap: 14px;
                align-items: start;
            }

            .return-request-item__check {
                grid-column: 1 / -1;
                padding-top: 0;
            }

            .return-request-item .order-detail-product__image {
                width: 84px;
                height: 84px;
                min-width: 84px;
            }

            .return-request-item__fields {
                grid-template-columns: 1fr;
            }

            .return-request-form__actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .return-request-form__actions > * {
                width: 100%;
                min-width: 0;
            }
        }
    </style>
@endpush


@push('scripts')
    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                /*
                |--------------------------------------------------------------------------
                | Bật/tắt thông tin từng sản phẩm
                |--------------------------------------------------------------------------
                */

                document
                    .querySelectorAll('[data-return-item]')
                    .forEach(function (item) {
                        const checkbox = item.querySelector(
                            '[data-return-item-checkbox]'
                        );

                        const fields = item.querySelector(
                            '[data-return-item-fields]'
                        );

                        if (! checkbox || ! fields) {
                            return;
                        }

                        function renderItem() {
                            fields.hidden = ! checkbox.checked;
                        }

                        renderItem();

                        checkbox.addEventListener(
                            'change',
                            renderItem
                        );
                    });

                /*
                |--------------------------------------------------------------------------
                | Preview ảnh
                |--------------------------------------------------------------------------
                */

                const imageInput = document.querySelector(
                    '[data-return-images]'
                );

                const imagePreview = document.querySelector(
                    '[data-return-image-preview]'
                );

                if (imageInput && imagePreview) {
                    imageInput.addEventListener(
                        'change',
                        function () {
                            imagePreview.innerHTML = '';

                            const files = Array.from(
                                imageInput.files || []
                            ).slice(0, 6);

                            files.forEach(function (file) {
                                const reader = new FileReader();

                                reader.addEventListener(
                                    'load',
                                    function () {
                                        const wrapper =
                                            document.createElement(
                                                'div'
                                            );

                                        wrapper.className =
                                            'order-review-preview__item';

                                        const image =
                                            document.createElement(
                                                'img'
                                            );

                                        image.src = reader.result;
                                        image.alt =
                                            'Ảnh bằng chứng';

                                        wrapper.appendChild(image);
                                        imagePreview.appendChild(
                                            wrapper
                                        );
                                    }
                                );

                                reader.readAsDataURL(file);
                            });
                        }
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Chặn submit nếu chưa chọn sản phẩm
                |--------------------------------------------------------------------------
                */

                const form = document.querySelector(
                    '.return-request-form'
                );

                if (form) {
                    form.addEventListener(
                        'submit',
                        function (event) {
                            const selectedItems =
                                form.querySelectorAll(
                                    '[data-return-item-checkbox]:checked'
                                );

                            if (selectedItems.length === 0) {
                                event.preventDefault();

                                alert(
                                    'Vui lòng chọn ít nhất một sản phẩm.'
                                );
                            }
                        }
                    );
                }
            }
        );
    </script>
@endpush
