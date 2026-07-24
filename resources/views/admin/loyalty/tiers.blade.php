@extends('admin.layouts.master')

@section('title', 'Quản lý hạng Loyalty')
@section('page-title', 'Quản lý hạng Loyalty')
@section('page-description', 'Cấu hình điều kiện, quyền lợi, thứ tự và trạng thái các hạng thành viên.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Hạng Loyalty
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Điều chỉnh điều kiện lên hạng, hệ số điểm và ưu đãi thành viên.
            </p>
        </div>

        <a
            href="{{ route('admin.loyalty.index') }}"
            class="inline-flex justify-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
        >
            Quay lại Loyalty
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

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Tổng số hạng', $statistics['total'], 'text-gray-900'],
            ['Đang hoạt động', $statistics['active'], 'text-green-600'],
            ['Đang tắt', $statistics['inactive'], 'text-orange-600'],
            ['Tài khoản đã gán hạng', $statistics['assigned_accounts'], 'text-pink-600'],
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

    <section class="rounded-xl border border-blue-200 bg-blue-50 p-5">
        <h3 class="font-bold text-blue-900">
            Quy tắc cấu hình
        </h3>

        <div class="mt-3 grid grid-cols-1 gap-3 text-sm leading-6 text-blue-800 md:grid-cols-2">
            <p>
                Các hạng nên được sắp xếp từ thấp đến cao bằng trường
                <strong>Thứ tự</strong>.
            </p>

            <p>
                Không nên đặt điều kiện của hạng thấp cao hơn hạng đứng sau.
            </p>

            <p>
                Tắt một hạng không tự động gỡ hạng khỏi tài khoản đã được gán.
            </p>

            <p>
                Thay đổi hệ số điểm hoặc giảm giá chỉ áp dụng theo logic xử lý đơn hàng của hệ thống.
            </p>
        </div>
    </section>

    <div class="space-y-5">
        @forelse ($tiers as $tier)
            @php
                $statusClass = (int) $tier->status === 1
                    ? 'bg-green-100 text-green-700'
                    : 'bg-gray-100 text-gray-700';
            @endphp

            <section
                id="tier-{{ $tier->id }}"
                class="overflow-hidden rounded-xl border border-gray-200 bg-white"
            >
                <div class="border-b border-gray-200 px-5 py-4">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex-1 min-w-0">

    <div class="flex flex-wrap items-center gap-2">

        <h3
            class="text-xl font-bold text-gray-900"
        >
            {{ $tier->name }}
        </h3>

        <span
            class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}"
        >
            {{ (int)$tier->status === 1
                ? 'Đang hoạt động'
                : 'Đang tắt'
            }}
        </span>

    </div>

    <div
        class="mt-1 text-sm text-gray-500"
    >
        Mã:
        <strong>{{ $tier->code }}</strong>

        ·

        Thứ tự:
        <strong>{{ $tier->sort_order }}</strong>
    </div>

</div>

                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div class="rounded-lg bg-gray-50 px-3 py-2 text-center">
                                <p class="text-xs text-gray-500">
                                    Tài khoản
                                </p>

                                <strong class="mt-1 block text-gray-900">
                                    {{ number_format((int) $tier->accounts_count) }}
                                </strong>
                            </div>

                            <div class="rounded-lg bg-gray-50 px-3 py-2 text-center">
                                <p class="text-xs text-gray-500">
                                    Điểm tối thiểu
                                </p>

                                <strong class="mt-1 block text-gray-900">
                                    {{ number_format((int) $tier->minimum_points) }}
                                </strong>
                            </div>

                            <div class="rounded-lg bg-gray-50 px-3 py-2 text-center">
                                <p class="text-xs text-gray-500">
                                    Nhân điểm
                                </p>

                                <strong class="mt-1 block text-green-700">
                                    x{{ number_format((float) $tier->point_multiplier, 2) }}
                                </strong>
                            </div>

                            <div class="rounded-lg bg-gray-50 px-3 py-2 text-center">
                                <p class="text-xs text-gray-500">
                                    Giảm giá
                                </p>

                                <strong class="mt-1 block text-pink-600">
                                    {{ number_format((float) $tier->discount_percent, 2) }}%
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <details
                    class="group"
                    @if ($errors->any() && (string) old('_tier_id') === (string) $tier->id)
                        open
                    @endif
                >
                    <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-4 font-semibold text-gray-700 hover:bg-gray-50">
                        <span>
                            Chỉnh sửa cấu hình
                        </span>

                        <span class="transition group-open:rotate-180">
                            ▼
                        </span>
                    </summary>

                    <div class="border-t border-gray-200 p-5">
                        <input
                            type="hidden"
                            name="_tier_id"
                            value="{{ $tier->id }}"
                            form="tier-form-{{ $tier->id }}"
                        >

                        @include(
                            'admin.loyalty.partials.tier-form',
                            ['tier' => $tier]
                        )
                    </div>
                </details>
            </section>
        @empty
            <section class="rounded-xl border border-gray-200 bg-white px-5 py-16 text-center">
                <p class="text-gray-500">
                    Chưa có hạng Loyalty.
                </p>
            </section>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-color-picker]').forEach(function (picker) {
        const id = picker.getAttribute('data-color-picker');
        const textInput = document.querySelector(
            '[data-color-text="' + id + '"]'
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
