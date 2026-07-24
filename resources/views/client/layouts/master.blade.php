<!DOCTYPE html>
<html lang="vi">

@include('client.partials.head')

<body>

    @include('client.partials.topbar')

    @include('client.partials.header')

    @include('client.partials.navbar')

    <main>

        @yield('content')

    </main>

    @include('client.partials.footer')

    @include('client.partials.scripts')

</body>

</html>
