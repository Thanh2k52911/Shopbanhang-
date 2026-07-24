@php
    $rowIndex = $index;
    $productId = $discount->product_id ?? '';
    $percent = $discount->discount_percent ?? '';
    $amount = $discount->discount_amount ?? '';
    $limit = $discount->limit_quantity ?? '';
    $type = $percent !== null && $percent !== '' ? 'percent' : 'amount';
@endphp

<div data-discount-row class="rounded-xl border border-gray-200 bg-gray-50 p-4">
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-6">
        <div class="lg:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Sản phẩm</label>
            <select data-field="product_id"
                    name="discounts[{{ $rowIndex }}][product_id]"
                    required
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                <option value="">-- Chọn sản phẩm --</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected((string) $productId === (string) $product->id)>
                        {{ $product->name }}{{ (int) $product->status === 1 ? '' : ' (Ngừng bán)' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Loại giảm</label>
            <select data-discount-type class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                <option value="percent" @selected($type === 'percent')>Phần trăm</option>
                <option value="amount" @selected($type === 'amount')>Số tiền</option>
            </select>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Giảm %</label>
            <input data-percent data-field="discount_percent"
                   type="number"
                   name="discounts[{{ $rowIndex }}][discount_percent]"
                   value="{{ $percent }}"
                   min="0.01" max="100" step="0.01"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Giảm tiền</label>
            <input data-amount data-field="discount_amount"
                   type="number"
                   name="discounts[{{ $rowIndex }}][discount_amount]"
                   value="{{ $amount }}"
                   min="0.01" step="0.01"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Giới hạn</label>
            <input data-field="limit_quantity"
                   type="number"
                   name="discounts[{{ $rowIndex }}][limit_quantity]"
                   value="{{ $limit }}"
                   min="{{ max(1, (int) $soldQuantity) }}"
                   step="1"
                   placeholder="Không giới hạn"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
        </div>
    </div>

    <div class="mt-3 flex items-center justify-between gap-4">
        <p class="text-xs text-gray-500">
            Đã bán: {{ number_format((int) $soldQuantity) }}
        </p>
        <button data-remove-row type="button" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600">
            Xóa dòng
        </button>
    </div>
</div>
