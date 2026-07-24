@extends('client.layouts.master')

@section('title', 'Liên hệ - ' . site_name())

@section('meta_description', 'Gửi yêu cầu hỗ trợ tới cửa hàng.')

@section('content')
    <section class="contact-page">
        <div class="client-container contact-page__container">
            <header class="contact-page__header">
                <p class="home-section__eyebrow">Hỗ trợ khách hàng</p>
                <h1>Chúng tôi luôn sẵn sàng hỗ trợ bạn</h1>
                <p>
                    Điền thông tin bên dưới, đội ngũ của chúng tôi sẽ tiếp nhận
                    và phản hồi yêu cầu của bạn trong thời gian sớm nhất.
                </p>
            </header>

            @if (session('success'))
                <div class="contact-alert contact-alert--success" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="contact-alert contact-alert--error" role="alert">
                    <strong>Vui lòng kiểm tra lại thông tin:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="contact-layout">
                <aside class="contact-info">
                    <div>
                        <span class="contact-info__label">Cosmetic Shop</span>
                        <h2>Bạn cần hỗ trợ vấn đề gì?</h2>
                        <p>
                            Gửi yêu cầu cho chúng tôi về đơn hàng, sản phẩm,
                            thanh toán hoặc các vấn đề khác.
                        </p>
                    </div>

                    <ul class="contact-info__list">
                        <li>
                            <span class="contact-info__icon">01</span>
                            <div>
                                <strong>Tiếp nhận nhanh chóng</strong>
                                <p>Yêu cầu được lưu và chuyển tới bộ phận phù hợp.</p>
                            </div>
                        </li>
                        <li>
                            <span class="contact-info__icon">02</span>
                            <div>
                                <strong>Thông tin được bảo mật</strong>
                                <p>Dữ liệu liên hệ chỉ được dùng để hỗ trợ bạn.</p>
                            </div>
                        </li>
                        <li>
                            <span class="contact-info__icon">03</span>
                            <div>
                                <strong>Hỗ trợ chính xác hơn</strong>
                                <p>Nhập mã đơn hàng nếu yêu cầu liên quan tới đơn mua.</p>
                            </div>
                        </li>
                    </ul>
                </aside>

                <form method="POST" action="{{ route('contact.store') }}" class="contact-form">
                    @csrf

                    <div class="contact-form__heading">
                        <h2>Gửi yêu cầu liên hệ</h2>
                        <p>Các trường có dấu <span>*</span> là bắt buộc.</p>
                    </div>

                    <div class="contact-form__grid">
                        <label class="contact-field">
                            <span>Họ và tên <b>*</b></span>
                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $user?->name) }}"
                                required
                                maxlength="150"
                                autocomplete="name"
                                placeholder="Nhập họ và tên"
                            >
                        </label>

                        <label class="contact-field">
                            <span>Email <b>*</b></span>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email', $user?->email) }}"
                                required
                                maxlength="255"
                                autocomplete="email"
                                placeholder="Nhập địa chỉ email"
                            >
                        </label>

                        <label class="contact-field">
                            <span>Số điện thoại</span>
                            <input
                                type="text"
                                name="phone"
                                value="{{ old('phone') }}"
                                maxlength="20"
                                autocomplete="tel"
                                placeholder="Nhập số điện thoại"
                            >
                        </label>

                        <label class="contact-field">
                            <span>Loại hỗ trợ <b>*</b></span>
                            <select name="type" required>
                                @foreach ($types as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type', 'general') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="contact-field contact-field--full">
                            <span>Mã đơn hàng <small>(nếu có)</small></span>
                            <input
                                type="text"
                                name="order_code"
                                value="{{ old('order_code', request('order_code')) }}"
                                maxlength="50"
                                placeholder="Ví dụ: ORD2026..."
                            >
                        </label>

                        <label class="contact-field contact-field--full">
                            <span>Tiêu đề <b>*</b></span>
                            <input
                                type="text"
                                name="subject"
                                value="{{ old('subject') }}"
                                required
                                maxlength="255"
                                placeholder="Nhập nội dung cần hỗ trợ"
                            >
                        </label>

                        <label class="contact-field contact-field--full">
                            <span>Nội dung <b>*</b></span>
                            <textarea
                                name="message"
                                rows="7"
                                required
                                maxlength="5000"
                                placeholder="Mô tả chi tiết vấn đề của bạn..."
                            >{{ old('message') }}</textarea>
                        </label>
                    </div>

                    <div class="contact-form__footer">
                        <p>Chúng tôi sẽ liên hệ lại qua email hoặc số điện thoại bạn cung cấp.</p>
                        <button type="submit" class="contact-submit-button">
                            Gửi yêu cầu
                            <span aria-hidden="true">→</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
