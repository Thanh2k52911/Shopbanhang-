@extends('admin.layouts.master')

@section('title', 'Quản lý nhà cung cấp')
@section('page-title', 'Quản lý nhà cung cấp')
@section('page-description', 'Theo dõi, tìm kiếm và quản lý toàn bộ nhà cung cấp.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Nhà cung cấp
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Quản lý thông tin liên hệ, mã số thuế và trạng thái hợp tác.
            </p>
        </div>

        <a
            href="{{ route('admin.suppliers.create') }}"
            class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700"
        >
            + Thêm nhà cung cấp
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Tổng nhà cung cấp
            </p>

            <strong class="mt-2 block text-2xl text-gray-900">
                {{ number_format($statistics['total']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Đang hợp tác
            </p>

            <strong class="mt-2 block text-2xl text-green-600">
                {{ number_format($statistics['active']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Ngừng hợp tác
            </p>

            <strong class="mt-2 block text-2xl text-gray-500">
                {{ number_format($statistics['inactive']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Có sản phẩm
            </p>

            <strong class="mt-2 block text-2xl text-pink-600">
                {{ number_format($statistics['with_products']) }}
            </strong>
        </article>
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form
            method="GET"
            action="{{ route('admin.suppliers.index') }}"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4"
        >
            <div class="xl:col-span-2">
                <label for="keyword" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Tìm kiếm
                </label>

                <input
                    id="keyword"
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    placeholder="Tên, liên hệ, SĐT, email, địa chỉ, mã số thuế..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Trạng thái
                </label>

                <select
                    id="status"
                    name="status"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Tất cả trạng thái</option>
                    <option value="1" @selected((string) request('status') === '1')>
                        Đang hợp tác
                    </option>
                    <option value="0" @selected((string) request('status') === '0')>
                        Ngừng hợp tác
                    </option>
                </select>
            </div>

            <div>
                <label for="sort" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Sắp xếp
                </label>

                <select
                    id="sort"
                    name="sort"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Mặc định</option>
                    <option value="name_asc" @selected(request('sort') === 'name_asc')>Tên A → Z</option>
                    <option value="name_desc" @selected(request('sort') === 'name_desc')>Tên Z → A</option>
                    <option value="sort_asc" @selected(request('sort') === 'sort_asc')>Thứ tự tăng dần</option>
                    <option value="sort_desc" @selected(request('sort') === 'sort_desc')>Thứ tự giảm dần</option>
                    <option value="products_desc" @selected(request('sort') === 'products_desc')>Nhiều sản phẩm nhất</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                </select>
            </div>

            <div class="flex flex-wrap items-end gap-3 md:col-span-2 xl:col-span-4">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800"
                >
                    Lọc dữ liệu
                </button>

                <a
                    href="{{ route('admin.suppliers.index') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Đặt lại
                </a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="flex flex-col gap-2 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">
                    Danh sách nhà cung cấp
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Hiển thị {{ number_format($suppliers->count()) }}
                    trên tổng {{ number_format($suppliers->total()) }} kết quả.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Nhà cung cấp
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Liên hệ
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Mã số thuế
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Sản phẩm
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Thứ tự
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Trạng thái
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Thao tác
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($suppliers as $supplier)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="min-w-[280px]">
                                    <a
                                        href="{{ route('admin.suppliers.show', $supplier->id) }}"
                                        class="block truncate font-semibold text-gray-900 transition hover:text-pink-600"
                                        title="{{ $supplier->name }}"
                                    >
                                        {{ $supplier->name }}
                                    </a>

                                    @if ($supplier->address)
                                        <p
                                            class="mt-1 max-w-md truncate text-xs text-gray-500"
                                            title="{{ $supplier->address }}"
                                        >
                                            {{ $supplier->address }}
                                        </p>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="min-w-[230px] space-y-1">
                                    <p class="text-sm font-medium text-gray-800">
                                        {{ $supplier->contact_name ?: 'Chưa cập nhật' }}
                                    </p>

                                    @if ($supplier->phone)
                                        <p class="text-xs text-gray-500">
                                            SĐT: {{ $supplier->phone }}
                                        </p>
                                    @endif

                                    @if ($supplier->email)
                                        <a
                                            href="mailto:{{ $supplier->email }}"
                                            class="block max-w-[230px] truncate text-xs text-blue-600 hover:underline"
                                            title="{{ $supplier->email }}"
                                        >
                                            {{ $supplier->email }}
                                        </a>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if ($supplier->tax_code)
                                    <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                        {{ $supplier->tax_code }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">
                                        Chưa có
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex min-w-9 justify-center rounded-full bg-pink-50 px-3 py-1 text-xs font-semibold text-pink-700">
                                    {{ number_format((int) $supplier->products_count) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="font-semibold text-gray-700">
                                    {{ number_format((int) $supplier->sort_order) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if ((int) $supplier->status === 1)
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        Đang hợp tác
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                        Ngừng hợp tác
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex min-w-[245px] items-center justify-end gap-2">
                                    <a
                                        href="{{ route('admin.suppliers.show', $supplier->id) }}"
                                        class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-xs font-bold text-gray-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700"
                                    >
                                        CHI TIẾT
                                    </a>

                                    <a
                                        href="{{ route('admin.suppliers.edit', $supplier->id) }}"
                                        class="inline-flex h-9 items-center justify-center rounded-lg border border-orange-200 bg-orange-50 px-3 text-xs font-bold text-orange-700 transition hover:bg-orange-100"
                                    >
                                        SỬA
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.suppliers.destroy', $supplier->id) }}"
                                        class="inline-flex"
                                        onsubmit="return confirm('Bạn chắc chắn muốn xóa nhà cung cấp này?');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="inline-flex h-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-bold text-red-600 transition hover:bg-red-100"
                                        >
                                            XÓA
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-gray-500">
                                <div class="mx-auto max-w-md">
                                    <p class="text-lg font-semibold text-gray-700">
                                        Không tìm thấy nhà cung cấp
                                    </p>

                                    <p class="mt-2 text-sm text-gray-500">
                                        Thử thay đổi từ khóa hoặc bộ lọc, hoặc tạo nhà cung cấp mới.
                                    </p>

                                    <a
                                        href="{{ route('admin.suppliers.create') }}"
                                        class="mt-4 inline-flex rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700"
                                    >
                                        Thêm nhà cung cấp
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($suppliers->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $suppliers->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
