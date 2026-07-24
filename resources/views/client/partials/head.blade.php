<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    @php
        $pageTitle = trim(
            $__env->yieldContent(
                'title',
                setting_text(
                    'meta_title',
                    site_name()
                )
            )
        );

        $metaDescription = trim(
            $__env->yieldContent(
                'meta_description',
                setting_text(
                    'meta_description',
                    'Website mỹ phẩm chính hãng '
                        . site_name()
                )
            )
        );

        $metaKeywords = setting_text(
            'meta_keywords'
        );

        $faviconUrl = site_favicon();
    @endphp

    <title>{{ $pageTitle }}</title>

    <meta
        name="description"
        content="{{ $metaDescription }}"
    >

    @if ($metaKeywords !== '')
        <meta
            name="keywords"
            content="{{ $metaKeywords }}"
        >
    @endif

    <meta
        property="og:site_name"
        content="{{ site_name() }}"
    >

    <meta
        property="og:title"
        content="{{ $pageTitle }}"
    >

    <meta
        property="og:description"
        content="{{ $metaDescription }}"
    >

    <meta
        property="og:type"
        content="website"
    >

    <meta
        property="og:url"
        content="{{ url()->current() }}"
    >

    @hasSection('og_image')
        <meta
            property="og:image"
            content="@yield('og_image')"
        >
    @elseif (site_logo())
        <meta
            property="og:image"
            content="{{ site_logo() }}"
        >
    @endif

    @if ($faviconUrl)
        <link
            rel="icon"
            href="{{ $faviconUrl }}"
        >

        <link
            rel="shortcut icon"
            href="{{ $faviconUrl }}"
        >

        <link
            rel="apple-touch-icon"
            href="{{ $faviconUrl }}"
        >
    @endif

    @vite([
        'resources/css/client.css',
        'resources/js/client.js',
    ])

    @stack('styles')
</head>
