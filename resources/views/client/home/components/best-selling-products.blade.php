@if ($bestSellingProducts->isNotEmpty())
    <section class="home-section home-products home-products--best-selling">
        <div class="client-container">
            <div class="home-section__heading">
                <div>
                    <p class="home-section__eyebrow">
                        Được yêu thích
                    </p>

                    <h2 class="home-section__title">
                        Sản phẩm bán chạy
                    </h2>
                </div>

                <a
                    href="{{ route('products.index', ['sort' => 'best_selling']) }}"
                >
                    Xem tất cả
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            <div class="product-grid">
                @foreach ($bestSellingProducts as $product)
                    @include(
                        'client.components.product-card',
                        [
                            'product' => $product,
                            'badge' => 'Bán chạy',
                        ]
                    )
                @endforeach
            </div>
        </div>
    </section>
@endif
