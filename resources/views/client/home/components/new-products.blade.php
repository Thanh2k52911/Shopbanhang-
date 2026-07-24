@if ($newProducts->isNotEmpty())
    <section class="home-section home-products">
        <div class="client-container">
            <div class="home-section__heading">
                <div>
                    <p class="home-section__eyebrow">
                        Vừa cập nhật
                    </p>

                    <h2 class="home-section__title">
                        Sản phẩm mới
                    </h2>
                </div>

                <a
                    href="{{ route('products.index', ['sort' => 'newest']) }}"
                    class="home-section__more"
                >
                    Xem tất cả
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            <div class="product-grid">
                @foreach ($newProducts as $product)
                    @include(
                        'client.components.product-card',
                        [
                            'product' => $product,
                            'badge' => 'Mới',
                        ]
                    )
                @endforeach
            </div>
        </div>
    </section>
@endif
