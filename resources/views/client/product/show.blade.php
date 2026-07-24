@extends('client.layouts.master')

@section(
    'title',
    $product->name . ' - Cosmetic Shop'
)

@section(
    'meta_description',
    $product->short_description
        ?: \Illuminate\Support\Str::limit(
            strip_tags($product->description ?? ''),
            160
        )
)

@php
    $mainImage = $images->first();

    $mainImageUrl = $mainImage
        ? asset(
            'storage/'
            . ltrim($mainImage->image_path, '/')
        )
        : null;

    $youtubeEmbedUrl = static function (?string $url): ?string {
        if (! $url) {
            return null;
        }

        $videoId = null;
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if (str_contains($host, 'youtu.be')) {
            $videoId = explode('/', $path)[0] ?? null;
        } elseif (str_contains($host, 'youtube.com')) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

            if (! empty($query['v'])) {
                $videoId = $query['v'];
            } elseif (str_starts_with($path, 'shorts/')) {
                $videoId = explode('/', $path)[1] ?? null;
            } elseif (str_starts_with($path, 'embed/')) {
                $videoId = explode('/', $path)[1] ?? null;
            }
        }

        if (! $videoId) {
            return null;
        }

        return 'https://www.youtube.com/embed/'
            . rawurlencode($videoId)
            . '?autoplay=1&mute=1&rel=0&playsinline=1';
    };

    $preparedVideos = collect($videos ?? [])->map(function ($video) use ($youtubeEmbedUrl) {
        $rawUrl = (string) $video->video_url;
        $isExternal = str_starts_with($rawUrl, 'http://')
            || str_starts_with($rawUrl, 'https://');
        $embedUrl = $isExternal
            ? $youtubeEmbedUrl($rawUrl)
            : null;

        return (object) [
            'id' => $video->id,
            'title' => $video->title ?: 'Video sản phẩm',
            'type' => $embedUrl
                ? 'youtube'
                : ($isExternal ? 'external' : 'video'),
            'source' => $embedUrl
                ?: ($isExternal
                    ? $rawUrl
                    : asset('storage/' . ltrim($rawUrl, '/'))),
        ];
    })->values();

    $defaultPrice = (float) $defaultSku->price;

    $salePrice = $defaultPrice;

    if ($discount?->discount_percent !== null) {
        $salePrice = $defaultPrice
            * (
                1
                - ((float) $discount->discount_percent / 100)
            );
    } elseif ($discount?->discount_amount !== null) {
        $salePrice = $defaultPrice
            - (float) $discount->discount_amount;
    }

    $salePrice = max(0, round($salePrice));

    $hasDiscount = $salePrice < $defaultPrice;
@endphp

