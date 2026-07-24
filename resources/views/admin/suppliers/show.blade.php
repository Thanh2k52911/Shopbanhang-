@extends('admin.layouts.master')

@section('title', 'Chi tiết nhà cung cấp')
@section('page-title', 'Chi tiết nhà cung cấp')
@section('page-description', 'Theo dõi thông tin nhà cung cấp và các sản phẩm liên quan.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex min-w-0 items-center gap-4">
            <a
                href="{{ route('admin.suppliers.index') }}"
                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white text-xl text-gray-600 transition hover:bg-gray-50"
                title="Quay lại"
            >
                ←
            </a>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h2
                        class="max-w-4xl truncate text-2xl font-bold text-gray-900"
                        title="{{ $supplier->name }}"
                    >
                        {{ $supplier->name }}
                    </h2>

                    @if ((int) $supplier->status === 1)
                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                            Đang hợp tác
                        </span>
                    @else
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                            Ngừng hợp tác
                        </span>
                    @endif
                </div>

                <p class="mt-1 text-sm text-gray-500">
                    ID: {{ $supplier->id }}
                    @if ($supplier->tax_code)
                        · MST: {{ $supplier->tax_code }}
                    @endif
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if ($supplier->email)
                <a
                    href="mailto:{{ $supplier->email }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Gửi email
                </a>
            @endif

            @if ($supplier->phone)
                <a
                    href="tel:{{ preg_replace('/\s+/', '', $supplier->phone) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Gọi điện
                </a>
            @endif

            <a
                href="{{ route('admin.suppliers.edit', $supplier->id) }}"
                class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700"
            >
                Chỉnh sửa
            </a>

            <form
                method="POST"
                action="{{ route('admin.suppliers.destroy', $supplier->id) }}"
                onsubmit="return confirm('Bạn chắc chắn muốn xóa nhà cung cấp này?');"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100"
                >
                    Xóa nhà cung cấp
                </button>
            </form>
        </div>
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
            <p class="text-sm text-gray-500">Tổng sản phẩm</p>

            <strong class="mt-2 block text-2xl text-pink-600">
                {{ number_format($statistics['products']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Đang bán</p>

            <strong class="mt-2 block text-2xl text-green-600">
                {{ number_format($statistics['active_products']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Sản phẩm nổi bật</p>

            <strong class="mt-2 block text-2xl text-orange-600">
                {{ number_format($statistics['featured_products']) }}
            </strong>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Tổng lượt xem</p>

            <strong class="mt-2 block text-2xl text-blue-600">
                {{ number_format($statistics['total_views']) }}
            </strong>
        </article>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <div class="space-y-6">
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Thông tin nhà cung cấp
                    </h3>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-gray-500">
                            Tên nhà cung cấp
                        </p>

                        <strong class="mt-1 block text-gray-900">
                            {{ $supplier->name }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Người liên hệ
                        </p>

                        <strong class="mt-1 block text-gray-900">
                            {{ $supplier->contact_name ?: 'Chưa cập nhật' }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Điện thoại
                        </p>

                        @if ($supplier->phone)
                            <a
                                href="tel:{{ preg_replace('/\s+/', '', $supplier->phone) }}"
                                class="mt-1 block font-medium text-blue-600 hover:underline"
                            >
                                {{ $supplier->phone }}
                            </a>
                        @else
                            <strong class="mt-1 block text-gray-900">
                                Chưa cập nhật
                            </strong>
                        @endif
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Email
                        </p>

                        @if ($supplier->email)
                            <a
                                href="mailto:{{ $supplier->email }}"
                                class="mt-1 block break-all font-medium text-blue-600 hover:underline"
                            >
                                {{ $supplier->email }}
                            </a>
                        @else
                            <strong class="mt-1 block text-gray-900">
                                Chưa cập nhật
                            </strong>
                        @endif
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Mã số thuế
                        </p>

                        <strong class="mt-1 block text-gray-900">
                            {{ $supplier->tax_code ?: 'Chưa cập nhật' }}
                        </strong>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Thứ tự hiển thị
                        </p>

                        <strong class="mt-1 block text-gray-900">
                            {{ number_format((int) $supplier->sort_order) }}
                        </strong>
                    </div>

                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500">
                            Địa chỉ
                        </p>

                        <p class="mt-1 whitespace-pre-line font-medium text-gray-900">
                            {{ $supplier->address ?: 'Chưa cập nhật' }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Sản phẩm từ nhà cung cấp
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Hiển thị tối đa 20 sản phẩm mới nhất.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Sản phẩm
                                </th>

                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Lượt xem
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
                            @forelse ($products as $product)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <a
                                            href="{{ route('admin.products.show', $product->id) }}"
                                            class="font-semibold text-gray-900 transition hover:text-pink-600"
                                        >
                                            {{ $product->name }}
                                        </a>

                                        @if ((bool) $product->is_featured)
                                            <span class="ml-2 rounded-full bg-pink-100 px-2 py-1 text-xs font-semibold text-pink-700">
                                                Nổi bật
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        {{ number_format((int) $product->view_count) }}
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        @if ((int) $product->status === 1)
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                Đang bán
                                            </span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                                Ngừng bán
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        <a
                                            href="{{ route('admin.products.show', $product->id) }}"
                                            class="inline-flex rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 transition hover:bg-gray-50"
                                        >
                                            CHI TIẾT
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="4"
                                        class="px-5 py-12 text-center text-gray-500"
                                    >
                                        Nhà cung cấp chưa có sản phẩm.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Trạng thái hợp tác
                </h3>

                <div class="mt-4">
                    @if ((int) $supplier->status === 1)
                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">
                            Đang hợp tác
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-600">
                            Ngừng hợp tác
                        </span>
                    @endif
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Liên hệ nhanh
                </h3>

                <div class="mt-4 space-y-3">
                    @if ($supplier->phone)
                        <a
                            href="tel:{{ preg_replace('/\s+/', '', $supplier->phone) }}"
                            class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            <span>Điện thoại</span>
                            <span class="text-blue-600">
                                {{ $supplier->phone }}
                            </span>
                        </a>
                    @endif

                    @if ($supplier->email)
                        <a
                            href="mailto:{{ $supplier->email }}"
                            class="block rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-blue-600 transition hover:bg-gray-50"
                        >
                            {{ $supplier->email }}
                        </a>
                    @endif

                    @if (! $supplier->phone && ! $supplier->email)
                        <p class="text-sm text-gray-500">
                            Chưa có thông tin liên hệ nhanh.
                        </p>
                    @endif
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Thời gian
                </h3>

                <dl class="mt-4 space-y-4">
                    <div>
                        <dt class="text-sm text-gray-500">
                            Ngày tạo
                        </dt>

                        <dd class="mt-1 font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($supplier->created_at)->format('d/m/Y H:i') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Cập nhật gần nhất
                        </dt>

                        <dd class="mt-1 font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($supplier->updated_at)->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>
</div>
@endsection
