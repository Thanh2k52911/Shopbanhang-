@extends('client.layouts.master')

@section('title', 'Thông tin tài khoản - Cosmetic Shop')

@section('content')
    <section class="account-profile-page">
        <div class="client-container">
            <nav class="account-profile-page__breadcrumb">
                <a href="{{ route('home') }}">
                    Trang chủ
                </a>

                <span>/</span>

                <a href="{{ route('account.index') }}">
                    Tài khoản
                </a>

                <span>/</span>

                <span>Thông tin cá nhân</span>
            </nav>

            <header class="account-profile-page__header">
                <div>
                    <p class="home-section__eyebrow">
                        Hồ sơ tài khoản
                    </p>

                    <h1>Thông tin cá nhân</h1>

                    <p>
                        Cập nhật ảnh đại diện, họ tên và email
                        đăng nhập của bạn.
                    </p>
                </div>

                <a href="{{ route('account.index') }}">
                    Quay lại tài khoản
                </a>
            </header>

            @if (session('profile_success'))
                <div class="account-message account-message--success">
                    {{ session('profile_success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="account-message account-message--error">
                    <strong>
                        Vui lòng kiểm tra lại thông tin:
                    </strong>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="account-profile-page__layout">
                <section class="account-profile-card">
                    <h2>Cập nhật tài khoản</h2>

                    <form
                        action="{{ route(
                            'account.profile.update'
                        ) }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="account-profile-form"
                    >
                        @csrf
                        @method('PATCH')

                        <div class="account-avatar">
                            <div class="account-avatar__preview">
                                @if ($user->avatar)
                                    <img
                                        src="{{ asset(
                                            'storage/' . $user->avatar
                                        ) }}"
                                        alt="{{ $user->name }}"
                                        id="avatar-preview-image"
                                    >
                                @else
                                    <span id="avatar-preview-letter">
                                        {{ mb_strtoupper(
                                            mb_substr(
                                                $user->name,
                                                0,
                                                1
                                            )
                                        ) }}
                                    </span>

                                    <img
                                        src=""
                                        alt="Xem trước ảnh đại diện"
                                        id="avatar-preview-image"
                                        hidden
                                    >
                                @endif
                            </div>

                            <div class="account-avatar__content">
                                <label for="profile-avatar">
                                    Ảnh đại diện
                                </label>

                                <input
                                    type="file"
                                    id="profile-avatar"
                                    name="avatar"
                                    accept=".jpg,.jpeg,.png,.webp"
                                >

                                <small>
                                    JPG, JPEG, PNG hoặc WEBP.
                                    Dung lượng tối đa 2MB.
                                </small>
                            </div>
                        </div>

                        <div class="account-profile-field">
                            <label for="profile-name">
                                Họ và tên
                            </label>

                            <input
                                type="text"
                                id="profile-name"
                                name="name"
                                value="{{ old(
                                    'name',
                                    $user->name
                                ) }}"
                                autocomplete="name"
                                required
                            >
                        </div>

                        <div class="account-profile-field">
                            <label for="profile-email">
                                Email
                            </label>

                            <input
                                type="email"
                                id="profile-email"
                                name="email"
                                value="{{ old(
                                    'email',
                                    $user->email
                                ) }}"
                                autocomplete="email"
                                required
                            >
                        </div>

                        <button
                            type="submit"
                            class="account-profile-submit"
                        >
                            Lưu thay đổi
                        </button>
                    </form>
                </section>

                <aside class="account-profile-card">
                    <div class="account-profile-current-avatar">
                        @if ($user->avatar)
                            <img
                                src="{{ asset(
                                    'storage/' . $user->avatar
                                ) }}"
                                alt="{{ $user->name }}"
                            >
                        @else
                            <span>
                                {{ mb_strtoupper(
                                    mb_substr(
                                        $user->name,
                                        0,
                                        1
                                    )
                                ) }}
                            </span>
                        @endif
                    </div>

                    <h2>Thông tin hiện tại</h2>

                    <dl class="account-profile-information">
                        <div>
                            <dt>Họ và tên</dt>

                            <dd>{{ $user->name }}</dd>
                        </div>

                        <div>
                            <dt>Email</dt>

                            <dd>{{ $user->email }}</dd>
                        </div>

                        <div>
                            <dt>Xác minh email</dt>

                            <dd>
                                @if ($user->email_verified_at)
                                    Đã xác minh
                                @else
                                    Chưa xác minh
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt>Ngày tham gia</dt>

                            <dd>
                                {{ $user->created_at
                                    ?->format('d/m/Y') }}
                            </dd>
                        </div>
                    </dl>

                    <a
                        href="{{ route('account.orders.index') }}"
                        class="account-profile-orders-link"
                    >
                        Xem đơn hàng của tôi
                    </a>
                </aside>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener(
            'DOMContentLoaded',
            () => {
                const avatarInput = document.getElementById(
                    'profile-avatar'
                );

                const previewImage = document.getElementById(
                    'avatar-preview-image'
                );

                const previewLetter = document.getElementById(
                    'avatar-preview-letter'
                );

                avatarInput?.addEventListener(
                    'change',
                    () => {
                        const file = avatarInput.files?.[0];

                        if (!file || !previewImage) {
                            return;
                        }

                        const imageUrl =
                            URL.createObjectURL(file);

                        previewImage.src = imageUrl;
                        previewImage.hidden = false;

                        if (previewLetter) {
                            previewLetter.hidden = true;
                        }
                    }
                );
            }
        );
    </script>
@endpush
