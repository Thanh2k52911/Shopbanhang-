@php
    $footerSiteName = site_name();

    $footerLogo = site_logo();

    $footerInitial = mb_strtoupper(
        mb_substr(
            $footerSiteName,
            0,
            1
        )
    );

    $siteDescription = setting_text(
        'site_description',
        'Cung cấp mỹ phẩm chăm sóc da, trang điểm '
            . 'và chăm sóc cơ thể chính hãng.'
    );

    $hotline = setting_text(
        'contact_hotline',
        '1900 0000'
    );

    $contactEmail = setting_text(
        'contact_email',
        'support@cosmeticshop.vn'
    );

    $contactAddress = setting_text(
        'contact_address',
        'Hà Nội'
    );

    $socialLinks = [
        'Facebook' => setting_text('facebook_url'),
        'Instagram' => setting_text('instagram_url'),
        'YouTube' => setting_text('youtube_url'),
        'TikTok' => setting_text('tiktok_url'),
    ];
@endphp

<footer class="client-footer">
    <div class="client-container client-footer__main">
        <div class="client-footer__column">
            <a
                href="{{ route('home') }}"
                class="client-footer__logo"
                aria-label="{{ $footerSiteName }}"
            >
                @if ($footerLogo)
                    <span
                        class="client-footer__logo-mark"
                        style="
                            overflow: hidden;
                            background: #ffffff;
                        "
                    >
                        <img
                            src="{{ $footerLogo }}"
                            alt="{{ $footerSiteName }}"
                            style="
                                width: 100%;
                                height: 100%;
                                object-fit: contain;
                            "
                        >
                    </span>
                @else
                    <span class="client-footer__logo-mark">
                        {{ $footerInitial }}
                    </span>
                @endif

                <span>
                    {{ $footerSiteName }}
                </span>
            </a>

            <p class="client-footer__description">
                {{ $siteDescription }}
            </p>

            <div class="client-footer__socials">
                @foreach ($socialLinks as $label => $url)
                    @if ($url !== '')
                        <a
                            href="{{ $url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {{ $label }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="client-footer__column">
            <h2>
                Chính sách và hỗ trợ
            </h2>

            <ul class="client-footer__links">
                @forelse (($footerPages ?? collect()) as $page)
                    <li>
                        <a
                            href="{{ route(
                                'pages.show',
                                $page->slug
                            ) }}"
                        >
                            {{ $page->title }}
                        </a>
                    </li>
                @empty
                    <li>
                        <span>
                            Nội dung đang được cập nhật
                        </span>
                    </li>
                @endforelse
            </ul>
        </div>

        <div class="client-footer__column">
            <h2>
                Liên hệ
            </h2>

            <ul class="client-footer__contact">
                <li>
                    <span>
                        Hotline:
                    </span>

                    <a
                        href="tel:{{ preg_replace(
                            '/\D+/',
                            '',
                            $hotline
                        ) }}"
                    >
                        {{ $hotline }}
                    </a>
                </li>

                <li>
                    <span>
                        Email:
                    </span>

                    <a href="mailto:{{ $contactEmail }}">
                        {{ $contactEmail }}
                    </a>
                </li>

                <li>
                    <span>
                        Địa chỉ:
                    </span>

                    <p>
                        {{ $contactAddress }}
                    </p>
                </li>
            </ul>
        </div>
    </div>

    <div class="client-footer__bottom">
        <div class="client-container">
            <p>
                © {{ now()->year }} {{ $footerSiteName }}.
                All rights reserved.
            </p>
        </div>
    </div>
</footer>
