@extends('client.layouts.master')

@section('title', 'Sản phẩm đã xem gần đây - Cosmetic Shop')

@section(
    'meta_description',
    'Xem lại những sản phẩm mỹ phẩm bạn đã xem gần đây tại Cosmetic Shop.'
)

@section('content')
    <section class="recently-viewed-page">
        <div class="client-container">
            <nav
                class="recently-viewed-page__breadcrumb"
                aria-label="Breadcrumb"
            >
                <a href="{{ route('home') }}">
                    Trang chủ
                </a>

                <span aria-hidden="true">/</span>

                <span>Sản phẩm đã xem gần đây</span>
            </nav>

            <header class="recently-viewed-page__header">
                <div>
                    <p class="home-section__eyebrow">
                        Lịch sử của bạn
                    </p>

                    <h1>Sản phẩm đã xem gần đây</h1>

                    <p>
                        Bạn đang có
                        <strong data-recently-viewed-total>
                            {{ $recentProducts->total() }}
                        </strong>
                        sản phẩm trong lịch sử xem.
                    </p>
                </div>

                @if ($recentProducts->isNotEmpty())
                    <form
                        action="{{ route('recently-viewed.clear') }}"
                        method="POST"
                        class="recently-viewed-page__clear-form"
                        data-clear-recently-viewed-form
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="recently-viewed-page__clear-button"
                        >
                            Xóa toàn bộ lịch sử
                        </button>
                    </form>
                @endif
            </header>

            @if (session('success'))
                <div class="recently-viewed-message recently-viewed-message--success">
                    {{ session('success') }}
                </div>
            @endif

            <div
                class="recently-viewed-message"
                data-recently-viewed-message
                aria-live="polite"
            ></div>

            @if ($recentProducts->isNotEmpty())
                <div
                    class="recently-viewed-grid"
                    data-recently-viewed-grid
                >
                    @foreach ($recentProducts as $recent)
                        @php
                            $product = $recent->product;

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
                            class="recently-viewed-card"
                            data-recently-viewed-card="{{ $recent->id }}"
                        >
                            <div class="recently-viewed-card__media">
                                <a
                                    href="{{ $productUrl }}"
                                    class="recently-viewed-card__image-link"
                                >
                                    @if ($imageUrl)
                                        <img
                                            src="{{ $imageUrl }}"
                                            alt="{{ $product->name }}"
                                            class="recently-viewed-card__image"
                                            loading="lazy"
                                        >
                                    @else
                                        <span class="recently-viewed-card__placeholder">
                                            Cosmetic Shop
                                        </span>
                                    @endif
                                </a>

                                <button
                                    type="button"
                                    class="recently-viewed-card__remove"
                                    data-recently-viewed-remove
                                    data-remove-url="{{ route(
                                        'recently-viewed.destroy',
                                        [
                                            'recentlyViewedProduct' =>
                                                $recent->id,
                                        ]
                                    ) }}"
                                    aria-label="Xóa {{ $product->name }} khỏi lịch sử đã xem"
                                    title="Xóa khỏi lịch sử"
                                >
                                    ×
                                </button>
                            </div>

                            <div class="recently-viewed-card__body">
                                @if ($product->brand)
                                    <p class="recently-viewed-card__brand">
                                        {{ $product->brand->name }}
                                    </p>
                                @endif

                                <h2 class="recently-viewed-card__name">
                                    <a href="{{ $productUrl }}">
                                        {{ $product->name }}
                                    </a>
                                </h2>

                                @if ($product->short_description)
                                    <p class="recently-viewed-card__description">
                                        {{ \Illuminate\Support\Str::limit(
                                            $product->short_description,
                                            90
                                        ) }}
                                    </p>
                                @endif

                                <div class="recently-viewed-card__meta">
                                    <span>
                                        Đã xem
                                        <strong>
                                            {{ $recent->view_count }}
                                        </strong>
                                        lần
                                    </span>

                                    <time
                                        datetime="{{ optional(
                                            $recent->last_viewed_at
                                        )->toIso8601String() }}"
                                    >
                                        {{ optional(
                                            $recent->last_viewed_at
                                        )->format('d/m/Y H:i') }}
                                    </time>
                                </div>

                                <div class="recently-viewed-card__price">
                                    <strong>
                                        {{ number_format(
                                            $displayPrice,
                                            0,
                                            ',',
                                            '.'
                                        ) }}đ
                                    </strong>
                                </div>

                                <div class="recently-viewed-card__actions">
                                    <a
                                        href="{{ $productUrl }}"
                                        class="recently-viewed-card__view"
                                    >
                                        Xem lại sản phẩm
                                    </a>

                                    @if ($firstSku)
                                        <button
                                            type="button"
                                            class="recently-viewed-card__cart"
                                            data-add-to-cart
                                            data-product-id="{{ $product->id }}"
                                            data-sku-id="{{ $firstSku->id }}"
                                        >
                                            Thêm vào giỏ
                                        </button>

<button
                                            type="button"
                                            class="recently-viewed-card__buy-now"
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
                    class="recently-viewed-empty"
                    data-recently-viewed-empty
                    hidden
                >
                    <span aria-hidden="true">👁</span>

                    <h2>Chưa có sản phẩm đã xem</h2>

                    <p>
                        Hãy khám phá các sản phẩm để lịch sử xem
                        được hiển thị tại đây.
                    </p>

                    <a href="{{ route('products.index') }}">
                        Khám phá sản phẩm
                    </a>
                </div>

                @if ($recentProducts->hasPages())
                    <div
                        class="recently-viewed-pagination"
                        data-recently-viewed-pagination
                    >
                        {{ $recentProducts
                            ->withQueryString()
                            ->links() }}
                    </div>
                @endif
            @else
                <div class="recently-viewed-empty">
                    <span aria-hidden="true">👁</span>

                    <h2>Chưa có sản phẩm đã xem</h2>

                    <p>
                        Hãy khám phá các sản phẩm để lịch sử xem
                        được hiển thị tại đây.
                    </p>

                    <a href="{{ route('products.index') }}">
                        Khám phá sản phẩm
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection
