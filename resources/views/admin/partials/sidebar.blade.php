@php
    /*
    |--------------------------------------------------------------------------
    | Helper menu
    |--------------------------------------------------------------------------
    |
    | Route chưa tồn tại sẽ được khóa tạm thời để sidebar không gây ra
    | RouteNotFoundException.
    |
    */

    $menuUrl = static function (string $routeName): string {
        return \Illuminate\Support\Facades\Route::has($routeName)
            ? route($routeName)
            : '#';
    };

    $menuClass = static function (
        string|array $patterns,
        string $routeName
    ): string {
        $patterns = (array) $patterns;

        if (! \Illuminate\Support\Facades\Route::has($routeName)) {
            return 'text-gray-300 cursor-not-allowed pointer-events-none';
        }

        return request()->routeIs(...$patterns)
            ? 'bg-pink-100 text-pink-600'
            : 'text-gray-700 hover:bg-gray-100';
    };
@endphp

<aside
    :class="
        sidebarOpen
            ? 'translate-x-0'
            : '-translate-x-full lg:translate-x-0'
    "
    class="
        fixed inset-y-0 left-0 z-50
        w-64 overflow-y-auto
        border-r border-gray-200 bg-white
        transition-transform duration-300
    "
>
    {{-- Logo --}}
    <div
        class="
            flex h-16 items-center justify-center
            border-b border-gray-200
        "
    >
        <a
            href="{{ $menuUrl('admin.dashboard') }}"
            class="text-xl font-bold text-pink-600"
        >
            Cosmetic Shop
        </a>
    </div>

    <nav class="space-y-6 px-3 py-4">

        {{-- ============================================================= --}}
        {{-- Tổng quan --}}
        {{-- ============================================================= --}}

        <div>
            <p
                class="
                    mb-2 px-3 text-xs font-semibold
                    uppercase tracking-wider text-gray-400
                "
            >
                Tổng quan
            </p>

            <a
                href="{{ $menuUrl('admin.dashboard') }}"
                class="
                    flex items-center rounded-lg px-3 py-2
                    text-sm font-medium transition
                    {{ $menuClass(
                        ['admin.dashboard'],
                        'admin.dashboard'
                    ) }}
                "
            >
                <span aria-hidden="true">
                    📊
                </span>

                <span class="ml-3">
                    Dashboard
                </span>
            </a>
        </div>

        {{-- ============================================================= --}}
        {{-- Bán hàng --}}
        {{-- ============================================================= --}}

        <div>
            <p
                class="
                    mb-2 px-3 text-xs font-semibold
                    uppercase tracking-wider text-gray-400
                "
            >
                Bán hàng
            </p>

            <div class="space-y-1">

                {{-- Đơn hàng --}}
                <a
                    href="{{ $menuUrl('admin.orders.index') }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.orders.*'],
                            'admin.orders.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.orders.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        🛒
                    </span>

                    <span class="ml-3">
                        Quản lý đơn hàng
                    </span>
                </a>

                {{-- Vận chuyển --}}
                <a
                    href="{{ $menuUrl('admin.shipments.index') }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.shipments.*'],
                            'admin.shipments.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.shipments.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        🚚
                    </span>

                    <span class="ml-3">
                        Vận chuyển
                    </span>
                </a>

                {{-- Thanh toán --}}
                <a
                    href="{{ $menuUrl('admin.payments.index') }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.payments.*'],
                            'admin.payments.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.payments.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        💳
                    </span>

                    <span class="ml-3">
                        Thanh toán
                    </span>
                </a>

                {{-- Trả hàng / hoàn tiền --}}
                <a
                    href="{{ $menuUrl(
                        'admin.return-requests.index'
                    ) }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.return-requests.*'],
                            'admin.return-requests.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.return-requests.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        ↩️
                    </span>

                    <span class="ml-3">
                        Trả hàng / Hoàn tiền
                    </span>
                </a>

            </div>
        </div>

       {{-- ============================================================= --}}
        {{-- lịch sử đăng nhập users  --}}
        {{-- ============================================================= --}}
        <a
    href="{{ $menuUrl('admin.login-histories.index') }}"
    class="
        flex items-center rounded-lg px-3 py-2
        text-sm font-medium transition
        {{ $menuClass(
            ['admin.login-histories.*'],
            'admin.login-histories.index'
        ) }}
    "