@section('content')
    <section class="product-detail">
        <div class="client-container">
            <nav
                class="product-detail__breadcrumb"
                aria-label="Breadcrumb"
            >
                <a href="{{ route('home') }}">
                    Trang chủ
                </a>

                <span>/</span>

                <a href="{{ route('products.index') }}">
                    Sản phẩm
                </a>

                @if ($product->category_name)
                    <span>/</span>

                    <a
                        href="{{ route(
                            'products.index',
                            ['category' => $product->category_slug]
                        ) }}"
                    >
                        {{ $product->category_name }}
                    </a>
                @endif

                <span>/</span>

                <span>{{ $product->name }}</span>
            </nav>

            <div class="product-detail__main">
                <div
                    class="product-gallery"
                    data-product-gallery
                >
                    <div class="product-gallery__main">
                        <div
                            class="product-gallery__media"
                            data-product-gallery-image-panel
                            @if ($preparedVideos->isNotEmpty()) hidden @endif
                        >
                            @if ($mainImageUrl)
                                <img
                                    src="{{ $mainImageUrl }}"
                                    alt="{{ $product->name }}"
                                    data-product-main-image
                                >
                            @endif
                        </div>

                        <div
                            class="product-gallery__media"
                            data-product-gallery-video-panel
                            hidden
                        >
                            <video
                                controls
                                muted
                                playsinline
                                preload="metadata"
                                data-product-main-video
                            ></video>
                        </div>

                        <div
                            class="product-gallery__media"
                            data-product-gallery-youtube-panel
                            hidden
                        >
                            <iframe
                                title="Video sản phẩm"
                                allow="autoplay; encrypted-media; picture-in-picture"
                                allowfullscreen
                                referrerpolicy="strict-origin-when-cross-origin"
                                data-product-youtube-frame
                            ></iframe>
                        </div>

                        <div
                            class="product-gallery__external"
                            data-product-gallery-external-panel
                            hidden
                        >
                            <span class="product-gallery__external-icon">
                                ▶
                            </span>

                            <strong data-product-external-title>
                                Video sản phẩm
                            </strong>

                            <p>
                                Video này được lưu từ nguồn bên ngoài.
                            </p>

                            <a
                                href="{{ route('products.show', $product->slug) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                data-product-external-link
                            >
                                Mở video trong tab mới
                            </a>
                        </div>

                        <div
                            class="product-gallery__placeholder"
                            data-product-gallery-placeholder
                            @if ($preparedVideos->isNotEmpty() || $mainImageUrl) hidden @endif
                        >
                            Cosmetic Shop
                        </div>
                    </div>

                    @if ($preparedVideos->isNotEmpty() || $images->isNotEmpty())
                        <div class="product-gallery__thumbnails">
                            {{-- Video luôn được đặt trước hình ảnh --}}
                            @foreach ($preparedVideos as $video)
                                <button
                                    type="button"
                                    class="product-gallery__thumbnail product-gallery__thumbnail--video
                                        @if ($loop->first)
                                            product-gallery__thumbnail--active
                                        @endif"
                                    data-product-media-thumbnail
                                    data-media-type="{{ $video->type }}"
                                    data-media-url="{{ $video->source }}"
                                    data-media-title="{{ $video->title }}"
                                    aria-label="{{ $video->title }}"
                                    aria-current="{{ $loop->first ? 'true' : 'false' }}"
                                >
                                    <span class="product-gallery__video-icon">
                                        ▶
                                    </span>

                                    <span class="product-gallery__video-label">
                                        Video
                                    </span>
                                </button>
                            @endforeach

                            @foreach ($images as $image)
                                @php
                                    $imageUrl = asset(
                                        'storage/'
                                        . ltrim($image->image_path, '/')
                                    );
                                @endphp

                                <button
                                    type="button"
                                    class="product-gallery__thumbnail
                                        @if ($preparedVideos->isEmpty() && $loop->first)
                                            product-gallery__thumbnail--active
                                        @endif"
                                    data-product-media-thumbnail
                                    data-media-type="image"
                                    data-media-url="{{ $imageUrl }}"
                                    data-media-title="{{ $product->name }}"
                                    aria-label="Xem hình ảnh {{ $loop->iteration }}"
                                    aria-current="{{ $preparedVideos->isEmpty() && $loop->first ? 'true' : 'false' }}"
                                >
                                    <img
                                        src="{{ $imageUrl }}"
                                        alt="{{ $product->name }}"
                                    >
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="product-detail__info">
                    @if ($product->brand_name)
                        <a
                            href="{{ route(
                                'products.index',
                                ['brand' => $product->brand_slug]
                            ) }}"
                            class="product-detail__brand"
                        >
                            {{ $product->brand_name }}
                        </a>
                    @endif

                    <h1>{{ $product->name }}</h1>

                    @if ($product->short_description)
                        <p class="product-detail__summary">
                            {{ $product->short_description }}
                        </p>
                    @endif

                    <div class="product-detail__meta">
                        <div class="product-detail__rating">
                            <span>★★★★★</span>

                            <strong>
                                {{ number_format(
                                    (float) $reviewSummary->average_rating,
                                    1
                                ) }}
                            </strong>

                            <small>
                                ({{ $reviewSummary->review_count }}
                                đánh giá)
                            </small>
                        </div>

                        <span>
                            Đã xem
                            {{ number_format(
                                $product->view_count + 1
                            ) }}
                        </span>
                    </div>

                    @if ($discount)
                        <div class="product-detail__promotion">
                            <strong>
                                {{ $discount->is_flash_sale
                                    ? '⚡ Flash Sale'
                                    : 'Khuyến mãi' }}
                            </strong>

                            <span>
                                {{ $discount->campaign_name }}
                            </span>
                        </div>
                    @endif

                    <div class="product-detail__prices">
                        <strong data-product-sale-price>
                            {{ number_format(
                                $salePrice,
                                0,
                                ',',
                                '.'
                            ) }}đ
                        </strong>

                        <del
                            data-product-original-price
                            @if (!$hasDiscount) hidden @endif
                        >
                            {{ number_format(
                                $defaultPrice,
                                0,
                                ',',
                                '.'
                            ) }}đ
                        </del>
                    </div>

                    <div class="product-detail__sku">
                        <span>Mã SKU:</span>

                        <strong data-product-sku-code>
                            {{ $defaultSku->sku_code }}
                        </strong>
                    </div>

                    @if ($skus->count() > 1)
                        <div class="product-detail__variants">
                            <span class="product-detail__label">
                                Lựa chọn sản phẩm
                            </span>

                            <div class="product-detail__variant-list">
                                @foreach ($skus as $sku)
                                    @php
                                        $skuLabel = $sku->variant_name
                                            ?: $sku->attributes
                                                ->map(
                                                    fn ($item) =>
                                                        $item->attribute_value
                                                )
                                                ->implode(' - ');

                                        $skuLabel = $skuLabel
                                            ?: $sku->sku_code;
                                    @endphp

                                    <button
                                        type="button"
                                        class="product-detail__variant
                                            @if ($sku->id === $defaultSku->id)
                                                product-detail__variant--active
                                            @endif"
                                        data-product-sku
                                        data-sku-id="{{ $sku->id }}"
                                        data-sku-code="{{ $sku->sku_code }}"
                                        data-price="{{ (float) $sku->price }}"
                                        data-stock="{{ (int) $sku->available_quantity }}"
                                        @disabled(
                                            (int) $sku->available_quantity <= 0
                                        )
                                    >
                                        {{ $skuLabel }}

                                        @if (
                                            (int) $sku->available_quantity <= 0
                                        )
                                            <small>Hết hàng</small>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="product-detail__stock">
                        <span
                            class="{{ (int) $defaultSku->available_quantity > 0
                                ? 'product-detail__stock--available'
                                : 'product-detail__stock--empty' }}"
                            data-product-stock
                        >
                            @if (
                                (int) $defaultSku->available_quantity > 0
                            )
                                Còn hàng:
                                {{ $defaultSku->available_quantity }}
                                sản phẩm
                            @else
                                Sản phẩm hiện đang hết hàng
                            @endif
                        </span>
                    </div>

                    <div class="product-detail__purchase">
                        <div class="product-quantity">
                            <button
                                type="button"
                                data-quantity-minus
                                aria-label="Giảm số lượng"
                            >
                                −
                            </button>

                            <input
                                type="number"
                                value="1"
                                min="1"
                                max="{{ max(
                                    1,
                                    (int) $defaultSku->available_quantity
                                ) }}"
                                data-product-quantity
                            >

                            <button
                                type="button"
                                data-quantity-plus
                                aria-label="Tăng số lượng"
                            >
                                +
                            </button>
                        </div>

                        <div class="product-detail__purchase-buttons">
                            <button
                                type="button"
                                class="product-detail__cart"
                                data-detail-add-to-cart
                                data-product-id="{{ $product->id }}"
                                data-sku-id="{{ $defaultSku->id }}"
                                @disabled((int) $defaultSku->available_quantity <= 0)
                            >
                                Thêm vào giỏ hàng
                            </button>

                            <button
                                type="button"
                                class="product-detail__buy-now"
                                data-buy-now
                                data-product-id="{{ $product->id }}"
                                data-sku-id="{{ $defaultSku->id }}"
                                data-buy-now-detail
                                @disabled((int) $defaultSku->available_quantity <= 0)
                            >
                                Mua ngay
                            </button>
                        </div>

                        <div class="product-detail-favorite">
    @auth
        <button
            type="button"
            class="product-favorite-button {{ $isFavorite
                ? 'is-favorite'
                : '' }}"
            data-favorite-toggle
            data-favorite-url="{{ route(
    'account.favorites.toggle',
    ['product' => $product->id]
) }}"
            aria-pressed="{{ $isFavorite
                ? 'true'
                : 'false' }}"
            title="{{ $isFavorite
                ? 'Bỏ khỏi danh sách yêu thích'
                : 'Thêm vào danh sách yêu thích' }}"
        >
            <span
                class="product-favorite-button__icon"
                aria-hidden="true"
            >
                {{ $isFavorite ? '♥' : '♡' }}
            </span>

            <span data-favorite-label>
                {{ $isFavorite
                    ? 'Đã yêu thích'
                    : 'Yêu thích' }}
            </span>
        </button>
    @else
        <a
            href="{{ route('login') }}"
            class="product-favorite-button"
            title="Đăng nhập để thêm sản phẩm yêu thích"
        >
            <span
                class="product-favorite-button__icon"
                aria-hidden="true"
            >
                ♡
            </span>

            <span>Yêu thích</span>
        </a>
    @endauth

    <span
        class="product-favorite-message"
        data-favorite-message
        aria-live="polite"
    ></span>
