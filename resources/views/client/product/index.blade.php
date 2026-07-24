@extends('client.layouts.master')

@section('title', 'Sản phẩm - Cosmetic Shop')

@section(
    'meta_description',
    'Khám phá các sản phẩm mỹ phẩm chăm sóc da, trang điểm và chăm sóc cơ thể chính hãng.'
)

@section('content')
    <section class="product-list-page">
        <div class="client-container">
            <nav
                class="product-list-page__breadcrumb"
                aria-label="Breadcrumb"
            >
                <a href="{{ route('home') }}">
                    Trang chủ
                </a>

                <span aria-hidden="true">/</span>

                <span>Sản phẩm</span>
            </nav>

            <header class="product-list-page__header">
                <div>
                    <p class="home-section__eyebrow">
                        Cosmetic Shop
                    </p>

                    <h1>Tất cả sản phẩm</h1>

                    <p>
                        Tìm thấy
                        <strong>{{ $products->total() }}</strong>
                        sản phẩm phù hợp.
                    </p>
                </div>
            </header>

            <div class="product-list-page__layout">
                <aside class="product-filter">
                    <div class="product-filter__heading">
                        <h2>Bộ lọc sản phẩm</h2>

                        @if (request()->query())
                            <a href="{{ route('products.index') }}">
                                Xóa bộ lọc
                            </a>
                        @endif
                    </div>

                    <form
                        action="{{ route('products.index') }}"
                        method="GET"
                        class="product-filter__form"
                    >
                        <div class="product-filter__group">
                            <label for="filter-keyword">
                                Từ khóa
                            </label>

                            <input
                                type="search"
                                id="filter-keyword"
                                name="keyword"
                                value="{{ $keyword }}"
                                placeholder="Tên sản phẩm, thương hiệu..."
                            >
                        </div>

                        <div class="product-filter__group">
                            <label for="filter-category">
                                Danh mục
                            </label>

                            <select
                                id="filter-category"
                                name="category"
                            >
                                <option value="">
                                    Tất cả danh mục
                                </option>

                                @foreach ($categories as $category)
                                    <option
                                        value="{{ $category->slug }}"
                                        @selected(
                                            $categorySlug
                                            === $category->slug
                                        )
                                    >
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="product-filter__group">
                            <label for="filter-brand">
                                Thương hiệu
                            </label>

                            <select
                                id="filter-brand"
                                name="brand"
                            >
                                <option value="">
                                    Tất cả thương hiệu
                                </option>

                                @foreach ($brands as $brand)
                                    <option
                                        value="{{ $brand->slug }}"
                                        @selected(
                                            $brandSlug === $brand->slug
                                        )
                                    >
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="product-filter__group">
                            <span class="product-filter__label">
                                Khoảng giá
                            </span>

                            <div class="product-filter__price">
                                <input
                                    type="number"
                                    name="min_price"
                                    value="{{ $minPrice }}"
                                    min="0"
                                    step="1000"
                                    placeholder="Từ"
                                    aria-label="Giá tối thiểu"
                                >

                                <span>—</span>

                                <input
                                    type="number"
                                    name="max_price"
                                    value="{{ $maxPrice }}"
                                    min="0"
                                    step="1000"
                                    placeholder="Đến"
                                    aria-label="Giá tối đa"
                                >
                            </div>
                        </div>

                        <input
                            type="hidden"
                            name="sort"
                            value="{{ $sort }}"
                        >

                        <input
                            type="hidden"
                            name="per_page"
                            value="{{ $perPage }}"
                        >

                        <button
                            type="submit"
                            class="product-filter__submit"
                        >
                            Áp dụng bộ lọc
                        </button>
                    </form>
                </aside>

                <div class="product-list-page__content">
                    <form
                        action="{{ route('products.index') }}"
                        method="GET"
                        class="product-toolbar"
                    >
                        @foreach (
                            request()->except([
                                'sort',
                                'per_page',
                                'page',
                            ])
                            as $name => $value
                        )
                            @if (!is_array($value))
                                <input
                                    type="hidden"
                                    name="{{ $name }}"
                                    value="{{ $value }}"
                                >
                            @endif
                        @endforeach

                        <label>
                            <span>Sắp xếp:</span>

                            <select
                                name="sort"
                                onchange="this.form.submit()"
                            >
                                <option
                                    value="newest"
                                    @selected($sort === 'newest')
                                >
                                    Mới nhất
                                </option>

                                <option
                                    value="price_asc"
                                    @selected($sort === 'price_asc')
                                >
                                    Giá tăng dần
                                </option>

                                <option
                                    value="price_desc"
                                    @selected($sort === 'price_desc')
                                >
                                    Giá giảm dần
                                </option>

                                <option
                                    value="best_selling"
                                    @selected(
                                        $sort === 'best_selling'
                                    )
                                >
                                    Bán chạy
                                </option>

                                <option
                                    value="featured"
                                    @selected($sort === 'featured')
                                >
                                    Nổi bật
                                </option>
                            </select>
                        </label>

                        <label>
                            <span>Hiển thị:</span>

                            <select
                                name="per_page"
                                onchange="this.form.submit()"
                            >
                                @foreach ([12, 24, 36] as $size)
                                    <option
                                        value="{{ $size }}"
                                        @selected($perPage === $size)
                                    >
                                        {{ $size }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </form>

                    @if ($products->isNotEmpty())
                        <div class="product-grid">
                            @foreach ($products as $product)
                                @include(
                                    'client.components.product-card',
                                    [
                                        'product' => $product,
                                        'badge' => $product->is_featured
                                            ? 'Nổi bật'
                                            : null,
                                    ]
                                )
                            @endforeach
                        </div>

                        <div class="product-pagination">
                            {{ $products->links() }}
                        </div>
                    @else
                        <div class="product-empty">
                            <span aria-hidden="true">🔎</span>

                            <h2>Không tìm thấy sản phẩm</h2>

                            <p>
                                Hãy thử thay đổi từ khóa hoặc bộ lọc
                                để xem thêm sản phẩm.
                            </p>

                            <a href="{{ route('products.index') }}">
                                Xem tất cả sản phẩm
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
