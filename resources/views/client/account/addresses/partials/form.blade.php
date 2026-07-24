@if ($errors->any())
    <div class="account-message account-message--error">
        <strong>Vui lòng kiểm tra lại:</strong>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="address-form-intro">
    <div class="address-form-intro__icon" aria-hidden="true">⌖</div>

    <div>
        <h2>Thông tin người nhận</h2>
        <p>
            Chọn địa chỉ theo thứ tự Tỉnh/Thành phố,
            Quận/Huyện rồi Phường/Xã để tránh nhập sai.
        </p>
    </div>
</div>

<div class="checkout-grid address-form-grid">
    <div class="checkout-field">
        <label for="receiver_name">Tên người nhận</label>
        <input
            id="receiver_name"
            name="receiver_name"
            type="text"
            required
            maxlength="150"
            value="{{ old('receiver_name', $address?->receiver_name) }}"
            placeholder="Ví dụ: Nguyễn Văn An"
        >
    </div>

    <div class="checkout-field">
        <label for="phone">Số điện thoại</label>
        <input
            id="phone"
            name="phone"
            type="tel"
            inputmode="tel"
            autocomplete="tel"
            required
            maxlength="20"
            value="{{ old('phone', $address?->phone) }}"
            placeholder="Ví dụ: 0912345678"
        >
    </div>

    <div class="checkout-field checkout-field--full">
        <label for="address">Số nhà, tên đường</label>
        <input
            id="address"
            name="address"
            type="text"
            autocomplete="street-address"
            required
            maxlength="500"
            value="{{ old('address', $address?->address) }}"
            placeholder="Ví dụ: Số 1 Đại Cồ Việt"
        >
    </div>
</div>

<div
    class="vietnam-address-selector"
    data-vietnam-address
    data-initial-province="{{ old('province', $address?->province) }}"
    data-initial-district="{{ old('district', $address?->district) }}"
    data-initial-ward="{{ old('ward', $address?->ward) }}"
>
    <div
        class="checkout-grid address-form-grid"
        data-vn-address-select-fields
    >
        <div class="checkout-field">
            <label for="province">Tỉnh/Thành phố</label>
            <select
                id="province"
                name="province"
                data-field-name="province"
                data-vn-province
                required
            >
                <option value="">Đang tải...</option>
            </select>
        </div>

        <div class="checkout-field">
            <label for="district">Quận/Huyện</label>
            <select
                id="district"
                name="district"
                data-field-name="district"
                data-vn-district
                required
                disabled
            >
                <option value="">Chọn Tỉnh/Thành phố trước</option>
            </select>
        </div>

        <div class="checkout-field checkout-field--full">
            <label for="ward">Phường/Xã</label>
            <select
                id="ward"
                name="ward"
                data-field-name="ward"
                data-vn-ward
                required
                disabled
            >
                <option value="">Chọn Quận/Huyện trước</option>
            </select>
        </div>
    </div>

    <div
        class="checkout-grid address-form-grid"
        data-vn-address-manual-fields
        hidden
    >
        <div class="checkout-field">
            <label for="manual-province">Tỉnh/Thành phố</label>
            <input
                id="manual-province"
                type="text"
                data-vn-manual-province
                placeholder="Nhập Tỉnh/Thành phố"
            >
        </div>

        <div class="checkout-field">
            <label for="manual-district">Quận/Huyện</label>
            <input
                id="manual-district"
                type="text"
                data-vn-manual-district
                placeholder="Nhập Quận/Huyện"
            >
        </div>

        <div class="checkout-field checkout-field--full">
            <label for="manual-ward">Phường/Xã</label>
            <input
                id="manual-ward"
                type="text"
                data-vn-manual-ward
                placeholder="Nhập Phường/Xã"
            >
        </div>
    </div>

    <div class="vietnam-address-selector__footer">
        <p data-vn-address-status hidden></p>

        <button
            type="button"
            class="vietnam-address-selector__manual"
            data-vn-address-manual-toggle
        >
            Nhập địa chỉ hành chính thủ công
        </button>
    </div>
</div>