</div>
                    </div>

                    <div class="product-detail__benefits">
                        <div>
                            <strong>✓ Chính hãng</strong>
                            <span>Cam kết nguồn gốc rõ ràng</span>
                        </div>

                        <div>
                            <strong>↻ Đổi trả</strong>
                            <span>Hỗ trợ đổi trả theo chính sách</span>
                        </div>

                        <div>
                            <strong>🚚 Giao hàng</strong>
                            <span>Miễn phí từ 500.000đ</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="product-detail__tabs">
                <section>
                    <h2>Mô tả sản phẩm</h2>

                    <div class="product-detail__content">
                        {!! nl2br(e(
                            $product->description
                                ?: 'Nội dung đang được cập nhật.'
                        )) !!}
                    </div>
                </section>

                @if ($product->ingredient)
                    <section>
                        <h2>Thành phần</h2>

                        <div class="product-detail__content">
                            {!! nl2br(e($product->ingredient)) !!}
                        </div>
                    </section>
                @endif

                @if ($product->usage)
                    <section>
                        <h2>Hướng dẫn sử dụng</h2>

                        <div class="product-detail__content">
                            {!! nl2br(e($product->usage)) !!}
                        </div>
                    </section>
                @endif

                <section>
                    <h2>Thông tin sản phẩm</h2>

                    <dl class="product-detail__specifications">
                        <div>
                            <dt>Thương hiệu</dt>
                            <dd>
                                {{ $product->brand_name ?: 'Đang cập nhật' }}
                            </dd>
                        </div>

                        <div>
                            <dt>Xuất xứ thương hiệu</dt>
                            <dd>
                                {{ $product->brand_country
                                    ?: $product->origin
                                    ?: 'Đang cập nhật' }}
                            </dd>
                        </div>

                        <div>
                            <dt>Loại da</dt>
                            <dd>
                                {{ $product->skin_type
                                    ?: 'Mọi loại da' }}
                            </dd>
                        </div>

                        <div>
                            <dt>Danh mục</dt>
                            <dd>
                                {{ $product->category_name }}
                            </dd>
                        </div>
                    </dl>
                </section>
                <section
    class="product-reviews-section"
    id="product-reviews"
