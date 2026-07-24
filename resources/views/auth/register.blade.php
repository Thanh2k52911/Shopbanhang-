<x-guest-layout>
    <div class="auth-panel__heading">
        <span>Tạo tài khoản mới</span>
        <h1>Đăng ký</h1>
        <p>Tham gia Cosmetic Shop để nhận ưu đãi và theo dõi đơn hàng.</p>
    </div>

    @if ($errors->any())
        <div class="auth-errors" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="auth-form auth-form--register" method="POST" action="{{ route('register') }}">
        @csrf

        <div class="auth-field">
            <label for="name">Họ và tên</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                placeholder="Nhập họ và tên"
                required
                autofocus
                autocomplete="name"
            >
        </div>

        <div class="auth-field">
            <label for="email">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Nhập địa chỉ email"
                required
                autocomplete="username"
            >
        </div>

        <div class="auth-field">
            <label for="password">Mật khẩu</label>
            <input
                id="password"
                type="password"
                name="password"
                placeholder="Tạo mật khẩu"
                required
                autocomplete="new-password"
            >
        </div>

        <div class="auth-field">
            <label for="password_confirmation">Xác nhận mật khẩu</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                placeholder="Nhập lại mật khẩu"
                required
                autocomplete="new-password"
            >
        </div>

        <button class="auth-submit" type="submit">Đăng ký</button>
    </form>

    <p class="auth-panel__switch">
        Đã có tài khoản?
        <a href="{{ route('login') }}">Đăng nhập</a>
    </p>
</x-guest-layout>
