@if (
    isset($middleBanners)
    && $middleBanners->isNotEmpty()
)
    <section class="home-middle-banners">
        <div class="client-container">
            <div class="home-middle-banners__list">
                @foreach ($middleBanners as $banner)
                    @php
                        $desktopImage = asset(
                            'storage/' . ltrim(
                                $banner->desktop_image,
                                '/'
                            )
                        );

                        $mobileImage = $banner->mobile_image
                            ? asset(
                                'storage/' . ltrim(
                                    $banner->mobile_image,
                                    '/'
                                )
                            )
                            : $desktopImage;
                    @endphp

                    <a
                        href="{{ $banner->link_url ?: '#' }}"
                        target="{{ $banner->target ?: '_self' }}"
                        class="home-middle-banner"
                        @if (!$banner->link_url)
                            aria-disabled="true"
                            onclick="return false;"
                        @endif
                        @if (($banner->target ?: '_self') === '_blank')
                            rel="noopener noreferrer"
                        @endif
                    >
                        <picture
                            style="--banner-image: url('{{ $desktopImage }}')"
                        >
                            <source
                                media="(max-width: 640px)"
                                srcset="{{ $mobileImage }}"
                            >

                            <img
                                src="{{ $desktopImage }}"
                                alt="{{ $banner->title ?: $banner->name }}"
                                loading="lazy"
                            >
                        </picture>

                        @if (
                            $banner->title
                            || $banner->subtitle
                            || $banner->button_text
                        )
                            <div class="home-middle-banner__overlay">
                                @if ($banner->title)
                                    <h2>{{ $banner->title }}</h2>
                                @endif

                                @if ($banner->subtitle)
                                    <p>{{ $banner->subtitle }}</p>
                                @endif

                                @if ($banner->button_text)
                                    <span>{{ $banner->button_text }}</span>
                                @endif
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