>
    <div class="product-reviews-section__heading">
        <div>
            <p class="home-section__eyebrow">
                Khách hàng nhận xét
            </p>

            <h2>Đánh giá sản phẩm</h2>

            <p class="product-reviews-section__description">
                Xem điểm trung bình, phân bố từ 1 đến 5 sao
                và nhận xét thực tế từ khách hàng đã mua sản phẩm.
            </p>
        </div>


        <a
            href="#product-reviews-list"
            class="product-reviews-section__count"
        >
            {{ number_format(
                $reviewStatistics->total_reviews
            ) }}
            đánh giá
        </a>
    </div>

    <div class="product-review-summary">
        <div class="product-review-summary__average">
            <strong>
                {{ number_format(
                    $reviewStatistics->average_rating,
                    1
                ) }}
            </strong>

            <span>/ 5</span>

            <div
                class="product-review-stars"
                aria-label="{{ $reviewStatistics->average_rating }} trên 5 sao"
            >
                @for ($star = 1; $star <= 5; $star++)
                    @php
                        $filledPercentage = max(
                            0,
                            min(
                                100,
                                (
                                    $reviewStatistics->average_rating
                                    - ($star - 1)
                                ) * 100
                            )
                        );
                    @endphp

                    <span class="product-review-star">
                        <span
                            class="product-review-star__empty"
                        >
                            ★
                        </span>

                        <span
                            class="product-review-star__filled"
                            style="width: {{ $filledPercentage }}%;"
                        >
                            ★
                        </span>
                    </span>
                @endfor
            </div>

            <p>
                Dựa trên
                {{ number_format(
                    $reviewStatistics->total_reviews
                ) }}
                đánh giá
            </p>
        </div>

        <div class="product-review-summary__breakdown">
            @foreach ($ratingBreakdown as $rating => $data)
                <div class="product-review-breakdown-row">
                    <span class="product-review-breakdown-row__rating">
                        {{ $rating }}
                        <span>★</span>
                    </span>

                    <div class="product-review-breakdown-row__bar">
                        <span
                            style="width: {{ $data['percentage'] }}%;"
                        ></span>
                    </div>

                    <span class="product-review-breakdown-row__count">
                        {{ $data['count'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <div
        class="product-reviews-list"
        id="product-reviews-list"
    >
        @forelse ($reviews as $review)
            <article
                class="product-review-card"
                id="review-{{ $review->id }}"
            >
                <div class="product-review-card__customer">
                    <div class="product-review-card__avatar">
                        @if (
                            $review->user
                            && $review->user->avatar
                        )
                            <img
                                src="{{ asset(
                                    'storage/' . $review->user->avatar
                                ) }}"
                                alt="{{ $review->user->name }}"
                            >
                        @else
                            <span>
                                {{ mb_strtoupper(
                                    mb_substr(
                                        $review->user?->name
                                            ?? 'K',
                                        0,
                                        1
                                    )
                                ) }}
                            </span>
                        @endif
                    </div>

                    <div>
                        <strong>
                            {{ $review->user?->name
                                ?? 'Khách hàng' }}
                        </strong>

                        <div class="product-review-card__meta">
                            <time
                                datetime="{{ $review->created_at->toIso8601String() }}"
                            >
                                {{ $review->created_at->format(
                                    'd/m/Y H:i'
                                ) }}
                            </time>

                            @if ($review->verified_purchase)
                                <span class="product-review-verified">
                                    ✓ Đã mua hàng
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="product-review-card__body">
                    <div
                        class="product-review-card__rating"
                        aria-label="{{ $review->rating }} trên 5 sao"
                    >
                        @for ($star = 1; $star <= 5; $star++)
                            <span
                                @class([
                                    'is-active' =>
                                        $star <= $review->rating,
                                ])
                            >
                                ★
                            </span>
                        @endfor
                    </div>

                    @if ($review->content)
                        <p class="product-review-card__content">
                            {!! nl2br(
                                e($review->content)
                            ) !!}
                        </p>
                    @endif

                    @if (
                        $review->images->isNotEmpty()
                        || $review->videos->isNotEmpty()
                    )
                        <div class="product-review-media">
                            @foreach ($review->images as $image)
                                <button
                                    type="button"
                                    class="product-review-media__item"
                                    data-review-media-open
                                    data-review-media-type="image"
                                    data-review-media-src="{{ asset(
                                        'storage/' . $image->image_path
                                    ) }}"
                                    aria-label="Xem hình ảnh đánh giá"
                                >
                                    <img
                                        src="{{ asset(
                                            'storage/' . $image->image_path
                                        ) }}"
                                        alt="Hình ảnh đánh giá sản phẩm"
                                        loading="lazy"
                                    >
                                </button>
                            @endforeach

                            @foreach ($review->videos as $video)
                                <button
                                    type="button"
                                    class="product-review-media__item product-review-media__item--video"
                                    data-review-media-open
                                    data-review-media-type="video"
                                    data-review-media-src="{{ asset(
                                        'storage/' . $video->video_path
                                    ) }}"
                                    aria-label="Xem video đánh giá"
                                >
                                    <video
                                        src="{{ asset(
                                            'storage/' . $video->video_path
                                        ) }}"
                                        preload="metadata"
                                        muted
                                    ></video>

                                    <span>▶</span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="product-review-card__actions">
    @auth
        <button
            type="button"
            class="product-review-like-button {{ $review->is_liked_by_current_user
                ? 'is-liked'
                : '' }}"
            data-review-like
            data-like-url="{{ route(
                'account.reviews.like',
                $review
            ) }}"
            aria-pressed="{{ $review->is_liked_by_current_user
                ? 'true'
                : 'false' }}"
        >
            <span
                class="product-review-like-button__icon"
                aria-hidden="true"
            >
                ♡
            </span>

            <span>
                Hữu ích
            </span>

            <span
                class="product-review-like-button__count"
                data-review-like-count
            >
                {{ $review->likes_count }}
            </span>
        </button>
    @else
        <a
            href="{{ route('login') }}"
            class="product-review-like-login"
        >
            ♡ Hữu ích

            <span>
                {{ $review->likes_count }}
            </span>
        </a>
    @endauth

    <span
        class="product-review-like-message"
        data-review-like-message
        aria-live="polite"
    ></span>
