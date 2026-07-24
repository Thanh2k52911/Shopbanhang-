@extends('admin.layouts.master')

@section('title', 'Quản lý sản phẩm')

@section('page-title', 'Quản lý sản phẩm')

@section(
    'page-description',
    'Theo dõi sản phẩm, SKU, giá bán và tồn kho trong hệ thống.'
)

@section('content')
    <div class="space-y-6">

        {{-- Header thao tác --}}
        <div
            class="
                flex flex-col gap-4
                sm:flex-row sm:items-center sm:justify-between
            "
        >
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    Danh sách sản phẩm
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Quản lý toàn bộ sản phẩm đang có trong cửa hàng.
                </p>
            </div>

            @if (
                \Illuminate\Support\Facades\Route::has(
                    'admin.products.create'
                )
            )
                <a
                    href="{{ route('admin.products.create') }}"
                    class="
                        inline-flex items-center justify-center
                        rounded-lg bg-pink-600 px-4 py-2.5
                        text-sm font-semibold text-white
                        transition hover:bg-pink-700
                    "
                >
                    + Thêm sản phẩm
                </a>
            @endif
        </div>

        {{-- Thống kê --}}
        <div
            class="
                grid grid-cols-1 gap-4
                sm:grid-cols-2 xl:grid-cols-5
            "
        >
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    Tổng sản phẩm
                </p>

                <strong class="mt-2 block text-3xl text-gray-900">
                    {{ number_format($statistics['total']) }}
                </strong>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    Đang bán
                </p>

                <strong class="mt-2 block text-3xl text-green-600">
                    {{ number_format($statistics['active']) }}
                </strong>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    Ngừng bán
                </p>

                <strong class="mt-2 block text-3xl text-red-500">
                    {{ number_format($statistics['inactive']) }}
                </strong>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    Nổi bật
                </p>

                <strong class="mt-2 block text-3xl text-pink-600">
                    {{ number_format($statistics['featured']) }}
                </strong>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    Hết hàng
                </p>

                <strong class="mt-2 block text-3xl text-orange-600">
                    {{ number_format($statistics['out_of_stock']) }}
                </strong>
            </div>
        </div>

        {{-- Bộ lọc --}}
        <form
            method="GET"
            action="{{ route('admin.products.index') }}"
            class="rounded-xl border border-gray-200 bg-white p-5"
        >
            <div
                class="
                    grid grid-cols-1 gap-4
                    md:grid-cols-2 xl:grid-cols-6
                "
            >
                <div class="xl:col-span-2">
                    <label
                        for="product-keyword"
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Tìm kiếm
                    </label>

                    <input
                        id="product-keyword"
                        type="text"
                        name="keyword"
                        value="{{ request('keyword') }}"
                        placeholder="Tên, slug hoặc mã SKU..."
                        class="
                            w-full rounded-lg border border-gray-300
                            px-4 py-2.5 text-sm
                            focus:border-pink-500 focus:ring-pink-500
                        "
                    >
                </div>

                <div>
                    <label
                        for="product-category"
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Danh mục
                    </label>

                    <select
                        id="product-category"
                        name="category_id"
                        class="
                            w-full rounded-lg border border-gray-300
                            px-3 py-2.5 text-sm
                            focus:border-pink-500 focus:ring-pink-500
                        "
                    >
                        <option value="">
                            Tất cả danh mục
                        </option>

                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected(
                                    (string) request('category_id')
                                    === (string) $category->id
                                )
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        for="product-brand"
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Thương hiệu
                    </label>

                    <select
                        id="product-brand"
                        name="brand_id"
                        class="
                            w-full rounded-lg border border-gray-300
                            px-3 py-2.5 text-sm
                            focus:border-pink-500 focus:ring-pink-500
                        "
                    >
                        <option value="">
                            Tất cả thương hiệu
                        </option>

                        @foreach ($brands as $brand)
                            <option
                                value="{{ $brand->id }}"
                                @selected(
                                    (string) request('brand_id')
                                    === (string) $brand->id
                                )
                            >
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        for="product-supplier"
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Nhà cung cấp
                    </label>

                    <select
                        id="product-supplier"
                        name="supplier_id"
                        class="
                            w-full rounded-lg border border-gray-300
                            px-3 py-2.5 text-sm
                            focus:border-pink-500 focus:ring-pink-500
                        "
                    >
                        <option value="">
                            Tất cả nhà cung cấp
                        </option>

                        @foreach ($suppliers as $supplier)
                            <option
                                value="{{ $supplier->id }}"
                                @selected(
                                    (string) request('supplier_id')
                                    === (string) $supplier->id
                                )
                            >
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        for="product-status"
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Trạng thái
                    </label>

                    <select
                        id="product-status"
                        name="status"
                        class="
                            w-full rounded-lg border border-gray-300
                            px-3 py-2.5 text-sm
                            focus:border-pink-500 focus:ring-pink-500
                        "
                    >
                        <option value="">
                            Tất cả trạng thái
                        </option>

                        <option
                            value="1"
                            @selected(request('status') === '1')
                        >
                            Đang bán
                        </option>

                        <option
                            value="0"
                            @selected(request('status') === '0')
                        >
                            Ngừng bán
                        </option>
                    </select>
                </div>
            </div>

            <div
                class="
                    mt-4 flex flex-col gap-3
                    sm:flex-row sm:items-center sm:justify-end
                "
            >
                <a
                    href="{{ route('admin.products.index') }}"
                    class="
                        inline-flex items-center justify-center
                        rounded-lg border border-gray-300
                        px-4 py-2.5 text-sm font-semibold text-gray-700
                        transition hover:bg-gray-50
                    "
                >
                    Xóa bộ lọc
                </a>

                <button
                    type="submit"
                    class="
                        inline-flex items-center justify-center
                        rounded-lg bg-pink-600 px-5 py-2.5
                        text-sm font-semibold text-white
                        transition hover:bg-pink-700
                    "
                >
                    Lọc sản phẩm
                </button>
            </div>
        </form>

        {{-- Danh sách sản phẩm --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">

            {{-- Tiêu đề bảng --}}
            <div
                class="
                    flex flex-col gap-3 border-b border-gray-200 px-5 py-4
                    sm:flex-row sm:items-center sm:justify-between
                "
            >
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        Danh sách sản phẩm
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Tìm thấy
                        <strong>{{ number_format($products->total()) }}</strong>
                        sản phẩm.
                    </p>
                </div>

                {{-- Sắp xếp --}}
                <form method="GET" class="flex items-center gap-2">
                    @foreach (
                        request()->except(['sort', 'page'])
                        as $key => $value
                    )
                        @if (is_array($value))
                            @foreach ($value as $item)
                                <input
                                    type="hidden"
                                    name="{{ $key }}[]"
                                    value="{{ $item }}"
                                >
                            @endforeach
                        @else
                            <input
                                type="hidden"
                                name="{{ $key }}"
                                value="{{ $value }}"
                            >
                        @endif
                    @endforeach

                    <label
                        for="product-sort"
                        class="whitespace-nowrap text-sm font-medium text-gray-600"
                    >
                        Sắp xếp:
                    </label>

                    <select
                        id="product-sort"
                        name="sort"
                        onchange="this.form.submit()"
                        class="
                            rounded-lg border border-gray-300 px-3 py-2
                            text-sm text-gray-700
                            focus:border-pink-500 focus:ring-pink-500
                        "
                    >
                        <option
                            value=""
                            @selected(
                                request('sort') === null
                                || request('sort') === ''
                            )
                        >
                            Mới nhất
                        </option>

                        <option
                            value="oldest"
                            @selected(request('sort') === 'oldest')
                        >
                            Cũ nhất
                        </option>

                        <option
                            value="name_asc"
                            @selected(request('sort') === 'name_asc')
                        >
                            Tên A → Z
                        </option>

                        <option
                            value="name_desc"
                            @selected(request('sort') === 'name_desc')
                        >
                            Tên Z → A
                        </option>

                        <option
                            value="price_asc"
                            @selected(request('sort') === 'price_asc')
                        >
                            Giá thấp → cao
                        </option>

                        <option
                            value="price_desc"
                            @selected(request('sort') === 'price_desc')
                        >
                            Giá cao → thấp
                        </option>

                        <option
                            value="view_desc"
                            @selected(request('sort') === 'view_desc')
                        >
                            Lượt xem cao nhất
                        </option>
                    </select>
                </form>
            </div>

            {{-- Bảng --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="
                                    px-5 py-3 text-left text-xs font-semibold
                                    uppercase tracking-wider text-gray-500
                                "
                            >
                                Sản phẩm
                            </th>

                            <th
                                class="
                                    px-5 py-3 text-left text-xs font-semibold
                                    uppercase tracking-wider text-gray-500
                                "
                            >
                                Phân loại
                            </th>

                            <th
                                class="
                                    px-5 py-3 text-left text-xs font-semibold
                                    uppercase tracking-wider text-gray-500
                                "
                            >
                                Giá bán
                            </th>

                            <th
                                class="
                                    px-5 py-3 text-center text-xs font-semibold
                                    uppercase tracking-wider text-gray-500
                                "
                            >
                                SKU
                            </th>

                            <th
                                class="
                                    px-5 py-3 text-center text-xs font-semibold
                                    uppercase tracking-wider text-gray-500
                                "
                            >
                                Tồn kho
                            </th>

                            <th
                                class="
                                    px-5 py-3 text-center text-xs font-semibold
                                    uppercase tracking-wider text-gray-500
                                "
                            >
                                Trạng thái
                            </th>

                            <th
                                class="
                                    px-5 py-3 text-right text-xs font-semibold
                                    uppercase tracking-wider text-gray-500
                                "
                            >
                                Thao tác
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($products as $product)
                            @php
                                $minimumPrice = is_null(
                                    $product->minimum_price
                                )
                                    ? null
                                    : (float) $product->minimum_price;

                                $maximumPrice = is_null(
                                    $product->maximum_price
                                )
                                    ? null
                                    : (float) $product->maximum_price;

                                $availableQuantity =
                                    (int) $product->available_quantity;
                            @endphp

                            <tr class="transition hover:bg-gray-50">

                                {{-- Sản phẩm --}}
                                <td class="px-5 py-4">
                                    <div
                                        class="
                                            flex min-w-[300px]
                                            items-center gap-4
                                        "
                                    >
                                        <div
                                            class="
                                                flex h-16 w-16 shrink-0
                                                items-center justify-center
                                                overflow-hidden rounded-lg
                                                border border-gray-200
                                                bg-gray-50
                                            "
                                        >
                                            @if ($product->thumbnail)
    <img
        src="{{ asset(
            'storage/'
            . ltrim($product->thumbnail, '/')
        ) }}"
        alt="{{ $product->name }}"
        class="
            h-full w-full
            object-cover
        "
    >
@else
                                                <span class="text-2xl">
                                                    📦
                                                </span>
                                            @endif
                                        </div>

                                        <div class="min-w-0">
                                            <h3
                                                class="
                                                    max-w-[290px] truncate
                                                    font-semibold
                                                    text-gray-900
                                                "
                                                title="{{ $product->name }}"
                                            >
                                                {{ $product->name }}
                                            </h3>

                                            <p
                                                class="
                                                    mt-1 max-w-[290px]
                                                    truncate text-sm
                                                    text-gray-500
                                                "
                                                title="{{ $product->slug }}"
                                            >
                                                {{ $product->slug }}
                                            </p>

                                            <div
                                                class="
                                                    mt-2 flex flex-wrap
                                                    gap-2
                                                "
                                            >
                                                @if (
                                                    (bool) $product
                                                        ->is_featured
                                                )
                                                    <span
                                                        class="
                                                            rounded-full
                                                            bg-pink-100
                                                            px-2.5 py-1
                                                            text-xs
                                                            font-semibold
                                                            text-pink-700
                                                        "
                                                    >
                                                        Nổi bật
                                                    </span>
                                                @endif

                                                <span
                                                    class="
                                                        rounded-full
                                                        bg-gray-100
                                                        px-2.5 py-1
                                                        text-xs
                                                        text-gray-600
                                                    "
                                                >
                                                    👁
                                                    {{ number_format(
                                                        (int) $product
                                                            ->view_count
                                                    ) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Phân loại --}}
                                <td class="px-5 py-4">
                                    <div
                                        class="
                                            min-w-[220px]
                                            space-y-1.5 text-sm
                                        "
                                    >
                                        <p>
                                            <span class="text-gray-500">
                                                Danh mục:
                                            </span>

                                            <strong class="text-gray-800">
                                                {{ $product->category_name
                                                    ?: 'Chưa có' }}
                                            </strong>
                                        </p>

                                        <p>
                                            <span class="text-gray-500">
                                                Thương hiệu:
                                            </span>

                                            <strong class="text-gray-800">
                                                {{ $product->brand_name
                                                    ?: 'Chưa có' }}
                                            </strong>
                                        </p>

                                        <p
                                            class="max-w-[240px] truncate"
                                            title="{{ $product
                                                ->supplier_name }}"
                                        >
                                            <span class="text-gray-500">
                                                Nhà cung cấp:
                                            </span>

                                            <strong class="text-gray-800">
                                                {{ $product->supplier_name
                                                    ?: 'Chưa có' }}
                                            </strong>
                                        </p>
                                    </div>
                                </td>

                                {{-- Giá --}}
                                <td class="px-5 py-4">
                                    <div class="min-w-[145px]">
                                        @if (is_null($minimumPrice))
                                            <span
                                                class="
                                                    text-sm text-gray-400
                                                "
                                            >
                                                Chưa có giá
                                            </span>
                                        @elseif (
                                            $maximumPrice !== null
                                            && $minimumPrice
                                                !== $maximumPrice
                                        )
                                            <strong class="text-pink-600">
                                                {{ number_format(
                                                    $minimumPrice,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}đ
                                            </strong>

                                            <span
                                                class="
                                                    block text-xs
                                                    text-gray-500
                                                "
                                            >
                                                đến
                                                {{ number_format(
                                                    $maximumPrice,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}đ
                                            </span>
                                        @else
                                            <strong class="text-pink-600">
                                                {{ number_format(
                                                    $minimumPrice,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}đ
                                            </strong>
                                        @endif
                                    </div>
                                </td>

                                {{-- SKU --}}
                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="
                                            inline-flex min-w-10
                                            justify-center rounded-full
                                            bg-blue-50 px-3 py-1
                                            text-sm font-semibold
                                            text-blue-700
                                        "
                                    >
                                        {{ number_format(
                                            (int) $product->sku_count
                                        ) }}
                                    </span>
                                </td>

                                {{-- Tồn kho --}}
                                <td class="px-5 py-4 text-center">
                                    @if ($availableQuantity <= 0)
                                        <span
                                            class="
                                                inline-flex rounded-full
                                                bg-red-100 px-3 py-1
                                                text-xs font-semibold
                                                text-red-700
                                            "
                                        >
                                            Hết hàng
                                        </span>
                                    @elseif ($availableQuantity <= 10)
                                        <div>
                                            <strong
                                                class="text-orange-600"
                                            >
                                                {{ number_format(
                                                    $availableQuantity
                                                ) }}
                                            </strong>

                                            <span
                                                class="
                                                    mt-1 block text-xs
                                                    text-orange-500
                                                "
                                            >
                                                Sắp hết
                                            </span>
                                        </div>
                                    @else
                                        <strong class="text-green-600">
                                            {{ number_format(
                                                $availableQuantity
                                            ) }}
                                        </strong>
                                    @endif
                                </td>

                                {{-- Trạng thái --}}
                                <td class="px-5 py-4 text-center">
                                    <div class="min-w-[110px] space-y-2">
                                        @if ((bool) $product->status)
                                            <span
                                                class="
                                                    inline-flex rounded-full
                                                    bg-green-100 px-3 py-1
                                                    text-xs font-semibold
                                                    text-green-700
                                                "
                                            >
                                                Đang bán
                                            </span>
                                        @else
                                            <span
                                                class="
                                                    inline-flex rounded-full
                                                    bg-gray-100 px-3 py-1
                                                    text-xs font-semibold
                                                    text-gray-600
                                                "
                                            >
                                                Ngừng bán
                                            </span>
                                        @endif

                                        <p class="text-xs text-gray-400">
                                            {{ \Carbon\Carbon::parse(
                                                $product->created_at
                                            )->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </td>

                                {{-- Thao tác --}}
                                <td class="px-5 py-4 text-right">
                                    <div
    class="
        flex min-w-[210px]
        items-center justify-end gap-2
    "
>
                                        <a
                                            href="{{ route(
                                                'products.show',
                                                $product->slug
                                            ) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="
                                                inline-flex h-9 w-9
                                                items-center justify-center
                                                rounded-lg border
                                                border-gray-300
                                                text-gray-600 transition
                                                hover:border-blue-400
                                                hover:bg-blue-50
                                                hover:text-blue-600
                                            "
                                            title="Xem trên website"
                                        >
                                            👁
                                        </a>

                                        @if (
                                            \Illuminate\Support\Facades\Route::has(
                                                'admin.products.show'
                                            )
                                        )
                                            <a
                                                href="{{ route(
                                                    'admin.products.show',
                                                    $product->id
                                                ) }}"
                                                class="
                                                    inline-flex h-9 w-9
                                                    items-center
                                                    justify-center
                                                    rounded-lg border
                                                    border-gray-300
                                                    text-gray-600
                                                    transition
                                                    hover:border-pink-400
                                                    hover:bg-pink-50
                                                    hover:text-pink-600
                                                "
                                                title="Chi tiết"
                                            >
                                                📄
                                            </a>
                                        @endif

                                        @if (
                                            \Illuminate\Support\Facades\Route::has(
                                                'admin.products.edit'
                                            )
                                        )
                                            <a
                                                href="{{ route(
                                                    'admin.products.edit',
                                                    $product->id
                                                ) }}"
                                                class="
                                                    inline-flex h-9 w-9
                                                    items-center
                                                    justify-center
                                                    rounded-lg border
                                                    border-gray-300
                                                    text-gray-600
                                                    transition
                                                    hover:border-orange-400
                                                    hover:bg-orange-50
                                                    hover:text-orange-600
                                                "
                                                title="Chỉnh sửa"
                                            >
                                                ✏️
                                            </a>
                                        @endif
                                        <form
    method="POST"
    action="{{ route('admin.products.destroy', $product->id) }}"
    class="inline-block"
    onsubmit="return confirm('Bạn chắc chắn muốn xóa sản phẩm này?');"
>
    @csrf
    @method('DELETE')

    <button
    type="submit"
    title="Xóa sản phẩm"
    class="
        inline-flex h-9 px-3
        items-center justify-center
        rounded-lg border border-red-200
        bg-red-50
        text-xs font-bold text-red-600
        transition hover:bg-red-100
    "
>
        🗑️

</button>
</form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="7"
                                    class="px-5 py-16 text-center"
                                >
                                    <div class="mx-auto max-w-md">
                                        <div class="text-5xl">
                                            📦
                                        </div>

                                        <h3
                                            class="
                                                mt-4 text-lg font-bold
                                                text-gray-800
                                            "
                                        >
                                            Không tìm thấy sản phẩm
                                        </h3>

                                        <p
                                            class="
                                                mt-2 text-sm
                                                text-gray-500
                                            "
                                        >
                                            Không có sản phẩm phù hợp với
                                            bộ lọc hiện tại.
                                        </p>

                                        <a
                                            href="{{ route(
                                                'admin.products.index'
                                            ) }}"
                                            class="
                                                mt-5 inline-flex
                                                rounded-lg bg-pink-600
                                                px-4 py-2 text-sm
                                                font-semibold text-white
                                                hover:bg-pink-700
                                            "
                                        >
                                            Xóa bộ lọc
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Phân trang --}}
            @if ($products->hasPages())
                <div class="border-t border-gray-200 px-5 py-4">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
