<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Cosmetic Shop') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite([
        'resources/css/client.css',
        'resources/js/client.js',
    ])
</head>
<body class="auth-body">
    <main
        class="auth-screen"
        style="--auth-background: url('{{ asset('images/auth-cosmetic-shop.jpe') }}');"
    >
        <div class="auth-screen__overlay" aria-hidden="true"></div>

        <a class="auth-screen__brand" href="{{ url('/') }}">
            <span class="auth-screen__brand-mark">CS</span>
            <span>Cosmetic Shop</span>
        </a>

        <section class="auth-panel">
            {{ $slot }}
        </section>
    </main>
</body>
</html>
