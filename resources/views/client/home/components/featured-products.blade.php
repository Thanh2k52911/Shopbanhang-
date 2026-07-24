@if ($featuredProducts->isNotEmpty())
    <section class="home-section home-products home-products--featured">
        <div class="client-container">
            <div class="home-section__heading">
                <div>
                    <p class="home-section__eyebrow">
                        Gợi ý hôm nay
                    </p>

                    <h2 class="home-section__title">
                        Sản phẩm nổi bật
                    </h2>
                </div>

                <a
    href="{{ route('products.index', ['sort' => 'featured']) }}"
    class="home-section__more"
></a>
                    Xem tất cả
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            <div class="product-grid">
                @foreach ($featuredProducts as $featuredProduct)
                    @include(
                        'client.components.product-card',
                        [
                            'product' => $featuredProduct,
                            'badge' => 'Nổi bật',
                        ]
                    )
                @endforeach
            </div>
        </div>
    </section>
@endif
