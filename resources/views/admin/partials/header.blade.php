<header class="sticky top-0 z-30 border-b border-gray-200 bg-white">

    <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">

        {{-- Bên trái --}}
        <div class="flex items-center gap-3">

            {{-- Nút mở sidebar trên mobile --}}
            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 lg:hidden"
                @click="sidebarOpen = true"
                aria-label="Mở menu"
            >
                <svg
                    class="h-6 w-6"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.8"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"
                    />
                </svg>
            </button>

            {{-- Tiêu đề --}}
            <div>
                <h1 class="text-lg font-semibold text-gray-900">
                    @yield('page-title', 'Trang quản trị')
                </h1>

                @hasSection('page-description')
                    <p class="hidden text-sm text-gray-500 sm:block">
                        @yield('page-description')
                    </p>
                @endif
            </div>

        </div>

        {{-- Bên phải --}}
        <div class="flex items-center gap-2 sm:gap-4">

            {{-- Link trở về website --}}
            <a
                href="{{ route('home') }}"
                target="_blank"
                class="hidden items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 sm:flex"
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
                        d="M13.5 4.5L21 12m0 0-7.5 7.5M21 12H3"
                    />
                </svg>

                Xem website
            </a>

            {{-- Chuông thông báo --}}
            @include('admin.partials.notification-bell')

            {{-- Tài khoản Admin --}}
            <div
                class="relative"
                @click.outside="profileOpen = false"
            >

                <button
                    type="button"
                    class="flex items-center gap-3 rounded-lg px-2 py-1.5 transition hover:bg-gray-100"
                    @click="profileOpen = !profileOpen"
                >
                    {{-- Avatar --}}
                    <div
                        class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-pink-100 text-sm font-semibold text-pink-600"
                    >
                        @if (auth()->user()?->avatar)
                            <img
                                src="{{ asset('storage/' . auth()->user()->avatar) }}"
                                alt="{{ auth()->user()->name }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            {{ strtoupper(mb_substr(auth()->user()?->name ?? 'A', 0, 1)) }}
                        @endif
                    </div>

                    {{-- Tên tài khoản --}}
                    <div class="hidden text-left md:block">
                        <p class="max-w-36 truncate text-sm font-semibold text-gray-900">
                            {{ auth()->user()?->name ?? 'Quản trị viên' }}
                        </p>

                        <p class="max-w-36 truncate text-xs text-gray-500">
                            {{ auth()->user()?->email ?? '' }}
                        </p>
                    </div>

                    {{-- Icon mũi tên --}}
                    <svg
                        class="hidden h-4 w-4 text-gray-500 md:block"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19.5 9.75L12 17.25 4.5 9.75"
                        />
                    </svg>
                </button>

                {{-- Dropdown --}}
                <div
                    x-show="profileOpen"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-56 origin-top-right overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg"
                    style="display: none;"
                >

                    {{-- Thông tin tài khoản --}}
                    <div class="border-b border-gray-100 px-4 py-3">
                        <p class="truncate text-sm font-semibold text-gray-900">
                            {{ auth()->user()?->name ?? 'Quản trị viên' }}
                        </p>

                        <p class="truncate text-xs text-gray-500">
                            {{ auth()->user()?->email ?? '' }}
                        </p>
                    </div>

                    <div class="p-2">

                        {{-- Hồ sơ --}}
                        <a
                            href="#"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-100"
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
                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.118a7.5 7.5 0 0115 0A17.933 17.933 0 0112 21.75a17.933 17.933 0 01-7.5-1.632z"
                                />
                            </svg>

                            Hồ sơ cá nhân
                        </a>

                        {{-- Đăng xuất --}}
                        <form
                            action="{{ route('logout') }}"
                            method="POST"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm text-red-600 transition hover:bg-red-50"
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
                                        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"
                                    />
                                </svg>

                                Đăng xuất
                            </button>
                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</header>
