<?php

namespace App\Services;

use App\Mail\EmailVerificationOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EmailVerificationOtpService
{
    private const OTP_TTL_MINUTES = 10;
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const MAX_ATTEMPTS = 5;

    public function send(User $user, bool $force = false): void
    {
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => 'Địa chỉ email này đã được xác minh.',
            ]);
        }

        $cooldownKey = $this->cooldownKey($user);

        if (! $force && Cache::has($cooldownKey)) {
            throw ValidationException::withMessages([
                'otp' => 'Vui lòng chờ 60 giây trước khi yêu cầu mã mới.',
            ]);
        }

        $otp = (string) random_int(100000, 999999);

        Cache::put(
            $this->otpKey($user),
            [
                'hash' => Hash::make($otp),
                'attempts' => 0,
                'email' => strtolower($user->email),
            ],
            now()->addMinutes(self::OTP_TTL_MINUTES)
        );

        Cache::put(
            $cooldownKey,
            true,
            now()->addSeconds(self::RESEND_COOLDOWN_SECONDS)
        );

        Mail::to($user->email)->send(
            new EmailVerificationOtpMail(
                user: $user,
                otp: $otp,
                expiresInMinutes: self::OTP_TTL_MINUTES
            )
        );
    }

    public function verify(User $user, string $otp): bool
    {
        if ($user->hasVerifiedEmail()) {
            return true;
        }

        $cacheKey = $this->otpKey($user);
        $payload = Cache::get($cacheKey);

        if (! is_array($payload)) {
            throw ValidationException::withMessages([
                'otp' => 'Mã xác minh đã hết hạn. Vui lòng yêu cầu mã mới.',
            ]);
        }

        if (
            strtolower((string) ($payload['email'] ?? ''))
            !== strtolower($user->email)
        ) {
            Cache::forget($cacheKey);

            throw ValidationException::withMessages([
                'otp' => 'Email đã thay đổi. Vui lòng yêu cầu mã xác minh mới.',
            ]);
        }

        $attempts = (int) ($payload['attempts'] ?? 0);

        if ($attempts >= self::MAX_ATTEMPTS) {
            Cache::forget($cacheKey);

            throw ValidationException::withMessages([
                'otp' => 'Bạn đã nhập sai quá nhiều lần. Vui lòng yêu cầu mã mới.',
            ]);
        }

        if (! Hash::check($otp, (string) $payload['hash'])) {
            $payload['attempts'] = $attempts + 1;

            Cache::put(
                $cacheKey,
                $payload,
                now()->addMinutes(self::OTP_TTL_MINUTES)
            );

            throw ValidationException::withMessages([
                'otp' => 'Mã xác minh không chính xác.',
            ]);
        }

        if (! $user->markEmailAsVerified()) {
            throw ValidationException::withMessages([
                'otp' => 'Không thể xác minh email. Vui lòng thử lại.',
            ]);
        }

        Cache::forget($cacheKey);
        Cache::forget($this->cooldownKey($user));

        return true;
    }

    public function clear(User $user): void
    {
        Cache::forget($this->otpKey($user));
        Cache::forget($this->cooldownKey($user));
    }

    private function otpKey(User $user): string
    {
        return 'email-verification-otp:' . $user->id;
    }

    private function cooldownKey(User $user): string
    {
        return 'email-verification-otp-cooldown:' . $user->id;
    }
}
