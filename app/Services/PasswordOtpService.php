<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class PasswordOtpService
{
    private const OTP_MINUTES = 10;

    private const VERIFIED_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    public function generate(
        int $userId,
        string $sessionId
    ): string {
        $otp = (string) random_int(100000, 999999);

        Cache::put(
            $this->otpKey($userId, $sessionId),
            [
                'hash' => Hash::make($otp),
                'attempts' => 0,
            ],
            now()->addMinutes(self::OTP_MINUTES)
        );

        /*
         * Khi gửi OTP mới, trạng thái xác minh cũ
         * phải bị xóa.
         */
        Cache::forget(
            $this->verifiedKey($userId, $sessionId)
        );

        return $otp;
    }

    public function verify(
        int $userId,
        string $sessionId,
        string $otp
    ): bool {
        $key = $this->otpKey($userId, $sessionId);

        $data = Cache::get($key);

        if (
            !is_array($data)
            || !isset($data['hash'], $data['attempts'])
        ) {
            return false;
        }

        $attempts = (int) $data['attempts'];

        if ($attempts >= self::MAX_ATTEMPTS) {
            Cache::forget($key);

            return false;
        }

        if (!Hash::check($otp, $data['hash'])) {
            $attempts++;

            if ($attempts >= self::MAX_ATTEMPTS) {
                Cache::forget($key);
            } else {
                /*
                 * Lưu lại với thời hạn 10 phút tính từ lần
                 * nhập sai hiện tại.
                 */
                Cache::put(
                    $key,
                    [
                        'hash' => $data['hash'],
                        'attempts' => $attempts,
                    ],
                    now()->addMinutes(self::OTP_MINUTES)
                );
            }

            return false;
        }

        Cache::forget($key);

        Cache::put(
            $this->verifiedKey($userId, $sessionId),
            true,
            now()->addMinutes(self::VERIFIED_MINUTES)
        );

        return true;
    }

    public function isVerified(
        int $userId,
        string $sessionId
    ): bool {
        return Cache::get(
            $this->verifiedKey($userId, $sessionId)
        ) === true;
    }

    public function clear(
        int $userId,
        string $sessionId
    ): void {
        Cache::forget(
            $this->otpKey($userId, $sessionId)
        );

        Cache::forget(
            $this->verifiedKey($userId, $sessionId)
        );
    }

    public function hasPendingOtp(
        int $userId,
        string $sessionId
    ): bool {
        return Cache::has(
            $this->otpKey($userId, $sessionId)
        );
    }

    private function otpKey(
        int $userId,
        string $sessionId
    ): string {
        return sprintf(
            'password-change:otp:%d:%s',
            $userId,
            hash('sha256', $sessionId)
        );
    }

    private function verifiedKey(
        int $userId,
        string $sessionId
    ): string {
        return sprintf(
            'password-change:verified:%d:%s',
            $userId,
            hash('sha256', $sessionId)
        );
    }
}
