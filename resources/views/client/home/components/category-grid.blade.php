@if ($categories->isNotEmpty())
    <section class="home-section home-categories">
        <div class="client-container">
            <div class="home-section__heading">
                <div>
                    <p class="home-section__eyebrow">
                        Khám phá
                    </p>

                    <h2 class="home-section__title">
                        Danh mục nổi bật
                    </h2>
                </div>

                <a href="{{ route('products.index') }}"
                   class="home-section__more">
                    Xem tất cả
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            <div class="home-categories__grid">
                @foreach ($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                       class="home-category-card">

                        <div class="home-category-card__media">
                            @if ($category->thumbnail)
                                <img
                                   src="{{ asset('storage/' . ltrim($category->thumbnail, '/')) }}"
                                    alt="{{ $category->name }}"
                                    class="home-category-card__image"
                                    loading="lazy"
                                >
                            @else
                                <div class="home-category-card__placeholder">
                                    {{ mb_strtoupper(
                                        mb_substr($category->name, 0, 1)
                                    ) }}
                                </div>
                            @endif
                        </div>

                        <div class="home-category-card__body">
                            <h3 class="home-category-card__name">
                                {{ $category->name }}
                            </h3>

                            @if ($category->description)
                                <p class="home-category-card__description">
                                    {{ \Illuminate\Support\Str::limit(
                                        $category->description,
                                        70
                                    ) }}
                                </p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
