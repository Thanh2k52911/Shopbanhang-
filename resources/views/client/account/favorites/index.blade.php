@extends('client.layouts.master')

@section('title', 'Sản phẩm yêu thích - Cosmetic Shop')

@section(
    'meta_description',
    'Danh sách các sản phẩm mỹ phẩm bạn đã yêu thích tại Cosmetic Shop.'
)

@section('content')
    <section class="account-favorites-page">
        <div class="client-container">
            <nav
                class="account-favorites-page__breadcrumb"
                aria-label="Breadcrumb"
            >
                <a href="{{ route('home') }}">
                    Trang chủ
                </a>

                <span aria-hidden="true">/</span>

                <a href="{{ route('account.index') }}">
                Tài khoản
                </a>

                <span aria-hidden="true">/</span>

                <span>Sản phẩm yêu thích</span>
            </nav>

            <div class="account-favorites-page__layout">


                <div class="account-favorites-page__content">
                    <header class="account-favorites-page__header">
                        <div>
                            <p class="home-section__eyebrow">
                                Danh sách của bạn
                            </p>

                            <h1>Sản phẩm yêu thích</h1>

                            <p>
                                Bạn đang có
                                <strong data-favorites-total>
                                    {{ $favorites->total() }}
                                </strong>
                                sản phẩm yêu thích.
                            </p>
                        </div>
                    </header>

                    <div
                        class="account-favorites-page__message"
                        data-favorites-message
                        aria-live="polite"
                    ></div>

                    @if ($favorites->isNotEmpty())
                        <div
                            class="account-favorites-grid"
                            data-favorites-grid
                        >
                            @foreach ($favorites as $favorite)
                                @php
                                    $product = $favorite->product;

                                    $productUrl = route(
                                        'products.show',
                                        $product->slug
                                    );

                                    $thumbnail = $product->images->first();

                                    $imageUrl = $thumbnail
                                        ? asset(
                                            'images/' .
                                            $thumbnail->image_path
                                        )
                                        : null;

                                    $firstSku = $product->skus->first();

                                    $displayPrice = $firstSku
                                        ? (float) $firstSku->price
                                        : 0;
                                @endphp

                                <article
                                    class="account-favorite-card"
                                    data-favorite-card="{{ $favorite->id }}"
                                >
                                    <div class="account-favorite-card__media">
                                        <a
                                            href="{{ $productUrl }}"
                                            class="account-favorite-card__image-link"
                                        >
                                            @if ($imageUrl)
                                                <img
                                                    src="{{ $imageUrl }}"
                                                    alt="{{ $product->name }}"
                                                    class="account-favorite-card__image"
                                                    loading="lazy"
                                                >
                                            @else
                                                <span class="account-favorite-card__placeholder">
                                                    Cosmetic Shop
                                                </span>
                                            @endif
                                        </a>

                                        <button
                                            type="button"
                                            class="account-favorite-card__remove"
                                            data-favorite-remove
                                            data-favorite-remove-url="{{ route(
                                                'account.favorites.destroy',
                                                $favorite
                                            ) }}"
                                            aria-label="Xóa {{ $product->name }} khỏi danh sách yêu thích"
                                            title="Bỏ yêu thích"
                                        >
                                            ×
                                        </button>
                                    </div>

                                    <div class="account-favorite-card__body">
                                        @if ($product->brand)
                                            <p class="account-favorite-card__brand">
                                                {{ $product->brand->name }}
                                            </p>
                                        @endif

                                        <h2 class="account-favorite-card__name">
                                            <a href="{{ $productUrl }}">
                                                {{ $product->name }}
                                            </a>
                                        </h2>

                                        @if ($product->short_description)
                                            <p class="account-favorite-card__description">
                                                {{ \Illuminate\Support\Str::limit(
                                                    $product->short_description,
                                                    90
                                                ) }}
                                            </p>
                                        @endif

                                        <div class="account-favorite-card__price">
                                            <strong>
                                                {{ number_format(
                                                    $displayPrice,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}đ
                                            </strong>
                                        </div>

                                        <div class="account-favorite-card__actions">
                                            <a
                                                href="{{ $productUrl }}"
                                                class="account-favorite-card__view"
                                            >
                                                Xem sản phẩm
                                            </a>

                                            @if ($firstSku)
                                                <button
                                                    type="button"
                                                    class="account-favorite-card__cart"
                                                    data-add-to-cart
                                                    data-product-id="{{ $product->id }}"
                                                    data-sku-id="{{ $firstSku->id }}"
                                                >
                                                    Thêm vào giỏ
                                                </button>

                                                <button
                                                    type="button"
                                                    class="account-favorite-card__buy-now"
                                                    data-buy-now
                                                    data-product-id="{{ $product->id }}"
                                                    data-sku-id="{{ $firstSku->id }}"
                                                >
                                                    Mua ngay
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div
                            class="account-favorites-empty"
                            data-favorites-empty
                            hidden
                        >
                            <span aria-hidden="true">♡</span>

                            <h2>Chưa có sản phẩm yêu thích</h2>

                            <p>
                                Hãy thêm những sản phẩm bạn quan tâm
                                để dễ dàng tìm lại sau này.
                            </p>

                            <a href="{{ route('products.index') }}">
                                Khám phá sản phẩm
                            </a>
                        </div>

                        @if ($favorites->hasPages())
                            <div
                                class="account-favorites-pagination"
                                data-favorites-pagination
                            >
                                {{ $favorites->withQueryString()->links() }}
                            </div>
                        @endif
                    @else
                        <div class="account-favorites-empty">
                            <span aria-hidden="true">♡</span>

                            <h2>Chưa có sản phẩm yêu thích</h2>

                            <p>
                                Hãy thêm những sản phẩm bạn quan tâm
                                để dễ dàng tìm lại sau này.
                            </p>

                            <a href="{{ route('products.index') }}">
                                Khám phá sản phẩm
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
