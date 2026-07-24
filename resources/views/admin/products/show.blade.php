@extends('admin.layouts.master')

@section('title', 'Chi tiết sản phẩm')

@section('page-title', 'Chi tiết sản phẩm')

@section(
    'page-description',
    'Theo dõi thông tin, hình ảnh, SKU, giá bán và tồn kho của sản phẩm.'
)

@section('content')
    @php
        $minimumPrice = is_null($statistics['minimum_price'])
            ? null
            : (float) $statistics['minimum_price'];

        $maximumPrice = is_null($statistics['maximum_price'])
            ? null
            : (float) $statistics['maximum_price'];
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div
            class="
                flex flex-col gap-4
                lg:flex-row lg:items-center lg:justify-between
            "
        >
            <div class="flex min-w-0 items-center gap-4">
                <a
                    href="{{ route('admin.products.index') }}"
                    class="
                        inline-flex h-11 w-11 shrink-0 items-center
                        justify-center rounded-lg border border-gray-300
                        bg-white text-xl text-gray-600 transition
                        hover:bg-gray-50
                    "
                    title="Quay lại"
                >
                    ←
                </a>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2
                            class="
                                max-w-4xl truncate text-2xl
                                font-bold text-gray-900
                            "
                            title="{{ $product->name }}"
                        >
                            {{ $product->name }}
                        </h2>

                        @if ((bool) $product->status)
                            <span
                                class="
                                    rounded-full bg-green-100 px-3 py-1
                                    text-xs font-semibold text-green-700
                                "
                            >
                                Đang bán
                            </span>
                        @else
                            <span
                                class="
                                    rounded-full bg-gray-100 px-3 py-1
                                    text-xs font-semibold text-gray-600
                                "
                            >
                                Ngừng bán
                            </span>
                        @endif

                        @if ((bool) $product->is_featured)
                            <span
                                class="
                                    rounded-full bg-pink-100 px-3 py-1
                                    text-xs font-semibold text-pink-700
                                "
                            >
                                Nổi bật
                            </span>
                        @endif
                    </div>

                    <p class="mt-1 text-sm text-gray-500">
                        ID: {{ $product->id }}
                        ·
                        {{ $product->slug }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a
                    href="{{ route(
                        'products.show',
                        $product->slug
                    ) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="
                        inline-flex items-center justify-center
                        rounded-lg border border-gray-300
                        bg-white px-4 py-2.5
                        text-sm font-semibold text-gray-700
                        transition hover:bg-gray-50
                    "
                >
                    Xem ngoài website
                </a>

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
                            inline-flex items-center justify-center
                            rounded-lg bg-pink-600 px-4 py-2.5
                            text-sm font-semibold text-white
                            transition hover:bg-pink-700
                        "
                    >
                        Chỉnh sửa
                    </a>

                @endif

                @if (
    \Illuminate\Support\Facades\Route::has(
        'admin.products.destroy'
    )
)
    <form
        method="POST"
        action="{{ route(
            'admin.products.destroy',
            $product->id
        ) }}"
        onsubmit="
            return confirm(
                'Bạn chắc chắn muốn xóa sản phẩm này?'
            );
        "
    >
        @csrf
        @method('DELETE')

        <button
            type="submit"
            class="
                inline-flex items-center justify-center
                rounded-lg border border-red-200
                bg-red-50 px-4 py-2.5
                text-sm font-semibold text-red-700
                transition hover:bg-red-100
            "
        >
            Xóa sản phẩm
        </button>
    </form>
