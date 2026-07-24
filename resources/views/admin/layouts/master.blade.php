<!DOCTYPE html>
<html lang="vi">

<head>
    @include('admin.partials.head')
</head>

<body class="bg-gray-100 text-gray-800">

    <div
        x-data="{
            sidebarOpen: false,
            profileOpen: false
        }"
        class="min-h-screen"
    >
        {{-- Lớp nền khi mở sidebar trên điện thoại --}}
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            @click="sidebarOpen = false"
            style="display: none;"
        ></div>

        {{-- Sidebar --}}
        @include('admin.partials.sidebar')

        <div class="min-h-screen lg:pl-64">

            {{-- Header --}}
            @include('admin.partials.header')

            {{-- Nội dung chính --}}
            <main class="min-w-0 p-4 sm:p-6 lg:p-8">

                {{-- Thông báo thành công --}}
                @if (session('success'))
                    <div
                    class="admin-alert mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 transition duration-300"
                    role="alert"
                    >
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Thông báo lỗi --}}
                @if (session('error'))
                    <div
                        class="admin-alert mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 transition duration-300"
                        role="alert"
                    >
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Hiển thị lỗi validation --}}
                @if ($errors->any())
                    <div
                        class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                        role="alert"
                    >
                        <p class="font-semibold">
                            Dữ liệu chưa hợp lệ:
                        </p>

                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Tiêu đề trang --}}
                @hasSection('page-header')
                    <div class="mb-6">
                        @yield('page-header')
                    </div>
                @endif

                {{-- Nội dung từng trang Admin --}}
                @yield('content')

            </main>

            {{-- Footer --}}
            @include('admin.partials.footer')
        </div>
    </div>

    @include('admin.partials.scripts')

    @stack('scripts')
</body>

</html>
