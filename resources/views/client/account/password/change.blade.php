@extends('client.layouts.master')

@section('title', 'Đặt mật khẩu mới - Cosmetic Shop')

@section('content')
    <section class="account-password-page">
        <div class="client-container">
            <div class="account-password-card">
                <p class="home-section__eyebrow">
                    Hoàn tất thay đổi
                </p>

                <h1>Đặt mật khẩu mới</h1>

                <p>
                    Mật khẩu mới nên có ít nhất 8 ký tự và khác
                    mật khẩu hiện tại.
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
                    action="{{ route('account.password.update') }}"
                    method="POST"
                    class="account-password-form"
                >
                    @csrf
                    @method('PATCH')

                    <div class="account-profile-field">
                        <label for="new-password">
                            Mật khẩu mới
                        </label>

                        <input
                            type="password"
                            id="new-password"
                            name="password"
                            autocomplete="new-password"
                            required
                            autofocus
                        >
                    </div>

                    <div class="account-profile-field">
                        <label for="new-password-confirmation">
                            Xác nhận mật khẩu mới
                        </label>

                        <input
                            type="password"
                            id="new-password-confirmation"
                            name="password_confirmation"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <button
                        type="submit"
                        class="account-profile-submit"
                    >
                        Đổi mật khẩu
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
