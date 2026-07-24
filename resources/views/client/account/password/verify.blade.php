@extends('client.layouts.master')

@section('title', 'Nhập mã OTP - Cosmetic Shop')

@section('content')
    <section class="account-password-page">
        <div class="client-container">
            <div class="account-password-card">
                <p class="home-section__eyebrow">
                    Xác thực email
                </p>

                <h1>Nhập mã OTP</h1>

                <p>
                    Mã xác thực đã được gửi tới
                    <strong>{{ $maskedEmail }}</strong>.
                    Mã có hiệu lực trong 10 phút.
                </p>

                @if (session('otp_success'))
                    <div class="account-message account-message--success">
                        {{ session('otp_success') }}
                    </div>
                @endif

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
                    action="{{ route('account.password.otp.verify') }}"
                    method="POST"
                    class="account-password-form"
                >
                    @csrf

                    <div class="account-profile-field">
                        <label for="otp">
                            Mã OTP gồm 6 chữ số
                        </label>

                        <input
                            type="text"
                            id="otp"
                            name="otp"
                            value="{{ old('otp') }}"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            autocomplete="one-time-code"
                            required
                            autofocus
                        >
                    </div>

                    <button
                        type="submit"
                        class="account-profile-submit"
                    >
                        Xác minh OTP
                    </button>
                </form>

                <form
                    action="{{ route('account.password.otp.send') }}"
                    method="POST"
                    class="account-password-resend"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="current_password"
                        value=""
                    >

                    <p>
                        Muốn gửi lại mã, hãy quay lại bước nhập
                        mật khẩu hiện tại.
                    </p>

                    <a href="{{ route('account.password.request') }}">
                        Quay lại
                    </a>
                </form>
            </div>
        </div>
    </section>
@endsection
