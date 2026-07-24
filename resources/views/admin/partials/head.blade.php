<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<meta
    name="csrf-token"
    content="{{ csrf_token() }}"
>

<title>
    @hasSection('title')
        @yield('title') | {{ config('app.name', 'Cosmetic Shop') }}
    @else
        Quản trị | {{ config('app.name', 'Cosmetic Shop') }}
    @endif
</title>

<meta
    name="description"
    content="@yield('meta_description', 'Hệ thống quản trị Cosmetic Shop')"
>

<link
    rel="icon"
    type="image/x-icon"
    href="{{ asset('favicon.ico') }}"
>

{{-- Font Inter từ Google Fonts --}}
<link rel="preconnect" href="https://fonts.googleapis.com">

<link
    rel="preconnect"
    href="https://fonts.gstatic.com"
    crossorigin
>

<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
    rel="stylesheet"
>

{{-- CSS và JavaScript Admin được build bởi Vite --}}
@vite([
    'resources/css/admin.css',
    'resources/js/admin.js'
])

{{-- CSS riêng của từng trang --}}
@stack('styles')