<label class="address-default-option">
    <input
        type="checkbox"
        name="is_default"
        value="1"
        @checked(old('is_default', $address?->is_default))
    >

    <span>
        <strong>Đặt làm địa chỉ mặc định</strong>
        <small>Tự động chọn địa chỉ này khi thanh toán.</small>
    </span>
</label>

<div class="account-dashboard-actions address-form-actions">
    <button type="submit">Lưu địa chỉ</button>
    <a href="{{ route('account.addresses.index') }}">Hủy</a>
</div>

@once
    @push('styles')
        <style>
            .address-form-intro {
                display: flex;
                align-items: center;
                gap: 1rem;
                margin-bottom: 1.5rem;
                padding: 1rem 1.1rem;
                border: 1px solid #f4d2e1;
                border-radius: 1rem;
                background: linear-gradient(135deg, #fff4f9, #fff);
            }

            .address-form-intro__icon {
                display: grid;
                flex: 0 0 48px;
                width: 48px;
                height: 48px;
                place-items: center;
                border-radius: 999px;
                background: #db2777;
                color: #fff;
                font-size: 1.35rem;
                font-weight: 900;
            }

            .address-form-intro h2,
            .address-form-intro p {
                margin: 0;
            }

            .address-form-intro h2 {
                font-size: 1.05rem;
            }

            .address-form-intro p {
                margin-top: .25rem;
                color: #6b7280;
                line-height: 1.6;
            }

            .address-form-grid {
                gap: 1.1rem;
            }

            .address-form-grid input,
            .address-form-grid select {
                width: 100%;
                min-height: 54px;
                box-sizing: border-box;
                border: 1px solid #d9dee8;
                border-radius: .85rem;
                padding: 0 1rem;
                background: #fff;
            }

            .address-form-grid input:focus,
            .address-form-grid select:focus {
                outline: 0;
                border-color: #ec4899;
                box-shadow: 0 0 0 3px rgb(236 72 153 / 12%);
            }

            .address-form-grid select:disabled {
                cursor: wait;
                background: #f8fafc;
                color: #9ca3af;
            }

            .vietnam-address-selector {
                margin-top: 1.1rem;
                padding: 1.1rem;
                border: 1px solid #edf0f5;
                border-radius: 1rem;
                background: #fbfcfe;
            }

            .vietnam-address-selector__footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
                margin-top: .9rem;
            }

            .vietnam-address-selector__footer p {
                margin: 0;
                color: #64748b;
                font-size: .82rem;
            }

            .vietnam-address-selector__footer p[data-type="success"] {
                color: #047857;
            }

            .vietnam-address-selector__footer p[data-type="warning"] {
                color: #b45309;
            }

            .vietnam-address-selector__footer p[data-type="error"] {
                color: #b91c1c;
            }

            .vietnam-address-selector__manual {
                border: 0;
                background: transparent;
                color: #db2777;
                font-weight: 800;
                cursor: pointer;
            }

            .address-default-option {
                display: flex;
                align-items: flex-start;
                gap: .75rem;
                margin-top: 1.25rem;
                padding: 1rem;
                border: 1px solid #e5e7eb;
                border-radius: .9rem;
                cursor: pointer;
            }

            .address-default-option input {
                width: 18px;
                height: 18px;
                margin-top: .2rem;
                accent-color: #db2777;
            }

            .address-default-option span {
                display: grid;
                gap: .2rem;
            }

            .address-default-option small {
                color: #6b7280;
            }

            .address-form-actions {
                display: flex;
                justify-content: flex-end;
                gap: .75rem;
                margin-top: 1.5rem;
            }

            .address-form-actions button,
            .address-form-actions a {
                width: auto;
                min-width: 150px;
                min-height: 48px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: .8rem;
                text-decoration: none;
            }

            @media (max-width: 640px) {
                .vietnam-address-selector__footer,
                .address-form-actions {
                    align-items: stretch;
                    flex-direction: column;
                }

                .address-form-actions button,
                .address-form-actions a {
                    width: 100%;
                }
            }
        </style>
    @endpush
@endonce
