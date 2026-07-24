@extends('admin.layouts.master')

@section('title', 'Cài đặt hệ thống')
@section('page-title', 'Cài đặt hệ thống')
@section('page-description', 'Quản lý thông tin website, liên hệ, SEO, vận chuyển và tùy chọn hệ thống.')

@section('content')
@php
    $activeSettings = $groupedSettings->get($activeGroup, collect());
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Cài đặt hệ thống
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Thay đổi cấu hình website theo từng nhóm chức năng.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('admin.settings.defaults') }}"
            onsubmit="return confirm('Bổ sung bộ cấu hình mặc định? Các key đã tồn tại sẽ được cập nhật theo bộ mặc định.')"
        >
            @csrf

            <button
                type="submit"
                class="rounded-lg border border-pink-200 bg-pink-50 px-5 py-2.5 text-sm font-semibold text-pink-700 hover:bg-pink-100"
            >
                Bổ sung cấu hình mặc định
            </button>
        </form>
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

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="font-semibold text-red-700">
                Dữ liệu chưa hợp lệ:
            </p>

            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['Tổng cấu hình', $statistics['total'], 'text-gray-900'],
            ['Nhóm cấu hình', $statistics['groups'], 'text-blue-600'],
            ['Công khai', $statistics['public'], 'text-green-600'],
            ['Nội bộ', $statistics['private'], 'text-orange-600'],
            ['Chưa có giá trị', $statistics['empty'], 'text-red-600'],
        ] as [$label, $value, $class])
            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">
                    {{ $label }}
                </p>

                <strong class="mt-2 block text-2xl {{ $class }}">
                    {{ number_format((int) $value) }}
                </strong>
            </article>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[260px_minmax(0,1fr)]">
        <aside class="rounded-xl border border-gray-200 bg-white p-3">
            <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Nhóm cài đặt
            </p>

            <nav class="mt-2 space-y-1">
                @forelse ($groupedSettings as $group => $items)
                    @php
                        $isActive = $activeGroup === $group;
                    @endphp

                    <a
                        href="{{ route('admin.settings.index', ['group' => $group]) }}"
                        class="
                            flex items-center justify-between rounded-lg px-3 py-2.5
                            text-sm font-semibold transition
                            {{ $isActive
                                ? 'bg-pink-100 text-pink-700'
                                : 'text-gray-700 hover:bg-gray-100'
                            }}
                        "
                    >
                        <span>
                            {{ $groupLabels[$group] ?? ucfirst($group) }}
                        </span>

                        <span
                            class="
                                rounded-full px-2 py-0.5 text-xs
                                {{ $isActive
                                    ? 'bg-pink-200 text-pink-700'
                                    : 'bg-gray-100 text-gray-500'
                                }}
                            "
                        >
                            {{ $items->count() }}
                        </span>
                    </a>
                @empty
                    <p class="px-3 py-4 text-sm text-gray-500">
                        Chưa có cấu hình.
                    </p>
                @endforelse
            </nav>
        </aside>

        <div class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-6 py-5">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">
                                {{ $groupLabels[$activeGroup] ?? ucfirst($activeGroup) }}
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                {{ $activeSettings->count() }} cấu hình trong nhóm này.
                            </p>
                        </div>

                        @if ($activeSettings->isNotEmpty())
                            <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                Nhóm: {{ $activeGroup }}
                            </span>
                        @endif
                    </div>
                </div>

                @if ($activeSettings->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <p class="text-gray-500">
                            Nhóm này chưa có cấu hình.
                        </p>
                    </div>
                @else
                    <form
                        method="POST"
                        action="{{ route('admin.settings.groups.update', $activeGroup) }}"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        @method('PATCH')

                        <div class="divide-y divide-gray-100">
                            @foreach ($activeSettings as $setting)
                                <div class="grid grid-cols-1 gap-4 px-6 py-5 lg:grid-cols-[minmax(0,260px)_minmax(0,1fr)]">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <label
                                                for="setting_{{ $setting->id }}"
                                                class="font-semibold text-gray-900"
                                            >
                                                {{ $setting->label ?: $setting->key }}
                                            </label>

                                            @if ($setting->is_public)
                                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">
                                                    Công khai
                                                </span>
                                            @else
                                                <span class="rounded-full bg-orange-100 px-2 py-0.5 text-xs font-semibold text-orange-700">
                                                    Nội bộ
                                                </span>
                                            @endif
                                        </div>

                                        <p class="mt-1 break-all font-mono text-xs text-gray-400">
                                            {{ $setting->key }}
                                        </p>

                                        @if ($setting->description)
                                            <p class="mt-2 text-sm leading-6 text-gray-500">
                                                {{ $setting->description }}
                                            </p>
                                        @endif
                                    </div>

                                    <div>
                                        @switch($setting->type)
                                            @case('text')
                                                <textarea
                                                    id="setting_{{ $setting->id }}"
                                                    name="settings[{{ $setting->key }}]"
                                                    rows="5"
                                                    class="w-full rounded-lg border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"
                                                >{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                                @break

                                            @case('number')
                                                <input
                                                    id="setting_{{ $setting->id }}"
                                                    type="number"
                                                    step="any"
                                                    name="settings[{{ $setting->key }}]"
                                                    value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                                                >
                                                @break

                                            @case('boolean')
                                                <input
                                                    type="hidden"
                                                    name="settings[{{ $setting->key }}]"
                                                    value="0"
                                                >

                                                <label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3">
                                                    <input
                                                        id="setting_{{ $setting->id }}"
                                                        type="checkbox"
                                                        name="settings[{{ $setting->key }}]"
                                                        value="1"
                                                        @checked((string) old(
                                                            'settings.' . $setting->key,
                                                            $setting->value
                                                        ) === '1')
                                                        class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                                    >

                                                    <span class="text-sm font-medium text-gray-700">
                                                        Bật cấu hình này
                                                    </span>
                                                </label>
                                                @break

                                            @case('json')
                                                <textarea
                                                    id="setting_{{ $setting->id }}"
                                                    name="settings[{{ $setting->key }}]"
                                                    rows="8"
                                                    spellcheck="false"
                                                    class="w-full rounded-lg border-gray-300 px-4 py-3 font-mono text-xs focus:border-pink-500 focus:ring-pink-500"
                                                >{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                                @break

                                            @case('image')
                                                @if ($setting->value)
                                                    <div class="mb-4 overflow-hidden rounded-xl border border-gray-200 bg-gray-50 p-4">
                                                        <img
                                                            src="{{ asset('storage/' . ltrim($setting->value, '/')) }}"
                                                            alt="{{ $setting->label ?: $setting->key }}"
                                                            class="max-h-40 max-w-full object-contain"
                                                        >
                                                    </div>
                                                @endif

                                                <input
                                                    id="setting_{{ $setting->id }}"
                                                    type="file"
                                                    name="settings[{{ $setting->key }}]"
                                                    accept=".jpg,.jpeg,.png,.webp,.gif,.svg,.ico"
                                                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700"
                                                >
                                                @break

                                            @case('file')
                                                @if ($setting->value)
                                                    <div class="mb-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm">
                                                        <a
                                                            href="{{ asset('storage/' . ltrim($setting->value, '/')) }}"
                                                            target="_blank"
                                                            class="font-semibold text-blue-600 hover:underline"
                                                        >
                                                            Xem tệp hiện tại
                                                        </a>
                                                    </div>
                                                @endif

                                                <input
                                                    id="setting_{{ $setting->id }}"
                                                    type="file"
                                                    name="settings[{{ $setting->key }}]"
                                                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700"
                                                >
                                                @break

                                            @case('color')
                                                <div class="flex items-center gap-3">
                                                    <input
                                                        type="color"
                                                        value="{{ old(
                                                            'settings.' . $setting->key,
                                                            $setting->value ?: '#ec4899'
                                                        ) }}"
                                                        class="h-11 w-14 rounded border border-gray-300 bg-white p-1"
                                                        data-setting-color-picker="{{ $setting->id }}"
                                                    >

                                                    <input
                                                        id="setting_{{ $setting->id }}"
                                                        type="text"
                                                        name="settings[{{ $setting->key }}]"
                                                        value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                                        maxlength="30"
                                                        class="min-w-0 flex-1 rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                                                        data-setting-color-text="{{ $setting->id }}"
                                                    >
                                                </div>
                                                @break

                                            @default
                                                <input
                                                    id="setting_{{ $setting->id }}"
                                                    type="text"
                                                    name="settings[{{ $setting->key }}]"
                                                    value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                                                >
                                        @endswitch

                                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                            <span class="text-xs text-gray-400">
                                                Kiểu: {{ $supportedTypes[$setting->type] ?? $setting->type }}
                                            </span>

                                            <details class="relative">
                                                <summary class="cursor-pointer text-xs font-semibold text-blue-600">
                                                    Chỉnh sửa nâng cao
                                                </summary>

                                                <div class="mt-3 grid grid-cols-1 gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4 md:grid-cols-2">
                                                    <div>
                                                        <label class="mb-1.5 block text-xs font-semibold text-gray-600">
                                                            Nhóm
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="group"
                                                            value="{{ $setting->group }}"
                                                            required
                                                            form="setting-meta-form-{{ $setting->id }}"
                                                            class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm"
                                                        >
                                                    </div>

                                                    <div>
                                                        <label class="mb-1.5 block text-xs font-semibold text-gray-600">
                                                            Key
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="key"
                                                            value="{{ $setting->key }}"
                                                            required
                                                            form="setting-meta-form-{{ $setting->id }}"
                                                            class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm"
                                                        >
                                                    </div>

                                                    <div>
                                                        <label class="mb-1.5 block text-xs font-semibold text-gray-600">
                                                            Nhãn
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="label"
                                                            value="{{ $setting->label }}"
                                                            form="setting-meta-form-{{ $setting->id }}"
                                                            class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm"
                                                        >
                                                    </div>

                                                    <div>
                                                        <label class="mb-1.5 block text-xs font-semibold text-gray-600">
                                                            Kiểu
                                                        </label>

                                                        <select
                                                            name="type"
                                                            form="setting-meta-form-{{ $setting->id }}"
                                                            class="w-full rounded-lg border-gray-300 text-sm"
                                                        >
                                                            @foreach ($supportedTypes as $typeValue => $typeLabel)
                                                                <option
                                                                    value="{{ $typeValue }}"
                                                                    @selected($setting->type === $typeValue)
                                                                >
                                                                    {{ $typeLabel }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="md:col-span-2">
                                                        <label class="mb-1.5 block text-xs font-semibold text-gray-600">
                                                            Mô tả
                                                        </label>

                                                        <textarea
                                                            name="description"
                                                            rows="3"
                                                            form="setting-meta-form-{{ $setting->id }}"
                                                            class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm"
                                                        >{{ $setting->description }}</textarea>
                                                    </div>

                                                    <div>
                                                        <label class="mb-1.5 block text-xs font-semibold text-gray-600">
                                                            Thứ tự
                                                        </label>

                                                        <input
                                                            type="number"
                                                            name="sort_order"
                                                            value="{{ $setting->sort_order }}"
                                                            min="0"
                                                            required
                                                            form="setting-meta-form-{{ $setting->id }}"
                                                            class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm"
                                                        >
                                                    </div>

                                                    <label class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3">
                                                        <input
                                                            type="hidden"
                                                            name="is_public"
                                                            value="0"
                                                            form="setting-meta-form-{{ $setting->id }}"
                                                        >

                                                        <input
                                                            type="checkbox"
                                                            name="is_public"
                                                            value="1"
                                                            form="setting-meta-form-{{ $setting->id }}"
                                                            @checked((bool) $setting->is_public)
                                                            class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                                        >

                                                        <span class="text-sm font-medium text-gray-700">
                                                            Công khai
                                                        </span>
                                                    </label>

                                                    <div class="flex flex-wrap gap-3 md:col-span-2">
                                                        <button
                                                            type="submit"
                                                            form="setting-meta-form-{{ $setting->id }}"
                                                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white"
                                                        >
                                                            Lưu metadata
                                                        </button>

                                                        <button
                                                            type="submit"
                                                            form="setting-delete-form-{{ $setting->id }}"
                                                            class="text-sm font-semibold text-red-600 hover:underline"
                                                            onclick="return confirm('Xóa cấu hình {{ $setting->key }}?')"
                                                        >
                                                            Xóa cấu hình
                                                        </button>
                                                    </div>
                                                </div>
                                            </details>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-gray-200 px-6 py-5 text-right">
                            <button
                                type="submit"
                                class="rounded-lg bg-pink-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-pink-700"
                            >
                                Lưu nhóm cấu hình
                            </button>
                        </div>
                    </form>

                    {{-- Các form độc lập, tránh form lồng nhau trong HTML --}}
                    @foreach ($activeSettings as $setting)
                        <form
                            id="setting-meta-form-{{ $setting->id }}"
                            method="POST"
                            action="{{ route('admin.settings.meta.update', $setting->id) }}"
                            class="hidden"
                        >
                            @csrf
                            @method('PATCH')
                        </form>

                        <form
                            id="setting-delete-form-{{ $setting->id }}"
                            method="POST"
                            action="{{ route('admin.settings.destroy', $setting->id) }}"
                            class="hidden"
                        >
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                @endif
            </section>

            <section class="rounded-xl border border-gray-200 bg-white">
                <details>
                    <summary class="cursor-pointer px-6 py-5 text-lg font-bold text-gray-900">
                        Thêm cấu hình mới
                    </summary>

                    <div class="border-t border-gray-200 p-6">
                        <form
                            method="POST"
                            action="{{ route('admin.settings.store') }}"
                            enctype="multipart/form-data"
                            class="grid grid-cols-1 gap-4 md:grid-cols-2"
                        >
                            @csrf

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Nhóm *
                                </label>

                                <input
                                    type="text"
                                    name="group"
                                    value="{{ old('group', $activeGroup) }}"
                                    required
                                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm"
                                >
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Key *
                                </label>

                                <input
                                    type="text"
                                    name="key"
                                    value="{{ old('key') }}"
                                    required
                                    placeholder="example_setting"
                                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm"
                                >
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Nhãn
                                </label>

                                <input
                                    type="text"
                                    name="label"
                                    value="{{ old('label') }}"
                                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm"
                                >
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Kiểu dữ liệu *
                                </label>

                                <select
                                    name="type"
                                    required
                                    class="w-full rounded-lg border-gray-300 text-sm"
                                >
                                    @foreach ($supportedTypes as $value => $label)
                                        <option
                                            value="{{ $value }}"
                                            @selected(old('type') === $value)
                                        >
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Giá trị ban đầu
                                </label>

                                <textarea
                                    name="value"
                                    rows="4"
                                    class="w-full rounded-lg border-gray-300 px-4 py-3 text-sm"
                                >{{ old('value') }}</textarea>

                                <p class="mt-1 text-xs text-gray-500">
                                    Với image/file, hãy tạo cấu hình trước rồi upload trong nhóm.
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Mô tả
                                </label>

                                <textarea
                                    name="description"
                                    rows="3"
                                    class="w-full rounded-lg border-gray-300 px-4 py-3 text-sm"
                                >{{ old('description') }}</textarea>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Thứ tự *
                                </label>

                                <input
                                    type="number"
                                    name="sort_order"
                                    value="{{ old('sort_order', 0) }}"
                                    min="0"
                                    required
                                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm"
                                >
                            </div>

                            <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-3">
                                <input type="hidden" name="is_public" value="0">

                                <input
                                    type="checkbox"
                                    name="is_public"
                                    value="1"
                                    @checked(old('is_public'))
                                    class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                >

                                <span class="text-sm font-medium text-gray-700">
                                    Cho phép công khai
                                </span>
                            </label>

                            <div class="md:col-span-2">
                                <button
                                    type="submit"
                                    class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white"
                                >
                                    Tạo cấu hình
                                </button>
                            </div>
                        </form>
                    </div>
                </details>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document
        .querySelectorAll('[data-setting-color-picker]')
        .forEach(function (picker) {
            const id = picker.getAttribute(
                'data-setting-color-picker'
            );

            const textInput = document.querySelector(
                '[data-setting-color-text="' + id + '"]'
            );

            picker.addEventListener('input', function () {
                if (textInput) {
                    textInput.value = picker.value;
                }
            });

            textInput?.addEventListener('input', function () {
                if (/^#[0-9a-fA-F]{6}$/.test(textInput.value)) {
                    picker.value = textInput.value;
                }
            });
        });
});
</script>
@endpush
