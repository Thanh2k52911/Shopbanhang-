@extends('client.layouts.master')

@section('title', 'Trang chủ - Cosmetic Shop')

@section(
    'meta_description',
    'Mua mỹ phẩm chăm sóc da, trang điểm và chăm sóc cơ thể chính hãng.'
)

@section('content')
    @include('client.home.components.banner-slider')

    @include('client.home.components.category-grid')

    @include('client.home.components.brand-grid')

    @include('client.home.components.flash-sale')

    @include('client.home.components.middle-banner')


    @include('client.home.components.new-products')

    @include('client.home.components.best-selling-products')

    @include('client.home.components.featured-products')

    @include('client.home.components.newsletter')

    @if (
        isset($recentlyViewedProducts)
        && $recentlyViewedProducts->isNotEmpty()
    )
        <section class="home-recent-history">
            <div class="client-container">
                <div class="home-recent-history__box">
                    <div class="home-recent-history__content">
                        <span
                            class="home-recent-history__icon"
                            aria-hidden="true"
                        >
                            👁
                        </span>

                        <div>
                            <p class="home-section__eyebrow">
                                Lịch sử của bạn
                            </p>

                            <h2>Xem lại sản phẩm vừa xem</h2>

                            <p>
                                Bạn đang có
                                <strong>
                                    {{ $recentlyViewedProducts->count() }}
                                </strong>
                                sản phẩm đã xem gần đây.
                            </p>
                        </div>
                    </div>

                    <a
                        href="{{ route('recently-viewed.index') }}"
                        class="home-recent-history__button"
                    >
                        Xem lịch sử
                    </a>
                </div>
            </div>
        </section>
    @endif
@endsection
