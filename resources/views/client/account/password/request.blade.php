@extends('client.layouts.master')

@section('title', 'Xác thực đổi mật khẩu - Cosmetic Shop')

@section('content')
    <section class="account-password-page">
        <div class="client-container">
            <div class="account-password-card">
                <p class="home-section__eyebrow">
                    Bảo mật tài khoản
                </p>

                <h1>Xác thực đổi mật khẩu</h1>

                <p>
                    Nhập mật khẩu hiện tại. Hệ thống sẽ gửi mã OTP
                    đến email {{ auth()->user()->email }}.
                </p>

                @if ($errors->any())
                    <div class="account-message account-message--error">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    action="{{ route('account.password.otp.send') }}"
                    method="POST"
                    class="account-password-form"
                >
                    @csrf

                    <div class="account-profile-field">
                        <label for="current-password">
                            Mật khẩu hiện tại
                        </label>

                        <input
                            type="password"
                            id="current-password"
                            name="current_password"
                            autocomplete="current-password"
                            required
                            autofocus
                        >
                    </div>

                    <button
                        type="submit"
                        class="account-profile-submit"
                    >
                        Gửi mã OTP
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