@endif
            </div>
        </div>

        {{-- Thống kê --}}
        <div
            class="
                grid grid-cols-1 gap-4
                sm:grid-cols-2 xl:grid-cols-6
            "
        >
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    Hình ảnh
                </p>

                <strong class="mt-2 block text-2xl text-gray-900">
                    {{ number_format($statistics['images']) }}
                </strong>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    Biến thể
                </p>

                <strong class="mt-2 block text-2xl text-blue-600">
                    {{ number_format($statistics['variants']) }}
                </strong>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    SKU
                </p>

                <strong class="mt-2 block text-2xl text-indigo-600">
                    {{ number_format($statistics['skus']) }}
                </strong>

                <p class="mt-1 text-xs text-gray-400">
                    {{ number_format($statistics['active_skus']) }}
                    đang bán
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    Tồn thực tế
                </p>

                <strong class="mt-2 block text-2xl text-green-600">
                    {{ number_format(
                        $statistics['available_quantity']
                    ) }}
                </strong>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    Đang giữ
                </p>

                <strong class="mt-2 block text-2xl text-orange-600">
                    {{ number_format(
                        $statistics['reserved_quantity']
                    ) }}
                </strong>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    Khoảng giá
                </p>

                @if ($minimumPrice === null)
                    <strong class="mt-2 block text-lg text-gray-400">
                        Chưa có giá
                    </strong>
                @elseif (
                    $maximumPrice !== null
                    && $minimumPrice !== $maximumPrice
                )
                    <strong class="mt-2 block text-lg text-pink-600">
                        {{ number_format(
                            $minimumPrice,
                            0,
                            ',',
                            '.'
                        ) }}đ
                    </strong>

                    <p class="mt-1 text-xs text-gray-500">
                        đến
                        {{ number_format(
                            $maximumPrice,
                            0,
                            ',',
                            '.'
                        ) }}đ
                    </p>
                @else
                    <strong class="mt-2 block text-lg text-pink-600">
                        {{ number_format(
                            $minimumPrice,
                            0,
                            ',',
                            '.'
                        ) }}đ
                    </strong>
                @endif
            </div>
        </div>

        <div
            class="
                grid grid-cols-1 gap-6
                xl:grid-cols-[minmax(0,2fr)_minmax(300px,1fr)]
            "
        >
            {{-- Cột trái --}}
            <div class="space-y-6">

                {{-- Thông tin cơ bản --}}
                <section
                    class="
                        overflow-hidden rounded-xl
                        border border-gray-200 bg-white
                    "
                >
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="text-lg font-bold text-gray-900">
                            Thông tin sản phẩm
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">
                        <div>
                            <p class="text-sm text-gray-500">
                                Danh mục
                            </p>

                            <strong class="mt-1 block text-gray-900">
                                {{ $product->category_name ?: 'Chưa có' }}
                            </strong>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Thương hiệu
                            </p>

                            <strong class="mt-1 block text-gray-900">
                                {{ $product->brand_name ?: 'Chưa có' }}
                            </strong>

                            @if ($product->brand_country)
                                <span class="text-sm text-gray-500">
                                    {{ $product->brand_country }}
                                </span>
                            @endif
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Nhà cung cấp
                            </p>

                            <strong class="mt-1 block text-gray-900">
                                {{ $product->supplier_name ?: 'Chưa có' }}
                            </strong>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Xuất xứ
                            </p>

                            <strong class="mt-1 block text-gray-900">
                                {{ $product->origin ?: 'Chưa cập nhật' }}
                            </strong>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Loại da
                            </p>

                            <strong class="mt-1 block text-gray-900">
                                {{ $product->skin_type ?: 'Chưa cập nhật' }}
                            </strong>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Lượt xem
                            </p>

                            <strong class="mt-1 block text-gray-900">
                                {{ number_format($product->view_count) }}
                            </strong>
                        </div>
                    </div>

                    @if ($product->short_description)
                        <div class="border-t border-gray-200 px-5 py-4">
                            <p class="text-sm text-gray-500">
                                Mô tả ngắn
                            </p>

                            <p class="mt-2 text-gray-800">
                                {{ $product->short_description }}
                            </p>
                        </div>
                    @endif

                    @if ($product->description)
                        <div class="border-t border-gray-200 px-5 py-4">
                            <p class="text-sm text-gray-500">
                                Mô tả chi tiết
                            </p>

                            <div
                                class="
                                    prose mt-2 max-w-none
                                    whitespace-pre-line text-gray-800
                                "
                            >
                                {{ $product->description }}
                            </div>
                        </div>
                    @endif

                    @if ($product->ingredient)
                        <div class="border-t border-gray-200 px-5 py-4">
                            <p class="text-sm text-gray-500">
                                Thành phần
                            </p>

                            <p class="mt-2 whitespace-pre-line text-gray-800">
                                {{ $product->ingredient }}
                            </p>
                        </div>
                    @endif

                    @if ($product->usage)
                        <div class="border-t border-gray-200 px-5 py-4">
                            <p class="text-sm text-gray-500">
                                Hướng dẫn sử dụng
                            </p>

                            <p class="mt-2 whitespace-pre-line text-gray-800">
                                {{ $product->usage }}
                            </p>
                        </div>
                    @endif
                </section>

                {{-- Hình ảnh --}}
                <section
                    class="
                        overflow-hidden rounded-xl
                        border border-gray-200 bg-white
                    "
                >
                    <div
                        class="
                            flex items-center justify-between
                            border-b border-gray-200 px-5 py-4
                        "
                    >
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">
                                Hình ảnh sản phẩm
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                {{ number_format($images->count()) }}
                                hình ảnh.
                            </p>
                        </div>
                    </div>

                    <div class="p-5">
                        @if ($images->isNotEmpty())
                            <div
                                class="
                                    grid grid-cols-2 gap-4
                                    md:grid-cols-3 xl:grid-cols-4
                                "
                            >
                                @foreach ($images as $image)
                                    <article
                                        class="
                                            overflow-hidden rounded-xl
                                            border border-gray-200
                                            bg-gray-50
                                        "
                                    >
                                        <div class="aspect-square">
                                            <img
    src="{{ asset('storage/' . ltrim($image->image_path, '/')) }}"
    alt="{{ $product->name }}"
    class="h-full w-full object-cover"
