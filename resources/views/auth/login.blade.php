<x-guest-layout>
    <div class="auth-panel__heading">
        <span>Chào mừng trở lại</span>
        <h1>Đăng nhập</h1>
        <p>Đăng nhập để tiếp tục mua sắm và quản lý đơn hàng.</p>
    </div>

    <x-auth-session-status class="auth-status" :status="session('status')" />

    @if ($errors->any())
        <div class="auth-errors" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="auth-form" method="POST" action="{{ route('login') }}">
        @csrf

        <div class="auth-field">
            <label for="email">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Nhập địa chỉ email"
                required
                autofocus
                autocomplete="username"
            >
        </div>

        <div class="auth-field">
            <label for="password">Mật khẩu</label>
            <input
                id="password"
                type="password"
                name="password"
                placeholder="Nhập mật khẩu"
                required
                autocomplete="current-password"
            >
        </div>

        <div class="auth-form__options">
            <label class="auth-remember" for="remember_me">
                <input id="remember_me" type="checkbox" name="remember">
                <span>Ghi nhớ đăng nhập</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">Quên mật khẩu?</a>
            @endif
        </div>

        <button class="auth-submit" type="submit">Đăng nhập</button>
    </form>

    <p class="auth-panel__switch">
        Chưa có tài khoản?
        <a href="{{ route('register') }}">Đăng ký ngay</a>
    </p>
</x-guest-layout>
