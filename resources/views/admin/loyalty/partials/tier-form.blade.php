@php
    $formId = 'tier-form-' . $tier->id;
@endphp

<form
    id="{{ $formId }}"
    method="POST"
    action="{{ route('admin.loyalty.tiers.update', $tier->id) }}"
    class="space-y-5"
>
    @csrf
    @method('PATCH')

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label
                for="name_{{ $tier->id }}"
                class="mb-1.5 block text-sm font-medium text-gray-700"
            >
                Tên hạng *
            </label>

            <input
                id="name_{{ $tier->id }}"
                type="text"
                name="name"
                value="{{ old('name', $tier->name) }}"
                maxlength="100"
                required
                class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
            >
        </div>

        <div>
            <label
                for="code_{{ $tier->id }}"
                class="mb-1.5 block text-sm font-medium text-gray-700"
            >
                Mã hạng *
            </label>

            <input
                id="code_{{ $tier->id }}"
                type="text"
                name="code"
                value="{{ old('code', $tier->code) }}"
                maxlength="50"
                required
                class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm lowercase focus:border-pink-500 focus:ring-pink-500"
            >
        </div>
    </div>

    <div>
        <label
            for="description_{{ $tier->id }}"
            class="mb-1.5 block text-sm font-medium text-gray-700"
        >
            Mô tả
        </label>

        <textarea
            id="description_{{ $tier->id }}"
            name="description"
            rows="4"
            maxlength="5000"
            class="w-full rounded-lg border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"
        >{{ old('description', $tier->description) }}</textarea>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div>
            <label
                for="minimum_spending_{{ $tier->id }}"
                class="mb-1.5 block text-sm font-medium text-gray-700"
            >
                Chi tiêu tối thiểu *
            </label>

            <input
                id="minimum_spending_{{ $tier->id }}"
                type="number"
                name="minimum_spending"
                value="{{ old('minimum_spending', $tier->minimum_spending) }}"
                min="0"
                step="0.01"
                required
                class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
            >
        </div>

        <div>
            <label
                for="minimum_points_{{ $tier->id }}"
                class="mb-1.5 block text-sm font-medium text-gray-700"
            >
                Điểm tối thiểu *
            </label>

            <input
                id="minimum_points_{{ $tier->id }}"
                type="number"
                name="minimum_points"
                value="{{ old('minimum_points', $tier->minimum_points) }}"
                min="0"
                step="1"
                required
                class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
            >
        </div>

        <div>
            <label
                for="point_multiplier_{{ $tier->id }}"
                class="mb-1.5 block text-sm font-medium text-gray-700"
            >
                Hệ số nhân điểm *
            </label>

            <input
                id="point_multiplier_{{ $tier->id }}"
                type="number"
                name="point_multiplier"
                value="{{ old('point_multiplier', $tier->point_multiplier) }}"
                min="0"
                max="999.99"
                step="0.01"
                required
                class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
            >
        </div>

        <div>
            <label
                for="discount_percent_{{ $tier->id }}"
                class="mb-1.5 block text-sm font-medium text-gray-700"
            >
                Giảm giá (%) *
            </label>

            <input
                id="discount_percent_{{ $tier->id }}"
                type="number"
                name="discount_percent"
                value="{{ old('discount_percent', $tier->discount_percent) }}"
                min="0"
                max="100"
                step="0.01"
                required
                class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
            >
        </div>
    </div>


    <section class="rounded-xl border border-pink-200 bg-pink-50 p-5">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-semibold text-gray-900">
                    Voucher thưởng khi lần đầu đạt hạng
                </h3>

                <p class="mt-1 text-sm text-gray-600">
                    Hệ thống tự tạo voucher cá nhân, lưu vào Ví voucher và chỉ phát một lần cho mỗi hạng.
                </p>
            </div>

            <label class="flex items-center gap-3 rounded-lg bg-white px-4 py-3 shadow-sm">
                <input type="hidden" name="reward_enabled" value="0">

                <input
                    type="checkbox"
                    name="reward_enabled"
                    value="1"
                    @checked((int) old('reward_enabled', $tier->reward_enabled ?? 0) === 1)
                    class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                >

                <span class="text-sm font-medium text-gray-900">
                    Bật thưởng thăng hạng
                </span>
            </label>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Tên voucher
                </label>

                <input
                    type="text"
                    name="reward_name"
                    value="{{ old('reward_name', $tier->reward_name ?? '') }}"
                    maxlength="255"
                    placeholder="Ví dụ: Quà thăng hạng Vàng"
                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Loại voucher
                </label>

                <select
                    name="reward_discount_type"
                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
                    <option value="">Chọn loại ưu đãi</option>
                    <option value="percentage" @selected(old('reward_discount_type', $tier->reward_discount_type ?? '') === 'percentage')>
                        Giảm theo phần trăm
                    </option>
                    <option value="fixed" @selected(old('reward_discount_type', $tier->reward_discount_type ?? '') === 'fixed')>
                        Giảm số tiền cố định
                    </option>
                    <option value="free_shipping" @selected(old('reward_discount_type', $tier->reward_discount_type ?? '') === 'free_shipping')>
                        Miễn phí vận chuyển
                    </option>
                </select>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Giá trị giảm
                </label>

                <input
                    type="number"
                    name="reward_discount_value"
                    value="{{ old('reward_discount_value', $tier->reward_discount_value ?? '') }}"
                    min="0"
                    step="0.01"
                    placeholder="15 hoặc 100000"
                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Giảm tối đa
                </label>

                <input
                    type="number"
                    name="reward_maximum_discount"
                    value="{{ old('reward_maximum_discount', $tier->reward_maximum_discount ?? '') }}"
                    min="0"
                    step="0.01"
                    placeholder="Chỉ dùng cho giảm %"
                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Đơn tối thiểu
                </label>

                <input
                    type="number"
                    name="reward_minimum_order_amount"
                    value="{{ old('reward_minimum_order_amount', $tier->reward_minimum_order_amount ?? 0) }}"
                    min="0"
                    step="0.01"
                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Thời hạn (ngày)
                </label>

                <input
                    type="number"
                    name="reward_valid_days"
                    value="{{ old('reward_valid_days', $tier->reward_valid_days ?? 30) }}"
                    min="1"
                    max="3650"
                    step="1"
                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>
        </div>

        <div class="mt-4">
            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                Mô tả voucher
            </label>

            <textarea
                name="reward_description"
                rows="3"
                maxlength="5000"
                placeholder="Quyền lợi dành riêng cho khách vừa thăng hạng..."
                class="w-full rounded-lg border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"
            >{{ old('reward_description', $tier->reward_description ?? '') }}</textarea>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div>
            <label
                for="color_{{ $tier->id }}"
                class="mb-1.5 block text-sm font-medium text-gray-700"
            >
                Màu hiển thị
            </label>

            <div class="flex items-center gap-3">
                <input
                    id="color_{{ $tier->id }}"
                    type="color"
                    value="{{ old('color', $tier->color ?: '#ec4899') }}"
                    class="h-11 w-14 rounded border border-gray-300 bg-white p-1"
                    data-color-picker="{{ $tier->id }}"
                >

                <input
                    type="text"
                    name="color"
                    value="{{ old('color', $tier->color) }}"
                    maxlength="30"
                    placeholder="#ec4899"
                    class="min-w-0 flex-1 rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                    data-color-text="{{ $tier->id }}"
                >
            </div>
        </div>

        <div>
            <label
                for="icon_{{ $tier->id }}"
                class="mb-1.5 block text-sm font-medium text-gray-700"
            >
                Icon
            </label>

            <input
                id="icon_{{ $tier->id }}"
                type="text"
                name="icon"
                value="{{ old('icon', $tier->icon) }}"
                maxlength="500"
                placeholder="🥉, 🥈, 🥇 hoặc đường dẫn icon"
                class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
            >
        </div>

        <div>
            <label
                for="sort_order_{{ $tier->id }}"
                class="mb-1.5 block text-sm font-medium text-gray-700"
            >
                Thứ tự *
            </label>

            <input
                id="sort_order_{{ $tier->id }}"
                type="number"
                name="sort_order"
                value="{{ old('sort_order', $tier->sort_order) }}"
                min="0"
                step="1"
                required
                class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
            >
        </div>

        <div class="flex items-end">
            <label class="flex w-full items-start gap-3 rounded-lg border border-gray-200 p-3">
                <input type="hidden" name="status" value="0">

                <input
                    type="checkbox"
                    name="status"
                    value="1"
                    @checked((int) old('status', $tier->status) === 1)
                    class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                >

                <span>
                    <strong class="block text-sm text-gray-900">
                        Đang hoạt động
                    </strong>

                    <span class="mt-1 block text-xs text-gray-500">
                        Cho phép sử dụng hạng này.
                    </span>
                </span>
            </label>
        </div>
    </div>

    <div class="flex flex-col gap-3 border-t border-gray-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-gray-500">
            Cập nhật gần nhất:
            {{ $tier->updated_at
                ? \Carbon\Carbon::parse($tier->updated_at)->format('d/m/Y H:i')
                : 'Chưa xác định' }}
        </p>

        <button
            type="submit"
            class="rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-pink-700"
        >
            Lưu thay đổi
        </button>
    </div>
</form>