>
    <span aria-hidden="true">🛡️</span>

    <span class="ml-3">
        Lịch sử đăng nhập
    </span>
</a>

<a
    href="{{ $menuUrl('admin.search-histories.index') }}"
    class="
        flex items-center rounded-lg px-3 py-2
        text-sm font-medium transition
        {{ $menuClass(
            ['admin.search-histories.*'],
            'admin.search-histories.index'
        ) }}
    "
>
    <span aria-hidden="true">🔎</span>

    <span class="ml-3">
        Lịch sử tìm kiếm
    </span>
</a>
{{-- ============================================================= --}}
        {{-- trò chuyện với khách trực tiếp  --}}
        {{-- ============================================================= --}}
<a
    href="{{ $menuUrl('admin.support-chats.index') }}"
    class="
        flex items-center rounded-lg px-3 py-2
        text-sm font-medium transition
        {{ $menuClass(
            ['admin.support-chats.*'],
            'admin.support-chats.index'
        ) }}
    "
>
    <span aria-hidden="true">💬</span>

    <span class="ml-3">
        Chat khách hàng
    </span>
</a>
        {{-- ============================================================= --}}
        {{-- vai trò và phân quyền  --}}
        {{-- ============================================================= --}}
        <div>
    <p
        class="
            mb-2 px-3 text-xs font-semibold
            uppercase tracking-wider text-gray-400
        "
    >
        Tài khoản và quyền
    </p>

    <div class="space-y-1">
        <a
            href="{{ $menuUrl('admin.staff.index') }}"
            class="
                flex items-center rounded-lg px-3 py-2
                text-sm font-medium transition
                {{ $menuClass(
                    ['admin.staff.*'],
                    'admin.staff.index'
                ) }}
            "
        >
            <span aria-hidden="true">👨‍💼</span>

            <span class="ml-3">
                Nhân viên
            </span>
        </a>

        <a
            href="{{ $menuUrl('admin.roles.index') }}"
            class="
                flex items-center rounded-lg px-3 py-2
                text-sm font-medium transition
                {{ $menuClass(
                    ['admin.roles.*'],
                    'admin.roles.index'
                ) }}
            "
        >
            <span aria-hidden="true">🔐</span>

            <span class="ml-3">
                Vai trò và phân quyền
            </span>
        </a>
    </div>
</div>
        {{-- ============================================================= --}}
        {{-- thuộc tính biến thể  --}}
        {{-- ============================================================= --}}
        <a
    href="{{ $menuUrl('admin.variant-attributes.index') }}"
    class="
        flex items-center rounded-lg px-3 py-2
        text-sm font-medium transition
        {{ $menuClass(
            ['admin.variant-attributes.*', 'admin.variant-values.*'],
            'admin.variant-attributes.index'
        ) }}
    "
>
    <span aria-hidden="true">🎨</span>

    <span class="ml-3">
        Thuộc tính biến thể
    </span>