</div>

                    @if ($review->replies->isNotEmpty())
                        <div class="product-review-replies">
                            @foreach ($review->replies as $reply)
                                @php
                                    $isShopReply =
                                        $reply->user
                                        && (
                                            $reply->user->roles
                                                ?->contains(
                                                    'name',
                                                    'admin'
                                                )
                                            || $reply->user->roles
                                                ?->contains(
                                                    'name',
                                                    'super_admin'
                                                )
                                        );
                                @endphp

                                <div
                                    class="product-review-reply
                                        {{ $isShopReply
                                            ? 'product-review-reply--shop'
                                            : 'product-review-reply--customer' }}"
                                >
                                    <div class="product-review-reply__heading">
                                        @if ($isShopReply)
                                            <span class="product-review-shop-badge">
                                                Chủ shop
                                            </span>
                                        @endif

                                        <strong>
                                            {{ $reply->user?->name
                                                ?? 'Khách hàng' }}
                                        </strong>

                                        @if (! $isShopReply)
                                            <span class="product-review-customer-badge">
                                                Người mua
                                            </span>
                                        @endif
                                    </div>

                                    <p>
                                        {!! nl2br(
                                            e($reply->content)
                                        ) !!}
                                    </p>

                                    <time
                                        datetime="{{ $reply->created_at->toIso8601String() }}"
                                    >
                                        {{ $reply->created_at->format(
                                            'd/m/Y H:i'
                                        ) }}
                                    </time>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @auth
                        @if (
                            (int) auth()->id()
                            === (int) $review->user_id
                        )
                            <details class="product-review-conversation">
                                <summary>
                                    Trả lời cửa hàng
                                </summary>

                                <form
                                    action="{{ route(
                                        'account.reviews.replies.store',
                                        $review
                                    ) }}"
                                    method="POST"
                                    class="product-review-reply-form"
                                >
                                    @csrf

                                    <label
                                        for="review-reply-{{ $review->id }}"
                                    >
                                        Nội dung phản hồi
                                    </label>

                                    <textarea
                                        id="review-reply-{{ $review->id }}"
                                        name="content"
                                        rows="3"
                                        maxlength="3000"
                                        placeholder="Nhập nội dung bạn muốn trao đổi thêm với cửa hàng..."
                                        required
                                    >{{ old('content') }}</textarea>

                                    @error('content')
                                        <small class="order-review-form__error">
                                            {{ $message }}
                                        </small>
                                    @enderror

                                    <button type="submit">
                                        Gửi phản hồi
                                    </button>
                                </form>
                            </details>
                        @endif
                    @endauth
                </div>
            </article>
        @empty
            <div class="product-reviews-empty">
                <strong>
                    Sản phẩm chưa có đánh giá
                </strong>

                <p>
                    Những khách hàng đã mua sản phẩm có thể
                    đánh giá sau khi đơn hàng hoàn thành.
                </p>
            </div>
        @endforelse
    </div>

    @if ($reviews->hasPages())
        <div class="product-reviews-pagination">
            {{ $reviews->withQueryString()->links() }}
        </div>
    @endif
