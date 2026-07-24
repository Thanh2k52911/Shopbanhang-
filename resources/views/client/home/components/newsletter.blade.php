<section class="home-newsletter">
    <div class="client-container">
        <div class="home-newsletter__box">
            <div class="home-newsletter__content">
                <p class="home-newsletter__eyebrow">
                    Ưu đãi dành riêng cho bạn
                </p>

                <h2 class="home-newsletter__title">
                    Đăng ký nhận thông tin khuyến mãi
                </h2>

                <p class="home-newsletter__description">
                    Nhận thông báo về sản phẩm mới, chương trình giảm giá
                    và bí quyết chăm sóc sắc đẹp từ Cosmetic Shop.
                </p>
            </div>

            <div class="home-newsletter__form-wrapper">
                @if (session('newsletter_success'))
                    <div class="home-newsletter__message home-newsletter__message--success">
                        {{ session('newsletter_success') }}
                    </div>
                @endif

                @if (session('newsletter_info'))
                    <div class="home-newsletter__message home-newsletter__message--info">
                        {{ session('newsletter_info') }}
                    </div>
                @endif

                @error('email')
                    <div class="home-newsletter__message home-newsletter__message--error">
                        {{ $message }}
                    </div>
                @enderror

                <form
                    action="{{ route('newsletter.store') }}"
                    method="POST"
                    class="home-newsletter__form"
                >
                    @csrf

                    <label
                        for="newsletter-email"
                        class="sr-only"
                    >
                        Địa chỉ email
                    </label>

                    <input
                        type="email"
                        id="newsletter-email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Nhập địa chỉ email của bạn"
                        autocomplete="email"
                        required
                    >

                    <button type="submit">
                        Đăng ký ngay
                    </button>
                </form>

                <p class="home-newsletter__privacy">
                    Chúng tôi không gửi thư rác và không chia sẻ email của bạn.
                </p>
            </div>
        </div>
    </div>
</section>
