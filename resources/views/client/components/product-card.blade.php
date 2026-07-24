@php
    $productUrl = route(
    'products.show',
    $product->slug
);

    $imageUrl = null;

if (! empty($product->image_path)) {
    $publicImagePath = public_path(
        'storage/'
        . ltrim($product->image_path, '/')
    );

    if (file_exists($publicImagePath)) {
        $imageUrl = asset(
            'storage/'
            . ltrim($product->image_path, '/')
        );
    }
}

    $displayPrice = isset($product->sale_price)
        ? (float) $product->sale_price
        : (float) ($product->price ?? 0);

    $originalPrice = isset($product->original_price)
        ? (float) $product->original_price
        : null;

    $hasDiscount = $originalPrice
        && $originalPrice > $displayPrice;

    $cardBadge = $badge
        ?? ($product->discount_label ?? null);

    $soldQuantity = (int) (
        $product->sold_quantity ?? 0
    );

    $limitQuantity = (int) (
        $product->limit_quantity ?? 0
    );

    $progress = $limitQuantity > 0
        ? min(
            100,
            round(
                ($soldQuantity / $limitQuantity) * 100
            )
        )
        : 0;
@endphp

<article class="product-card">
    <div class="product-card__media">
        @if ($cardBadge)
            <span class="product-card__badge">
                {{ $cardBadge }}
            </span>
        @endif

        @if ($imageUrl)
            <a href="{{ $productUrl }}">
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $product->name }}"
                    class="product-card__image"
                    loading="lazy"
                >
            </a>
        @else
            <a
                href="{{ $productUrl }}"
                class="product-card__placeholder"
                aria-label="{{ $product->name }}"
            >
                <span>Cosmetic Shop</span>
            </a>
        @endif

        <div class="product-card__actions">
            @auth
    <button
        type="button"
        class="product-card__action product-card__favorite-action {{ (int) ($product->is_favorite ?? 0) > 0
            ? 'is-favorite'
            : '' }}"
        data-favorite-toggle
        data-favorite-url="{{ route(
            'account.favorites.toggle',
            ['product' => $product->id]
        ) }}"
        aria-pressed="{{ (int) ($product->is_favorite ?? 0) > 0
            ? 'true'
            : 'false' }}"
        aria-label="{{ (int) ($product->is_favorite ?? 0) > 0
            ? 'Bỏ khỏi danh sách yêu thích'
            : 'Thêm vào danh sách yêu thích' }}"
        title="{{ (int) ($product->is_favorite ?? 0) > 0
            ? 'Đã yêu thích'
            : 'Yêu thích' }}"
    >
        <span
            class="product-favorite-button__icon"
            aria-hidden="true"
        >
            {{ (int) ($product->is_favorite ?? 0) > 0
                ? '♥'
                : '♡' }}
        </span>

        <span
            class="sr-only"
            data-favorite-label
        >
            {{ (int) ($product->is_favorite ?? 0) > 0
                ? 'Đã yêu thích'
                : 'Yêu thích' }}
        </span>
    </button>
@else
    <a
        href="{{ route('login') }}"
        class="product-card__action"
        aria-label="Đăng nhập để thêm vào yêu thích"
        title="Đăng nhập để yêu thích"
    >
        ♡
    </a>
@endauth

            <a
                href="{{ $productUrl }}"
                class="product-card__action"
                aria-label="Xem sản phẩm"
                title="Xem sản phẩm"
            >
                👁
            </a>
        </div>
    </div>

    <div class="product-card__body">
        @if (!empty($product->brand_name))
            <p class="product-card__brand">
                {{ $product->brand_name }}
            </p>
        @endif

        <h3 class="product-card__name">
            <a href="{{ $productUrl }}">
                {{ $product->name }}
            </a>
        </h3>

        @if (!empty($product->short_description))
            <p class="product-card__description">
                {{ \Illuminate\Support\Str::limit(
                    $product->short_description,
                    70
                ) }}
            </p>
        @endif

        <div class="product-card__rating">
            <span aria-label="Đánh giá sản phẩm">
                ★★★★★
            </span>

            @if (isset($product->review_count))
                <small>
                    ({{ $product->review_count }})
                </small>
            @endif
        </div>

        <div class="product-card__prices">
            <strong>
                {{ number_format(
                    $displayPrice,
                    0,
                    ',',
                    '.'
                ) }}đ
            </strong>

            @if ($hasDiscount)
                <del>
                    {{ number_format(
                        $originalPrice,
                        0,
                        ',',
                        '.'
                    ) }}đ
                </del>
            @endif
        </div>

        @if ($limitQuantity > 0)
            <div class="product-card__sold">
                <div class="product-card__progress">
                    <span
                        style="width: {{ $progress }}%"
                    ></span>
                </div>

                <small>
                    Đã bán {{ $soldQuantity }}
                    / {{ $limitQuantity }}
                </small>
            </div>
        @elseif (!empty($product->sold_quantity))
            <p class="product-card__sold-text">
                Đã bán {{ $soldQuantity }}
            </p>
        @endif

        <div class="product-card__purchase-actions">
            <button
                type="button"
                class="product-card__cart-button"
                data-add-to-cart
                data-product-id="{{ $product->id }}"
            >
                Thêm vào giỏ
            </button>

            <button
                type="button"
                class="product-card__buy-now-button"
                data-buy-now
                data-product-id="{{ $product->id }}"
            >
                Mua ngay
            </button>
        </div>
    </div>
</article>