</section>

<div
    class="product-review-media-modal"
    data-review-media-modal
    hidden
>
    <button
        type="button"
        class="product-review-media-modal__backdrop"
        data-review-media-close
        aria-label="Đóng"
    ></button>

    <div
        class="product-review-media-modal__content"
        role="dialog"
        aria-modal="true"
        aria-label="Nội dung đánh giá"
    >
        <button
            type="button"
            class="product-review-media-modal__close"
            data-review-media-close
            aria-label="Đóng"
        >
            ×
        </button>

        <div data-review-media-container></div>
    </div>
</div>

            <section
                class="product-questions-section"
                id="product-questions"
            >
                @include(
                    'client.product.components.product-questions',
                    [
                        'product' => $product,
                        'productQuestions' => $productQuestions,
                        'questionCount' => $questionCount,
                    ]
                )
            </section>
            </div>

            @if ($relatedProducts->isNotEmpty())
                <section class="home-section product-related">
                    <div class="home-section__heading">
                        <div>
                            <p class="home-section__eyebrow">
                                Có thể bạn quan tâm
                            </p>

                            <h2 class="home-section__title">
                                Sản phẩm liên quan
                            </h2>
                        </div>
                    </div>

                    <div class="product-grid">
                        @foreach (
                            $relatedProducts as $relatedProduct
                        )
                            @include(
                                'client.components.product-card',
                                [
                                    'product' => $relatedProduct,
                                    'badge' => $relatedProduct
                                        ->is_featured
                                            ? 'Nổi bật'
                                            : null,
                                ]
                            )
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
        @if (
    isset($recentlyViewedProducts)
    && $recentlyViewedProducts->isNotEmpty()
)
    <section class="recent-products-section">
        <div class="recent-products-section__heading">
            <div>
                <p class="home-section__eyebrow">
                    Lịch sử của bạn
                </p>

                <h2>Bạn vừa xem</h2>

                <p>
                    Những sản phẩm bạn đã xem gần đây.
                </p>
            </div>

            <a
                href="{{ route('recently-viewed.index') }}"
                class="recent-products-section__all"
            >
                Xem tất cả
            </a>
        </div>

        <div class="product-grid recent-products-section__grid">
            @foreach (
                $recentlyViewedProducts
                as $recentlyViewedProduct
            )
                @include(
                    'client.components.product-card',
                    [
                        'product' => $recentlyViewedProduct,
                        'badge' =>
                            $recentlyViewedProduct->is_featured
                                ? 'Nổi bật'
                                : null,
                    ]
                )
            @endforeach
        </div>
    </section>