</a>
        {{-- ============================================================= --}}
        {{-- Sản phẩm --}}
        {{-- ============================================================= --}}

        <div>
            <p
                class="
                    mb-2 px-3 text-xs font-semibold
                    uppercase tracking-wider text-gray-400
                "
            >
                Sản phẩm
            </p>

            <div class="space-y-1">

                {{-- Sản phẩm --}}
                <a
                    href="{{ $menuUrl('admin.products.index') }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.products.*'],
                            'admin.products.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.products.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        📦
                    </span>

                    <span class="ml-3">
                        Sản phẩm
                    </span>
                </a>

                {{-- Danh mục --}}
                <a
                    href="{{ $menuUrl('admin.categories.index') }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.categories.*'],
                            'admin.categories.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.categories.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        🗂️
                    </span>

                    <span class="ml-3">
                        Danh mục
                    </span>
                </a>

                {{-- Thương hiệu --}}
                <a
                    href="{{ $menuUrl('admin.brands.index') }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.brands.*'],
                            'admin.brands.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.brands.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        🏷️
                    </span>

                    <span class="ml-3">
                        Thương hiệu
                    </span>
                </a>

                {{-- Nhà cung cấp --}}
                <a
                    href="{{ $menuUrl('admin.suppliers.index') }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.suppliers.*'],
                            'admin.suppliers.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.suppliers.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        🚛
                    </span>

                    <span class="ml-3">
                        Nhà cung cấp
                    </span>
                </a>

            </div>
        </div>

        {{-- ============================================================= --}}
        {{-- Kho --}}
        {{-- ============================================================= --}}

        <div>
            <p
                class="
                    mb-2 px-3 text-xs font-semibold
                    uppercase tracking-wider text-gray-400
                "
            >
                Kho
            </p>

            <div class="space-y-1">

                {{-- Kho hàng --}}
                <a
                    href="{{ $menuUrl('admin.warehouses.index') }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.warehouses.*'],
                            'admin.warehouses.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.warehouses.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        🏬
                    </span>

                    <span class="ml-3">
                        Kho hàng
                    </span>
                </a>

                {{-- Tồn kho --}}
                <a
                    href="{{ $menuUrl('admin.inventories.index') }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.inventories.*'],
                            'admin.inventories.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.inventories.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        📈
                    </span>

                    <span class="ml-3">
                        Tồn kho
                    </span>
                </a>

            </div>
        </div>

        {{-- ============================================================= --}}
        {{-- Khách hàng --}}
        {{-- ============================================================= --}}

        <div>
            <p
                class="
                    mb-2 px-3 text-xs font-semibold
                    uppercase tracking-wider text-gray-400
                "
            >
                Khách hàng
            </p>

            <div class="space-y-1">

                {{-- Khách hàng --}}
                <a
                    href="{{ $menuUrl('admin.customers.index') }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.customers.*'],
                            'admin.customers.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.customers.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        👤
                    </span>

                    <span class="ml-3">
                        Khách hàng
                    </span>
                </a>

                {{-- Loyalty --}}
                <a
                    href="{{ $menuUrl('admin.loyalty.index') }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.loyalty.*'],
                            'admin.loyalty.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.loyalty.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        ⭐
                    </span>

                    <span class="ml-3">
                        Loyalty
                    </span>
                </a>

            </div>
        </div>

        {{-- Trang nội dung --}}
<a
    href="{{ $menuUrl('admin.pages.index') }}"
    class="
        flex items-center rounded-lg px-3 py-2
        text-sm font-medium transition
        {{ $menuClass(
            ['admin.pages.*'],
            'admin.pages.index'
        ) }}
    "
>
    <span aria-hidden="true">
        📄
    </span>

    <span class="ml-3">
        Trang nội dung
    </span>
</a>
        {{-- ============================================================= --}}
        {{-- Nội dung --}}
        {{-- ============================================================= --}}

        <div>
            <p
                class="
                    mb-2 px-3 text-xs font-semibold
                    uppercase tracking-wider text-gray-400
                "
            >
                Nội dung
            </p>

            <div class="space-y-1">

                {{-- Đánh giá --}}
                <a
                    href="{{ $menuUrl('admin.reviews.index') }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.reviews.*'],
                            'admin.reviews.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.reviews.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        ⭐
                    </span>

                    <span class="ml-3">
                        Đánh giá
                    </span>
                </a>
{{-- phương thức vận chuyển --}}
                <a
    href="{{ $menuUrl('admin.shipping-methods.index') }}"
    class="
        flex items-center rounded-lg px-3 py-2
        text-sm font-medium transition
        {{ $menuClass(
            ['admin.shipping-methods.*'],
            'admin.shipping-methods.index'
        ) }}
    "
>
    <span aria-hidden="true">🚚</span>

    <span class="ml-3">
        Phương thức vận chuyển
    </span>
</a>
                {{-- Hỏi đáp --}}
<a
    href="{{ $menuUrl('admin.questions.index') }}"
    class="
        flex items-center rounded-lg px-3 py-2
        text-sm font-medium transition
        {{ $menuClass(
            ['admin.questions.*'],
            'admin.questions.index'
        ) }}
    "
    @if (! \Illuminate\Support\Facades\Route::has(
        'admin.questions.index'
    ))
        title="Chức năng chưa được xây dựng"
    @endif
>
    <span aria-hidden="true">
        ❓
    </span>

    <span class="ml-3">
        Hỏi đáp
    </span>
</a>


                {{-- Liên hệ --}}
