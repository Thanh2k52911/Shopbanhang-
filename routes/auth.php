<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationOtpController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get(
        'register',
        [RegisteredUserController::class, 'create']
    )->name('register');

    Route::post(
        'register',
        [RegisteredUserController::class, 'store']
    )->middleware('throttle:5,1');

    Route::get(
        'login',
        [AuthenticatedSessionController::class, 'create']
    )->name('login');

    Route::post(
        'login',
        [AuthenticatedSessionController::class, 'store']
    )->middleware('throttle:10,1');

    Route::get(
        'forgot-password',
        [PasswordResetLinkController::class, 'create']
    )->name('password.request');

    Route::post(
        'forgot-password',
        [PasswordResetLinkController::class, 'store']
    )
        ->middleware('throttle:3,1')
        ->name('password.email');

    Route::get(
        'reset-password/{token}',
        [NewPasswordController::class, 'create']
    )->name('password.reset');

    Route::post(
        'reset-password',
        [NewPasswordController::class, 'store']
    )
        ->middleware('throttle:5,1')
        ->name('password.store');
});

Route::middleware('auth')->group(function (): void {
    /*
    |--------------------------------------------------------------------------
    | Xác minh email bằng OTP
    |--------------------------------------------------------------------------
    */

    Route::get(
        'verify-email',
        [EmailVerificationOtpController::class, 'show']
    )->name('verification.notice');

    Route::post(
        'verify-email/otp/send',
        [EmailVerificationOtpController::class, 'send']
    )
        ->middleware('throttle:3,1')
        ->name('verification.otp.send');

    Route::post(
        'verify-email/otp/verify',
        [EmailVerificationOtpController::class, 'verify']
    )
        ->middleware('throttle:6,1')
        ->name('verification.otp.verify');

    /*
    |--------------------------------------------------------------------------
    | Xác nhận và đổi mật khẩu
    |--------------------------------------------------------------------------
    */

    Route::get(
        'confirm-password',
        [ConfirmablePasswordController::class, 'show']
    )->name('password.confirm');

    Route::post(
        'confirm-password',
        [ConfirmablePasswordController::class, 'store']
    )->middleware('throttle:10,1');

    Route::put(
        'password',
        [PasswordController::class, 'update']
    )
        ->middleware('throttle:5,1')
        ->name('password.update');

    /*
    |--------------------------------------------------------------------------
    | Đăng xuất
    |--------------------------------------------------------------------------
    */

    Route::post(
        'logout',
        [AuthenticatedSessionController::class, 'destroy']
    )
        ->middleware('throttle:20,1')
        ->name('logout');
});
