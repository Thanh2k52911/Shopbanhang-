@if ($brands->isNotEmpty())
    <section class="home-section home-brands">
        <div class="client-container">
            <div class="home-section__heading">
                <div>
                    <p class="home-section__eyebrow">
                        Thương hiệu
                    </p>

                    <h2 class="home-section__title">
                        Thương hiệu nổi bật
                    </h2>
                </div>

                <a href="{{ route('products.index') }}"
                   class="home-section__more">
                    Xem tất cả
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            <div class="home-brands__grid">
                @foreach ($brands as $brand)
                    <a href="{{ route('products.index', ['brand' => $brand->slug]) }}"
                       class="home-brand-card">

                        <div class="home-brand-card__logo">
                            @if ($brand->thumbnail)
                                <img
                                    src="{{ asset('storage/' . ltrim($brand->thumbnail, '/')) }}"
                                    alt="{{ $brand->name }}"
                                    loading="lazy"
                                >
                            @else
                                <span>
                                    {{ mb_strtoupper(
                                        mb_substr($brand->name, 0, 1)
                                    ) }}
                                </span>
                            @endif
                        </div>

                        <div class="home-brand-card__content">
                            <h3>
                                {{ $brand->name }}
                            </h3>

                            @if ($brand->country)
                                <p>
                                    {{ $brand->country }}
                                </p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