<a
    href="{{ $menuUrl('admin.contact-messages.index') }}"
    class="
        flex items-center rounded-lg px-3 py-2
        text-sm font-medium transition
        {{ $menuClass(
            ['admin.contact-messages.*'],
            'admin.contact-messages.index'
        ) }}
    "
    @if (! \Illuminate\Support\Facades\Route::has(
        'admin.contact-messages.index'
    ))
        title="Chức năng chưa được xây dựng"
    @endif
>
    <span aria-hidden="true">
        📩
    </span>

    <span class="ml-3">
        Liên hệ
    </span>
</a>

            </div>
        </div>

        {{-- ============================================================= --}}
        {{-- Marketing --}}
        {{-- ============================================================= --}}

        <div>
            <p
                class="
                    mb-2 px-3 text-xs font-semibold
                    uppercase tracking-wider text-gray-400
                "
            >
                Marketing
            </p>

            <div class="space-y-1">

                {{-- Coupon --}}
                <a
                    href="{{ $menuUrl('admin.coupons.index') }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.coupons.*'],
                            'admin.coupons.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.coupons.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        🎟️
                    </span>

                    <span class="ml-3">
                        Coupon
                    </span>
                </a>

                {{-- Campaign --}}
                <a
                    href="{{ $menuUrl(
                        'admin.discount-campaigns.index'
                    ) }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.discount-campaigns.*'],
                            'admin.discount-campaigns.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.discount-campaigns.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        🎯
                    </span>

                    <span class="ml-3">
                        Campaign
                    </span>
                </a>

                {{-- Banner --}}
<a
    href="{{ $menuUrl('admin.banners.index') }}"
    class="
        flex items-center rounded-lg px-3 py-2
        text-sm font-medium transition
        {{ $menuClass(
            ['admin.banners.*'],
            'admin.banners.index'
        ) }}
    "
    @if (! \Illuminate\Support\Facades\Route::has(
        'admin.banners.index'
    ))
        title="Chức năng chưa được xây dựng"
    @endif
>
    <span aria-hidden="true">
        🖼️
    </span>

    <span class="ml-3">
        Banner
    </span>
</a>
                {{-- Newsletter --}}
                <a
                    href="{{ $menuUrl(
                        'admin.newsletter-subscribers.index'
                    ) }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.newsletter-subscribers.*'],
                            'admin.newsletter-subscribers.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.newsletter-subscribers.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        📰
                    </span>

                    <span class="ml-3">
                        Newsletter
                    </span>
                </a>

            </div>
        </div>

        {{-- ============================================================= --}}
        {{-- Hệ thống --}}
        {{-- ============================================================= --}}

        <div>
            <p
                class="
                    mb-2 px-3 text-xs font-semibold
                    uppercase tracking-wider text-gray-400
                "
            >
                Hệ thống
            </p>

            <div class="space-y-1">

                {{-- Cài đặt --}}
                <a
                    href="{{ $menuUrl('admin.settings.index') }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.settings.*'],
                            'admin.settings.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.settings.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        ⚙️
                    </span>

                    <span class="ml-3">
                        Cài đặt
                    </span>
                </a>

                {{-- Thống kê --}}
                <a
                    href="{{ $menuUrl('admin.statistics.index') }}"
                    class="
                        flex items-center rounded-lg px-3 py-2
                        text-sm font-medium transition
                        {{ $menuClass(
                            ['admin.statistics.*'],
                            'admin.statistics.index'
                        ) }}
                    "
                    @if (! \Illuminate\Support\Facades\Route::has(
                        'admin.statistics.index'
                    ))
                        title="Chức năng chưa được xây dựng"
                    @endif
                >
                    <span aria-hidden="true">
                        📊
                    </span>

                    <span class="ml-3">
                        Thống kê
                    </span>
                </a>

                {{-- Nhật ký quản trị --}}
<a
    href="{{ $menuUrl('admin.audit-logs.index') }}"
    class="
        flex items-center rounded-lg px-3 py-2
        text-sm font-medium transition
        {{ $menuClass(
            ['admin.audit-logs.*'],
            'admin.audit-logs.index'
        ) }}
    "
    @if (! \Illuminate\Support\Facades\Route::has(
        'admin.audit-logs.index'
    ))
        title="Chức năng chưa được xây dựng"
    @endif
>
    <span aria-hidden="true">
        📑
    </span>

    <span class="ml-3">
        Nhật ký quản trị
    </span>
</a>
            </div>
        </div>

    </nav>
</aside>