@endif
    </section>
@endsection


@push('styles')
    <style>
        .product-detail__main,
        .product-detail__tabs,
        .product-reviews-section,
        .product-questions-section {
            min-width: 0;
        }

        .product-detail__main {
            overflow: hidden;
        }

        .product-detail__info {
            min-width: 0;
        }

        .product-detail__meta {
            padding: 0.9rem 1rem;
            border: 1px solid #f3e8ee;
            border-radius: 0.9rem;
            background: linear-gradient(135deg, #fff7fb, #ffffff);
        }

        .product-detail__rating {
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        .product-detail__rating > span {
            letter-spacing: 0.12em;
        }

        .product-reviews-section {
            margin-top: 0;
            border-color: #eadde4;
            box-shadow: 0 14px 34px rgb(17 24 39 / 5%);
        }

        .product-reviews-section__heading {
            align-items: flex-start;
        }

        .product-reviews-section__description {
            max-width: 680px;
            margin: 0.7rem 0 0;
            color: #6b7280;
            line-height: 1.7;
        }

        .product-review-summary {
            border: 1px solid #f7cfdf;
            box-shadow: inset 0 1px 0 rgb(255 255 255 / 80%);
        }

        .product-review-summary__average {
            padding: 1.25rem;
            border-radius: 1rem;
            background: rgb(255 255 255 / 78%);
        }

        .product-review-summary__average > strong {
            display: inline-block;
            min-width: 96px;
        }

        .product-review-breakdown-row {
            min-height: 30px;
        }

        .product-review-breakdown-row__bar {
            height: 11px;
        }

        .product-review-card {
            border: 1px solid #f3e8ee;
            border-radius: 1rem;
            padding: 1.25rem;
            background: #ffffff;
        }

        .product-review-card + .product-review-card {
            margin-top: 0;
        }

        .product-review-card__body {
            min-width: 0;
        }

        .product-questions-section {
            margin-top: clamp(2rem, 5vw, 4rem);
            padding: clamp(1.25rem, 4vw, 2rem);
            border: 1px solid #eadde4;
            border-radius: 1.25rem;
            background: #ffffff;
            box-shadow: 0 14px 34px rgb(17 24 39 / 5%);
        }

        .product-questions-section > * {
            margin: 0;
        }

        .product-questions-section form,
        .product-questions-section textarea,
        .product-questions-section input,
        .product-questions-section select {
            max-width: 100%;
            box-sizing: border-box;
        }

        .product-questions-section textarea {
            width: 100%;
            resize: vertical;
        }


        .product-review-replies {
            position: relative;
            margin-top: 1rem;
            padding-left: 1rem;
            border-left: 2px solid #f9a8d4;
        }

        .product-review-reply {
            margin-top: .75rem;
            padding: 1rem;
            border-radius: .9rem;
        }

        .product-review-reply--shop {
            border: 1px solid #f5c2d8;
            background: linear-gradient(135deg, #fff1f7, #ffffff);
        }

        .product-review-reply--customer {
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .product-review-reply__heading {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .product-review-shop-badge,
        .product-review-customer-badge {
            display: inline-flex;
            align-items: center;
            padding: .25rem .55rem;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 800;
        }

        .product-review-shop-badge {
            background: linear-gradient(135deg, #be185d, #ec4899);
            color: #ffffff;
        }

        .product-review-customer-badge {
            background: #ecfdf5;
            color: #047857;
        }

        .product-review-conversation {
            margin-top: 1rem;
            border-top: 1px solid #f3f4f6;
            padding-top: .9rem;
        }

        .product-review-conversation summary {
            width: max-content;
            color: #db2777;
            font-size: .84rem;
            font-weight: 800;
            cursor: pointer;
        }

        .product-review-reply-form {
            display: grid;
            gap: .65rem;
            margin-top: .85rem;
            padding: 1rem;
            border: 1px solid #f3e8ee;
            border-radius: .9rem;
            background: #fffafa;
        }

        .product-review-reply-form label {
            font-size: .85rem;
            font-weight: 800;
            color: #374151;
        }

        .product-review-reply-form textarea {
            width: 100%;
            box-sizing: border-box;
            padding: .8rem .9rem;
            border: 1px solid #d1d5db;
            border-radius: .75rem;
            resize: vertical;
        }

        .product-review-reply-form textarea:focus {
            outline: 0;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgb(236 72 153 / 12%);
        }

        .product-review-reply-form button {
            justify-self: end;
            min-height: 42px;
            padding: .65rem 1rem;
            border: 0;
            border-radius: .7rem;
            background: #ec4899;
            color: #ffffff;
            font-weight: 800;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .product-reviews-section__heading {
                gap: 0.75rem;
            }

            .product-review-card {
                padding: 1rem;
            }

            .product-questions-section {
                padding: 1rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        window.productDetailDiscount = @json([
            'percent' => $discount?->discount_percent,
            'amount' => $discount?->discount_amount,
        ]);
    </script>
@endpush
