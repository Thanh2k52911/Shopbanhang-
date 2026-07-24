@extends('client.layouts.master')

@section('title', 'Sửa địa chỉ - Cosmetic Shop')

@section('content')
<section class="account-profile-page">
    <div class="client-container">
        <nav class="account-profile-page__breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a><span>/</span>
            <a href="{{ route('account.addresses.index') }}">Sổ địa chỉ</a><span>/</span>
            <span>Sửa địa chỉ</span>
        </nav>

        <header class="account-profile-page__header">
            <div><p class="home-section__eyebrow">Địa chỉ nhận hàng</p><h1>Sửa địa chỉ</h1></div>
            <a href="{{ route('account.addresses.index') }}">Quay lại</a>
        </header>

        <section class="account-profile-card">
            <form method="POST" action="{{ route('account.addresses.update', $address) }}">
                @csrf
                @method('PUT')
                @include('client.account.addresses.partials.form', ['address' => $address])
            </form>
        </section>
    </div>
</section>
@endsection
