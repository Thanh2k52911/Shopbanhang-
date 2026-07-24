@php
use Illuminate\Support\Facades\Storage;
@endphp
@if ($banners->isNotEmpty())
    <section
        class="home-banner"
        data-banner-slider
        aria-label="Banner khuyến mãi"
    >
        <div class="home-banner__viewport">
            <div
                class="home-banner__track"
                data-banner-track
            >
                @foreach ($banners as $index => $banner)
                    <article
                        class="home-banner__slide"
                        data-banner-slide
                        @if ($index !== 0) hidden @endif
                    >
                        @php
    $desktopImage = Storage::url(
        $banner->desktop_image
    );

    $mobileImage = $banner->mobile_image
        ? Storage::url($banner->mobile_image)
        : $desktopImage;
@endphp

                        <picture>
                            <source
                                media="(max-width: 640px)"
                                srcset="{{ $mobileImage }}"
                            >

                            <img
                                src="{{ $desktopImage }}"
                                alt="{{ $banner->title ?? $banner->name }}"
                                class="home-banner__image"
                                @if ($index === 0)
                                    fetchpriority="high"
                                @else
                                    loading="lazy"
                                @endif
                            >
                        </picture>

                        <div class="home-banner__overlay"></div>

                        <div class="client-container home-banner__content">
                            @if ($banner->subtitle)
                                <p class="home-banner__subtitle">
                                    {{ $banner->subtitle }}
                                </p>
                            @endif

                            @if ($banner->title)
                                <h2 class="home-banner__title">
                                    {{ $banner->title }}
                                </h2>
                            @endif

                            @if ($banner->link_url)
                                <a
                                    href="{{ $banner->link_url }}"
                                    target="{{ $banner->target }}"
                                    class="home-banner__button"
                                    @if ($banner->target === '_blank')
                                        rel="noopener noreferrer"
                                    @endif
                                >
                                    {{ $banner->button_text ?: 'Khám phá ngay' }}
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($banners->count() > 1)
                <button
                    type="button"
                    class="home-banner__arrow home-banner__arrow--previous"
                    data-banner-previous
                    aria-label="Banner trước"
                >
                    ‹
                </button>

                <button
                    type="button"
                    class="home-banner__arrow home-banner__arrow--next"
                    data-banner-next
                    aria-label="Banner tiếp theo"
                >
                    ›
                </button>

                <div
                    class="home-banner__dots"
                    aria-label="Chọn banner"
                >
                    @foreach ($banners as $index => $banner)
                        <button
                            type="button"
                            class="home-banner__dot
                                {{ $index === 0 ? 'is-active' : '' }}"
                            data-banner-dot="{{ $index }}"
                            aria-label="Chuyển đến banner {{ $index + 1 }}"
                            aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                        ></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