>
                                        </div>

                                        <div class="p-3">
                                            @if ((bool) $image->is_thumbnail)
                                                <span
                                                    class="
                                                        inline-flex
                                                        rounded-full
                                                        bg-pink-100
                                                        px-2.5 py-1
                                                        text-xs
                                                        font-semibold
                                                        text-pink-700
                                                    "
                                                >
                                                    Ảnh đại diện
                                                </span>
                                            @else
                                                <span
                                                    class="
                                                        text-xs
                                                        text-gray-500
                                                    "
                                                >
                                                    Thứ tự:
                                                    {{ $image->sort_order }}
                                                </span>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="py-10 text-center text-gray-500">
                                Sản phẩm chưa có hình ảnh.
                            </div>
                        @endif
                    </div>
                </section>


                {{-- Video sản phẩm --}}
                <section
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white"
                >
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="text-lg font-bold text-gray-900">
                            Video sản phẩm
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ number_format($videos->count()) }} video.
                        </p>
                    </div>

                    <div class="p-5">
                        @forelse ($videos as $video)
                            @php
                                $isExternalVideo =
                                    \Illuminate\Support\Str::startsWith(
                                        $video->video_url,
                                        ['http://', 'https://']
                                    );

                                $videoSource = $isExternalVideo
                                    ? $video->video_url
                                    : asset(
                                        'storage/'
                                        . ltrim($video->video_url, '/')
                                    );
                            @endphp

                            <article
                                class="
                                    mb-5 overflow-hidden rounded-xl
                                    border border-gray-200 bg-gray-50
                                    last:mb-0
                                "
                            >
                                @if ($isExternalVideo)
                                    <div class="p-4">
                                        <h4 class="font-semibold text-gray-900">
                                            {{ $video->title ?: 'Video sản phẩm' }}
                                        </h4>

                                        <p class="mt-1 text-xs text-gray-500">
                                            Loại:
                                            {{ $video->type ?: 'Không xác định' }}
                                            · Thứ tự:
                                            {{ $video->sort_order }}
                                        </p>

                                        <p class="mt-2 break-all text-sm text-gray-600">
                                            {{ $video->video_url }}
                                        </p>

                                        <a
                                            href="{{ $video->video_url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="
                                                mt-3 inline-flex rounded-lg
                                                border border-gray-300 bg-white
                                                px-4 py-2 text-sm font-semibold
                                                text-gray-700 hover:bg-gray-50
                                            "
                                        >
                                            Mở video
                                        </a>
                                    </div>
                                @else
                                    <video
                                        controls
                                        preload="metadata"
                                        class="aspect-video w-full bg-black"
                                    >
                                        <source src="{{ $videoSource }}">
                                        Trình duyệt không hỗ trợ phát video.
                                    </video>

                                    <div class="p-4">
                                        <h4 class="font-semibold text-gray-900">
                                            {{ $video->title ?: 'Video sản phẩm' }}
                                        </h4>

                                        <p class="mt-1 text-xs text-gray-500">
                                            Loại:
                                            {{ $video->type ?: 'Không xác định' }}
                                            ·
                                            {{ strtoupper(
                                                pathinfo(
                                                    $video->video_url,
                                                    PATHINFO_EXTENSION
                                                )
                                            ) }}
                                        </p>
                                    </div>
                                @endif
                            </article>
                        @empty
                            <div class="py-10 text-center text-gray-500">
                                Sản phẩm chưa có video.
                            </div>
                        @endforelse
                    </div>
                </section>

                {{-- Biến thể sản phẩm --}}
                <section
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white"
                >
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="text-lg font-bold text-gray-900">
                            Biến thể sản phẩm
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ number_format($variants->count()) }} biến thể,
                            {{ number_format($statistics['active_variants']) }} đang hoạt động.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Tên</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Mã biến thể</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Giá</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Giá so sánh</th>
                                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Khối lượng</th>
                                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Trạng thái</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($variants as $variant)
                                    <tr>
                                        <td class="px-5 py-4 font-semibold text-gray-900">
                                            {{ $variant->name }}
                                        </td>
                                        <td class="px-5 py-4 text-gray-700">
                                            {{ $variant->sku ?: '—' }}
                                        </td>
                                        <td class="px-5 py-4 text-right font-semibold text-pink-600">
                                            {{ number_format((float) $variant->price, 0, ',', '.') }}đ
                                        </td>
                                        <td class="px-5 py-4 text-right text-gray-600">
                                            {{ $variant->compare_price !== null
                                                ? number_format((float) $variant->compare_price, 0, ',', '.') . 'đ'
                                                : '—' }}
                                        </td>
                                        <td class="px-5 py-4 text-center text-gray-700">
                                            {{ $variant->weight !== null ? $variant->weight : '—' }}
                                        </td>
                                        <td class="px-5 py-4 text-center">
                                            @if ((bool) $variant->status)
                                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Hoạt động</span>
                                            @else
                                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">Tạm ẩn</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-5 py-12 text-center text-gray-500">
                                            Sản phẩm chưa có biến thể.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- SKU --}}
                <section
                    class="
                        overflow-hidden rounded-xl
                        border border-gray-200 bg-white
                    "
                >
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="text-lg font-bold text-gray-900">
                            SKU và giá bán
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Theo dõi toàn bộ mã SKU của sản phẩm.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="
                                            px-5 py-3 text-left
                                            text-xs font-semibold
                                            uppercase tracking-wider
                                            text-gray-500
                                        "
                                    >
                                        SKU
                                    </th>

                                    <th
                                        class="
                                            px-5 py-3 text-left
                                            text-xs font-semibold
                                            uppercase tracking-wider
                                            text-gray-500
                                        "
                                    >
                                        Biến thể
                                    </th>

                                    <th
                                        class="
                                            px-5 py-3 text-right
                                            text-xs font-semibold
                                            uppercase tracking-wider
                                            text-gray-500
                                        "
                                    >
                                        Giá
                                    </th>

                                    <th
                                        class="
                                            px-5 py-3 text-center
                                            text-xs font-semibold
                                            uppercase tracking-wider
                                            text-gray-500
                                        "
                                    >
                                        Tổng
                                    </th>

                                    <th
                                        class="
                                            px-5 py-3 text-center
                                            text-xs font-semibold
                                            uppercase tracking-wider
                                            text-gray-500
                                        "
                                    >
                                        Giữ
                                    </th>

                                    <th
                                        class="
                                            px-5 py-3 text-center
                                            text-xs font-semibold
                                            uppercase tracking-wider
                                            text-gray-500
                                        "
                                    >
                                        Khả dụng
                                    </th>

                                    <th
                                        class="
                                            px-5 py-3 text-center
                                            text-xs font-semibold
                                            uppercase tracking-wider
                                            text-gray-500
                                        "
                                    >
                                        Trạng thái
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($skus as $sku)
                                    <tr>
                                        <td class="px-5 py-4">
                                            <strong class="text-gray-900">
                                                {{ $sku->sku_code }}
                                            </strong>

                                            @if ($sku->barcode)
                                                <p class="mt-1 text-xs text-gray-500">
                                                    {{ $sku->barcode }}
                                                </p>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 text-sm text-gray-700">
                                            {{ $sku->variant_name
                                                ?: 'Mặc định' }}
                                        </td>

                                        <td class="px-5 py-4 text-right">
                                            <strong class="text-pink-600">
                                                {{ number_format(
                                                    (float) $sku->price,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}đ
                                            </strong>

                                            @if (
                                                $sku->compare_price
                                                && (float) $sku->compare_price
                                                    > (float) $sku->price
                                            )
                                                <p
                                                    class="
                                                        mt-1 text-xs
                                                        text-gray-400
                                                        line-through
                                                    "
                                                >
                                                    {{ number_format(
                                                        (float) $sku
                                                            ->compare_price,
                                                        0,
                                                        ',',
                                                        '.'
                                                    ) }}đ
                                                </p>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            {{ number_format(
                                                (int) $sku->total_quantity
                                            ) }}
                                        </td>

                                        <td class="px-5 py-4 text-center text-orange-600">
                                            {{ number_format(
                                                (int) $sku->reserved_quantity
                                            ) }}
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            <strong
                                                class="{{
                                                    (int) $sku
                                                        ->available_quantity > 0
                                                        ? 'text-green-600'
                                                        : 'text-red-600'
                                                }}"
                                            >
                                                {{ number_format(
                                                    (int) $sku
                                                        ->available_quantity
                                                ) }}
                                            </strong>
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            @if ((bool) $sku->status)
                                                <span
                                                    class="
                                                        rounded-full
                                                        bg-green-100
                                                        px-3 py-1
                                                        text-xs
                                                        font-semibold
                                                        text-green-700
                                                    "
                                                >
                                                    Đang bán
                                                </span>
                                            @else
                                                <span
                                                    class="
                                                        rounded-full
                                                        bg-gray-100
                                                        px-3 py-1
                                                        text-xs
                                                        font-semibold
                                                        text-gray-600
                                                    "
                                                >
                                                    Ngừng bán
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="7"
                                            class="
                                                px-5 py-12
                                                text-center text-gray-500
                                            "
                                        >
                                            Sản phẩm chưa có SKU.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Tồn kho --}}
                <section
                    class="
                        overflow-hidden rounded-xl
                        border border-gray-200 bg-white
                    "
                >
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="text-lg font-bold text-gray-900">
                            Tồn kho theo kho hàng
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="
                                            px-5 py-3 text-left
                                            text-xs font-semibold
                                            uppercase tracking-wider
                                            text-gray-500
                                        "
                                    >
                                        Kho
                                    </th>

                                    <th
                                        class="
                                            px-5 py-3 text-left
                                            text-xs font-semibold
                                            uppercase tracking-wider
                                            text-gray-500
                                        "
                                    >
                                        SKU
                                    </th>

                                    <th
                                        class="
                                            px-5 py-3 text-center
                                            text-xs font-semibold
                                            uppercase tracking-wider
                                            text-gray-500
                                        "
                                    >
                                        Tổng
                                    </th>

                                    <th
                                        class="
                                            px-5 py-3 text-center
                                            text-xs font-semibold
                                            uppercase tracking-wider
                                            text-gray-500
                                        "
                                    >
                                        Giữ
                                    </th>

                                    <th
                                        class="
                                            px-5 py-3 text-center
                                            text-xs font-semibold
                                            uppercase tracking-wider
                                            text-gray-500
                                        "
                                    >
                                        Khả dụng
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($inventories as $inventory)
                                    <tr>
                                        <td class="px-5 py-4">
                                            <strong class="text-gray-900">
                                                {{ $inventory
                                                    ->warehouse_name }}
                                            </strong>

                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ $inventory
                                                    ->warehouse_address ?: 'Chưa có địa chỉ' }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4">
                                            <strong class="text-gray-800">
                                                {{ $inventory->sku_code }}
                                            </strong>

                                            @if ($inventory->variant_name)
                                                <p class="mt-1 text-xs text-gray-500">
                                                    {{ $inventory
                                                        ->variant_name }}
                                                </p>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            {{ number_format(
                                                $inventory->quantity
                                            ) }}
                                        </td>

                                        <td class="px-5 py-4 text-center text-orange-600">
                                            {{ number_format(
                                                $inventory
                                                    ->reserved_quantity
                                            ) }}
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            <strong
                                                class="{{
                                                    $inventory
                                                        ->available_quantity > 0
                                                        ? 'text-green-600'
                                                        : 'text-red-600'
                                                }}"
                                            >
                                                {{ number_format(
                                                    $inventory
                                                        ->available_quantity
                                                ) }}
                                            </strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="5"
                                            class="
                                                px-5 py-12 text-center
                                                text-gray-500
                                            "
                                        >
                                            Chưa có dữ liệu tồn kho.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            {{-- Sidebar --}}
            <aside class="space-y-6">

                {{-- Đánh giá --}}
                <section
                    class="
                        rounded-xl border border-gray-200
                        bg-white p-5
                    "
                >
                    <h3 class="text-lg font-bold text-gray-900">
                        Đánh giá
                    </h3>

                    <div class="mt-4 flex items-end gap-3">
                        <strong class="text-4xl text-yellow-500">
                            {{ number_format(
                                (float) (
                                    $reviewStatistics->average_rating ?? 0
                                ),
                                1
                            ) }}
                        </strong>

                        <span class="pb-1 text-sm text-gray-500">
                            / 5
                        </span>
                    </div>

                    <p class="mt-2 text-sm text-gray-500">
                        {{ number_format(
                            (int) (
                                $reviewStatistics->total_reviews ?? 0
                            )
                        ) }}
                        lượt đánh giá
                    </p>
                </section>

                {{-- Hỏi đáp --}}
                <section
                    class="
                        rounded-xl border border-gray-200
                        bg-white p-5
                    "
                >
                    <h3 class="text-lg font-bold text-gray-900">
                        Hỏi đáp
                    </h3>

                    <dl class="mt-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500">
                                Tổng câu hỏi
                            </dt>

                            <dd class="font-semibold text-gray-900">
                                {{ number_format(
                                    $questionStatistics['total']
                                ) }}
                            </dd>
                        </div>

                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500">
                                Chưa trả lời
                            </dt>

                            <dd class="font-semibold text-orange-600">
                                {{ number_format(
                                    $questionStatistics['unanswered']
                                ) }}
                            </dd>
                        </div>
                    </dl>
                </section>

                {{-- Nhà cung cấp --}}
                <section
                    class="
                        rounded-xl border border-gray-200
                        bg-white p-5
                    "
                >
                    <h3 class="text-lg font-bold text-gray-900">
                        Nhà cung cấp
                    </h3>

                    <dl class="mt-4 space-y-4">
                        <div>
                            <dt class="text-sm text-gray-500">
                                Tên
                            </dt>

                            <dd class="mt-1 font-semibold text-gray-900">
                                {{ $product->supplier_name
                                    ?: 'Chưa có' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Điện thoại
                            </dt>

                            <dd class="mt-1 text-gray-800">
                                {{ $product->supplier_phone
                                    ?: 'Chưa có' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Email
                            </dt>

                            <dd class="mt-1 break-all text-gray-800">
                                {{ $product->supplier_email
                                    ?: 'Chưa có' }}
                            </dd>
                        </div>
                    </dl>
                </section>

                {{-- Thời gian --}}
                <section
                    class="
                        rounded-xl border border-gray-200
                        bg-white p-5
                    "
                >
                    <h3 class="text-lg font-bold text-gray-900">
                        Thời gian
                    </h3>

                    <dl class="mt-4 space-y-4">
                        <div>
                            <dt class="text-sm text-gray-500">
                                Ngày tạo
                            </dt>

                            <dd class="mt-1 font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse(
                                    $product->created_at
                                )->format('d/m/Y H:i') }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Cập nhật gần nhất
                            </dt>

                            <dd class="mt-1 font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse(
                                    $product->updated_at
                                )->format('d/m/Y H:i') }}
                            </dd>
                        </div>
                    </dl>
                </section>
            </aside>
        </div>
    </div>
@endsection
