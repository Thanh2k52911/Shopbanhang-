@extends('admin.layouts.master')

@section('title', 'Quản lý danh mục')

@section('page-title', 'Quản lý danh mục')

@section(
    'page-description',
    'Theo dõi, tìm kiếm và quản lý toàn bộ danh mục sản phẩm.'
)

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div
            class="
                flex flex-col gap-4
                lg:flex-row lg:items-center lg:justify-between
            "
        >
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Danh mục sản phẩm
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Quản lý danh mục cha, danh mục con và trạng thái hiển thị.
                </p>
            </div>

            <a
                href="{{ route('admin.categories.create') }}"
                class="
                    inline-flex items-center justify-center
                    rounded-lg bg-pink-600 px-5 py-2.5
                    text-sm font-semibold text-white
                    transition hover:bg-pink-700
                "
            >
                + Thêm danh mục
            </a>
        </div>

        {{-- Thông báo --}}
        @if (session('success'))
            <div
                class="
                    rounded-xl border border-green-200
                    bg-green-50 px-4 py-3
                    text-sm font-medium text-green-700
                "
            >
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="
                    rounded-xl border border-red-200
                    bg-red-50 px-4 py-3
                    text-sm font-medium text-red-700
                "
            >
                {{ session('error') }}
            </div>
        @endif

        {{-- Thống kê --}}
        <div
            class="
                grid grid-cols-1 gap-4
                sm:grid-cols-2 xl:grid-cols-5
            "
        >
            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    Tổng danh mục
                </p>

                <strong class="mt-2 block text-2xl text-gray-900">
                    {{ number_format($statistics['total']) }}
                </strong>
            </article>

            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    Đang hiển thị
                </p>

                <strong class="mt-2 block text-2xl text-green-600">
                    {{ number_format($statistics['active']) }}
                </strong>
            </article>

            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    Đang ẩn
                </p>

                <strong class="mt-2 block text-2xl text-gray-500">
                    {{ number_format($statistics['inactive']) }}
                </strong>
            </article>

            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    Danh mục cha
                </p>

                <strong class="mt-2 block text-2xl text-blue-600">
                    {{ number_format($statistics['root']) }}
                </strong>
            </article>

            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    Danh mục con
                </p>

                <strong class="mt-2 block text-2xl text-indigo-600">
                    {{ number_format($statistics['children']) }}
                </strong>
            </article>
        </div>

        {{-- Bộ lọc --}}
        <section
            class="
                rounded-xl border border-gray-200
                bg-white p-5
            "
        >
            <form
                method="GET"
                action="{{ route('admin.categories.index') }}"
                class="
                    grid grid-cols-1 gap-4
                    md:grid-cols-2 xl:grid-cols-5
                "
            >
                <div class="xl:col-span-2">
                    <label
                        for="keyword"
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Tìm kiếm
                    </label>

                    <input
                        id="keyword"
                        type="text"
                        name="keyword"
                        value="{{ request('keyword') }}"
                        placeholder="Tên, slug hoặc danh mục cha..."
                        class="
                            w-full rounded-lg border border-gray-300
                            px-4 py-2.5 text-sm
                            focus:border-pink-500 focus:ring-pink-500
                        "
                    >
                </div>

                <div>
                    <label
                        for="status"
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Trạng thái
                    </label>

                    <select
                        id="status"
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
                            @selected((string) request('status') === '1')
                        >
                            Đang hiển thị
                        </option>

                        <option
                            value="0"
                            @selected((string) request('status') === '0')
                        >
                            Đang ẩn
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        for="parent_id"
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Danh mục cha
                    </label>

                    <select
                        id="parent_id"
                        name="parent_id"
                        class="
                            w-full rounded-lg border border-gray-300
                            px-3 py-2.5 text-sm
                            focus:border-pink-500 focus:ring-pink-500
                        "
                    >
                        <option value="">
                            Tất cả danh mục
                        </option>

                        <option
                            value="root"
                            @selected(request('parent_id') === 'root')
                        >
                            Chỉ danh mục gốc
                        </option>

                        @foreach ($parentCategories as $parentCategory)
                            <option
                                value="{{ $parentCategory->id }}"
                                @selected(
                                    (string) request('parent_id')
                                    === (string) $parentCategory->id
                                )
                            >
                                {{ $parentCategory->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        for="sort"
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Sắp xếp
                    </label>

                    <select
                        id="sort"
                        name="sort"
                        class="
                            w-full rounded-lg border border-gray-300
                            px-3 py-2.5 text-sm
                            focus:border-pink-500 focus:ring-pink-500
                        "
                    >
                        <option value="">
                            Mặc định
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
                            value="sort_asc"
                            @selected(request('sort') === 'sort_asc')
                        >
                            Thứ tự tăng dần
                        </option>

                        <option
                            value="sort_desc"
                            @selected(request('sort') === 'sort_desc')
                        >
                            Thứ tự giảm dần
                        </option>

                        <option
                            value="oldest"
                            @selected(request('sort') === 'oldest')
                        >
                            Cũ nhất
                        </option>
                    </select>
                </div>

                <div
                    class="
                        flex flex-wrap items-end gap-3
                        md:col-span-2 xl:col-span-5
                    "
                >
                    <button
                        type="submit"
                        class="
                            inline-flex items-center justify-center
                            rounded-lg bg-gray-900 px-5 py-2.5
                            text-sm font-semibold text-white
                            transition hover:bg-gray-800
                        "
                    >
                        Lọc dữ liệu
                    </button>

                    <a
                        href="{{ route('admin.categories.index') }}"
                        class="
                            inline-flex items-center justify-center
                            rounded-lg border border-gray-300
                            bg-white px-5 py-2.5
                            text-sm font-semibold text-gray-700
                            transition hover:bg-gray-50
                        "
                    >
                        Đặt lại
                    </a>
                </div>
            </form>
        </section>

        {{-- Bảng danh mục --}}
        <section
            class="
                overflow-hidden rounded-xl
                border border-gray-200 bg-white
            "
        >
            <div
                class="
                    flex flex-col gap-2
                    border-b border-gray-200
                    px-5 py-4
                    sm:flex-row sm:items-center sm:justify-between
                "
            >
                <div>
                    <h3 class="text-lg font-bold text-gray-900">
                        Danh sách danh mục
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Hiển thị {{ number_format($categories->count()) }}
                        trên tổng {{ number_format($categories->total()) }}
                        kết quả.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="
                                    px-5 py-3 text-left text-xs
                                    font-semibold uppercase tracking-wider
                                    text-gray-500
                                "
                            >
                                Danh mục
                            </th>

                            <th
                                class="
                                    px-5 py-3 text-left text-xs
                                    font-semibold uppercase tracking-wider
                                    text-gray-500
                                "
                            >
                                Danh mục cha
                            </th>

                            <th
                                class="
                                    px-5 py-3 text-center text-xs
                                    font-semibold uppercase tracking-wider
                                    text-gray-500
                                "
                            >
                                Danh mục con
                            </th>

                            <th
                                class="
                                    px-5 py-3 text-center text-xs
                                    font-semibold uppercase tracking-wider
                                    text-gray-500
                                "
                            >
                                Sản phẩm
                            </th>

                            <th
                                class="
                                    px-5 py-3 text-center text-xs
                                    font-semibold uppercase tracking-wider
                                    text-gray-500
                                "
                            >
                                Thứ tự
                            </th>

                            <th
                                class="
                                    px-5 py-3 text-center text-xs
                                    font-semibold uppercase tracking-wider
                                    text-gray-500
                                "
                            >
                                Trạng thái
                            </th>

                            <th
                                class="
                                    px-5 py-3 text-right text-xs
                                    font-semibold uppercase tracking-wider
                                    text-gray-500
                                "
                            >
                                Thao tác
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($categories as $category)
                            <tr class="transition hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="flex min-w-[280px] items-center gap-4">
                                        <div
                                            class="
                                                h-14 w-14 shrink-0 overflow-hidden
                                                rounded-xl border border-gray-200
                                                bg-gray-50
                                            "
                                        >
                                            @if ($category->thumbnail)
                                                <img
                                                    src="{{ asset(
                                                        'storage/'
                                                        . ltrim(
                                                            $category->thumbnail,
                                                            '/'
                                                        )
                                                    ) }}"
                                                    alt="{{ $category->name }}"
                                                    class="h-full w-full object-cover"
                                                >
                                            @else
                                                <div
                                                    class="
                                                        grid h-full w-full place-items-center
                                                        bg-pink-50 text-lg font-bold
                                                        text-pink-600
                                                    "
                                                >
                                                    {{ mb_strtoupper(
                                                        mb_substr(
                                                            $category->name,
                                                            0,
                                                            1
                                                        )
                                                    ) }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="min-w-0">
                                            <a
                                                href="{{ route(
                                                    'admin.categories.show',
                                                    $category->id
                                                ) }}"
                                                class="
                                                    block truncate font-semibold
                                                    text-gray-900 transition
                                                    hover:text-pink-600
                                                "
                                                title="{{ $category->name }}"
                                            >
                                                {{ $category->name }}
                                            </a>

                                            <p
                                                class="
                                                    mt-1 max-w-xs truncate
                                                    text-xs text-gray-500
                                                "
                                                title="{{ $category->slug }}"
                                            >
                                                {{ $category->slug }}
                                            </p>

                                            @if ($category->description)
                                                <p
                                                    class="
                                                        mt-1 max-w-sm truncate
                                                        text-xs text-gray-400
                                                    "
                                                    title="{{ $category->description }}"
                                                >
                                                    {{ $category->description }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    @if ($category->parent_name)
                                        <span
                                            class="
                                                inline-flex rounded-full
                                                bg-blue-50 px-3 py-1
                                                text-xs font-semibold text-blue-700
                                            "
                                        >
                                            {{ $category->parent_name }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400">
                                            Danh mục gốc
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="
                                            inline-flex min-w-9 justify-center
                                            rounded-full bg-indigo-50
                                            px-3 py-1 text-xs
                                            font-semibold text-indigo-700
                                        "
                                    >
                                        {{ number_format(
                                            (int) $category->children_count
                                        ) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="
                                            inline-flex min-w-9 justify-center
                                            rounded-full bg-pink-50
                                            px-3 py-1 text-xs
                                            font-semibold text-pink-700
                                        "
                                    >
                                        {{ number_format(
                                            (int) $category->products_count
                                        ) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span class="font-semibold text-gray-700">
                                        {{ number_format(
                                            (int) $category->sort_order
                                        ) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    @if ((int) $category->status === 1)
                                        <span
                                            class="
                                                inline-flex rounded-full
                                                bg-green-100 px-3 py-1
                                                text-xs font-semibold text-green-700
                                            "
                                        >
                                            Hiển thị
                                        </span>
                                    @else
                                        <span
                                            class="
                                                inline-flex rounded-full
                                                bg-gray-100 px-3 py-1
                                                text-xs font-semibold text-gray-600
                                            "
                                        >
                                            Đang ẩn
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <div
                                        class="
                                            flex min-w-[245px]
                                            items-center justify-end gap-2
                                        "
                                    >
                                        <a
                                            href="{{ route(
                                                'admin.categories.show',
                                                $category->id
                                            ) }}"
                                            title="Chi tiết"
                                            class="
                                                inline-flex h-9 items-center
                                                justify-center rounded-lg
                                                border border-gray-300
                                                bg-white px-3
                                                text-xs font-bold text-gray-700
                                                transition hover:border-blue-300
                                                hover:bg-blue-50 hover:text-blue-700
                                            "
                                        >
                                            CHI TIẾT
                                        </a>

                                        <a
                                            href="{{ route(
                                                'admin.categories.edit',
                                                $category->id
                                            ) }}"
                                            title="Chỉnh sửa"
                                            class="
                                                inline-flex h-9 items-center
                                                justify-center rounded-lg
                                                border border-orange-200
                                                bg-orange-50 px-3
                                                text-xs font-bold text-orange-700
                                                transition hover:bg-orange-100
                                            "
                                        >
                                            SỬA
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.categories.destroy',
                                                $category->id
                                            ) }}"
                                            class="inline-flex"
                                            onsubmit="
                                                return confirm(
                                                    'Bạn chắc chắn muốn xóa danh mục này?'
                                                );
                                            "
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                title="Xóa danh mục"
                                                class="
                                                    inline-flex h-9 items-center
                                                    justify-center rounded-lg
                                                    border border-red-200
                                                    bg-red-50 px-3
                                                    text-xs font-bold text-red-600
                                                    transition hover:bg-red-100
                                                "
                                            >
                                                XÓA
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="7"
                                    class="
                                        px-5 py-16 text-center
                                        text-gray-500
                                    "
                                >
                                    <div class="mx-auto max-w-md">
                                        <p class="text-lg font-semibold text-gray-700">
                                            Không tìm thấy danh mục
                                        </p>

                                        <p class="mt-2 text-sm text-gray-500">
                                            Thử thay đổi từ khóa hoặc bộ lọc,
                                            hoặc tạo danh mục mới.
                                        </p>

                                        <a
                                            href="{{ route(
                                                'admin.categories.create'
                                            ) }}"
                                            class="
                                                mt-4 inline-flex rounded-lg
                                                bg-pink-600 px-4 py-2.5
                                                text-sm font-semibold text-white
                                                transition hover:bg-pink-700
                                            "
                                        >
                                            Thêm danh mục
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($categories->hasPages())
                <div class="border-t border-gray-200 px-5 py-4">
                    {{ $categories->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
