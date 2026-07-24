<nav class="client-navbar">
    <div class="client-container client-navbar__inner">
        <button type="button" class="client-navbar__category-button" aria-label="Mở danh mục sản phẩm" data-category-menu-button>
            <span>☰</span>
            <span>Danh mục sản phẩm</span>
        </button>

        <div class="client-navbar__menu">
            <a href="{{ route('home') }}" class="client-navbar__link">Trang chủ</a>
            <a href="{{ route('products.index') }}" class="client-navbar__link">Sản phẩm</a>

            @foreach (($navbarCategories ?? collect())->take(3) as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="client-navbar__link">
                    {{ $category->name }}
                </a>
            @endforeach

            <a href="{{ route('products.index', ['sort' => 'featured']) }}" class="client-navbar__link">Thương hiệu</a>
            <a href="{{ route('products.index', ['sort' => 'featured']) }}" class="client-navbar__link client-navbar__link--sale">Khuyến mãi</a>
            <a href="{{ route('contact.create') }}" class="client-navbar__link">Liên hệ</a>
        </div>

        <button type="button" class="client-navbar__mobile-toggle" aria-label="Mở menu" data-mobile-menu-button>☰</button>
    </div>

    <div class="client-navbar__mobile-menu" data-mobile-menu hidden>
        <a href="{{ route('home') }}">Trang chủ</a>
        <a href="{{ route('products.index') }}">Sản phẩm</a>
        @foreach (($navbarCategories ?? collect()) as $category)
            <a href="{{ route('products.index', ['category' => $category->slug]) }}">{{ $category->name }}</a>
        @endforeach
        <a href="{{ route('products.index', ['sort' => 'featured']) }}">Khuyến mãi</a>
        <a href="{{ route('contact.create') }}">Liên hệ</a>
    </div>

    @if (($navbarCategories ?? collect())->isNotEmpty())
        <div class="client-container" data-category-menu hidden>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;padding:16px 0;">
                @foreach ($navbarCategories as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="client-navbar__link">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const button = document.querySelector('[data-category-menu-button]');
        const menu = document.querySelector('[data-category-menu]');
        if (button && menu) {
            button.addEventListener('click', function () {
                menu.hidden = !menu.hidden;
            });
        }
    });
</script>
