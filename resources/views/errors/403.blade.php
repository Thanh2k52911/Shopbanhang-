<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Không có quyền truy cập | {{ config('app.name', 'Cosmetic Shop') }}
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    @vite([
        'resources/css/client.css',
        'resources/js/client.js',
    ])
</head>

<body class="bg-gray-50 font-sans text-gray-800">

    <main
        class="flex min-h-screen items-center justify-center px-4 py-12 sm:px-6"
    >
        <div class="w-full max-w-xl text-center">

            {{-- Mã lỗi --}}
            <p class="text-8xl font-extrabold tracking-tight text-pink-600 sm:text-9xl">
                403
            </p>

            {{-- Tiêu đề --}}
            <h1 class="mt-6 text-2xl font-bold text-gray-900 sm:text-3xl">
                Bạn không có quyền truy cập
            </h1>

            {{-- Nội dung --}}
            <p class="mx-auto mt-4 max-w-md text-sm leading-6 text-gray-500 sm:text-base">
                Tài khoản hiện tại không được phép truy cập khu vực hoặc chức năng này.
                Vui lòng quay lại trang trước hoặc trở về trang chủ.
            </p>

            {{-- Thông báo được truyền từ abort --}}
            @if ($exception?->getMessage())
                <div
                    class="mx-auto mt-6 max-w-md rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                >
                    {{ $exception->getMessage() }}
                </div>
            @endif

            {{-- Các nút điều hướng --}}
            <div
                class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row"
            >
                <button
                    type="button"
                    onclick="window.history.back()"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 sm:w-auto"
                >
                    <svg
                        class="h-5 w-5"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"
                        />
                    </svg>

                    Quay lại
                </button>

                <a
                    href="{{ route('home') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-pink-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-pink-700 sm:w-auto"
                >
                    <svg
                        class="h-5 w-5"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.25 12l8.954-8.955a1.125 1.125 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-6.75h4.5V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"
                        />
                    </svg>

                    Về trang chủ
                </a>
            </div>

            {{-- Tài khoản hiện tại --}}
            @auth
                <div class="mt-8 text-sm text-gray-400">
                    Bạn đang đăng nhập bằng:

                    <span class="font-medium text-gray-600">
                        {{ auth()->user()->email }}
                    </span>
                </div>
            @endauth

        </div>
    </main>

</body>

</html>
