@extends('admin.layouts.master')

@section('title', 'Chi tiết banner')
@section('page-title', 'Chi tiết banner')
@section('page-description', 'Xem nội dung, hình ảnh, liên kết và lịch hiển thị banner.')

@section('content')
@php
    $scheduled = $banner->start_at
        && \Carbon\Carbon::parse($banner->start_at)->isFuture();

    $expired = $banner->end_at
        && \Carbon\Carbon::parse($banner->end_at)->isPast();

    $active = (int) $banner->status === 1
        && ! $scheduled
        && ! $expired;
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                {{ $banner->name }}
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                ID: {{ $banner->id }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a
                href="{{ route('admin.banners.index') }}"
                class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700"
            >
                Quay lại
            </a>

            <a
                href="{{ route('admin.banners.edit', $banner->id) }}"
                class="rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white"
            >
                Chỉnh sửa
            </a>

            <form
                method="POST"
                action="{{ route('admin.banners.destroy', $banner->id) }}"
                onsubmit="return confirm('Bạn chắc chắn muốn xóa banner này?');"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700"
                >
                    Xóa
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

    <div class="admin-responsive-sidebar-layout grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_340px]">
        <div class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">
                    Ảnh desktop
                </h3>

                <img
                    src="{{ asset('storage/' . ltrim($banner->desktop_image, '/')) }}"
                    alt="{{ $banner->name }}"
                    class="mt-4 aspect-[16/7] w-full rounded-xl border border-gray-200 object-cover"
                >
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">
                    Thông tin banner
                </h3>

                <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <dt class="text-sm text-gray-500">Tên quản trị</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $banner->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Vị trí</dt>
                        <dd class="mt-1 font-semibold text-gray-900">
                            {{ $positions[$banner->position] ?? $banner->position }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Tiêu đề</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $banner->title ?: 'Không có' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Nút</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $banner->button_text ?: 'Không có' }}</dd>
                    </div>

                    <div class="md:col-span-2">
                        <dt class="text-sm text-gray-500">Phụ đề</dt>
                        <dd class="mt-2 whitespace-pre-line text-gray-800">{{ $banner->subtitle ?: 'Không có' }}</dd>
                    </div>

                    <div class="md:col-span-2">
                        <dt class="text-sm text-gray-500">Liên kết</dt>
                        <dd class="mt-1 break-all">
                            @if ($banner->link_url)
                                <a
                                    href="{{ $banner->link_url }}"
                                    target="{{ $banner->target }}"
                                    rel="noopener noreferrer"
                                    class="font-medium text-blue-600 hover:underline"
                                >
                                    {{ $banner->link_url }}
                                </a>
                            @else
                                <span class="text-gray-500">Không có</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>
        </div>

        <aside class="space-y-6">
            @if ($banner->mobile_image)
                <section class="rounded-xl border border-gray-200 bg-white p-5">
                    <h3 class="text-lg font-bold text-gray-900">
                        Ảnh mobile
                    </h3>

                    <img
                        src="{{ asset('storage/' . ltrim($banner->mobile_image, '/')) }}"
                        alt="{{ $banner->name }}"
                        class="mt-4 aspect-[4/5] w-full rounded-xl border border-gray-200 object-cover"
                    >
                </section>
            @endif

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Hiệu lực
                </h3>

                <div class="mt-4">
                    @if ((int) $banner->status !== 1)
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-600">Đang tắt</span>
                    @elseif ($scheduled)
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">Sắp hiển thị</span>
                    @elseif ($expired)
                        <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-700">Đã hết hạn</span>
                    @elseif ($active)
                        <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">Đang hoạt động</span>
                    @endif
                </div>

                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Bắt đầu</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ $banner->start_at
                                ? \Carbon\Carbon::parse($banner->start_at)->format('d/m/Y H:i')
                                : 'Không giới hạn' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Kết thúc</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ $banner->end_at
                                ? \Carbon\Carbon::parse($banner->end_at)->format('d/m/Y H:i')
                                : 'Không giới hạn' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Thứ tự</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ number_format((int) $banner->sort_order) }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Target</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ $banner->target }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Người thao tác
                </h3>

                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Người tạo</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ $banner->creator_name ?: 'Hệ thống' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Người cập nhật</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ $banner->updater_name ?: 'Chưa cập nhật' }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">
                    Thời gian
                </h3>

                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Ngày tạo</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($banner->created_at)->format('d/m/Y H:i') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Cập nhật</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($banner->updated_at)->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>
</div>
@endsection
