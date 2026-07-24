<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\PasswordChangeOtpMail;
use App\Services\PasswordOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class PasswordOtpController extends Controller
{
    public function requestForm(): View
    {
        return view(
            'client.account.password.request'
        );
    }

    public function send(
        Request $request,
        PasswordOtpService $otpService
    ): RedirectResponse {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
        ], [
            'current_password.required' =>
                'Vui lòng nhập mật khẩu hiện tại.',

            'current_password.current_password' =>
                'Mật khẩu hiện tại không chính xác.',
        ]);

        unset($validated);

        $user = $request->user();
        $sessionId = $request->session()->getId();

        $cooldownKey = sprintf(
            'password-otp:cooldown:%d:%s',
            $user->id,
            $request->ip()
        );

        $hourlyKey = sprintf(
            'password-otp:hourly:%d:%s',
            $user->id,
            $request->ip()
        );

        if (
            RateLimiter::tooManyAttempts(
                $cooldownKey,
                1
            )
        ) {
            $seconds = RateLimiter::availableIn(
                $cooldownKey
            );

            return back()->withErrors([
                'otp' =>
                    "Vui lòng chờ {$seconds} giây trước khi gửi lại mã.",
            ]);
        }

        if (
            RateLimiter::tooManyAttempts(
                $hourlyKey,
                5
            )
        ) {
            $seconds = RateLimiter::availableIn(
                $hourlyKey
            );

            $minutes = max(
                1,
                (int) ceil($seconds / 60)
            );

            return back()->withErrors([
                'otp' =>
                    "Bạn đã gửi quá nhiều mã. Vui lòng thử lại sau {$minutes} phút.",
            ]);
        }

        $otp = $otpService->generate(
            (int) $user->id,
            $sessionId
        );

        try {
            Mail::to($user->email)->send(
                new PasswordChangeOtpMail(
                    $otp,
                    $user->name
                )
            );
        } catch (Throwable $exception) {
            report($exception);

            $otpService->clear(
                (int) $user->id,
                $sessionId
            );

            return back()->withErrors([
                'otp' =>
                    'Không thể gửi mã xác thực. Vui lòng thử lại.',
            ]);
        }

        RateLimiter::hit($cooldownKey, 60);
        RateLimiter::hit($hourlyKey, 3600);

        /*
         * Không lưu OTP rõ vào session.
         * Chỉ lưu email đã che để hiển thị.
         */
        $request->session()->put(
            'password_otp_email',
            $this->maskEmail($user->email)
        );

        return redirect()
            ->route('account.password.otp.form')
            ->with(
                'otp_success',
                'Mã xác thực đã được gửi đến email của bạn.'
            );
    }

    public function otpForm(
        Request $request,
        PasswordOtpService $otpService
    ): View|RedirectResponse {
        $user = $request->user();

        if (
            !$otpService->hasPendingOtp(
                (int) $user->id,
                $request->session()->getId()
            )
        ) {
            return redirect()
                ->route('account.password.request')
                ->withErrors([
                    'otp' =>
                        'Mã xác thực chưa được gửi hoặc đã hết hạn.',
                ]);
        }

        return view(
            'client.account.password.verify',
            [
                'maskedEmail' =>
                    $request->session()->get(
                        'password_otp_email',
                        $this->maskEmail($user->email)
                    ),
            ]
        );
    }

    public function verify(
        Request $request,
        PasswordOtpService $otpService
    ): RedirectResponse {
        $validated = $request->validate([
            'otp' => [
                'required',
                'digits:6',
            ],
        ], [
            'otp.required' =>
                'Vui lòng nhập mã xác thực.',

            'otp.digits' =>
                'Mã xác thực phải gồm đúng 6 chữ số.',
        ]);

        $user = $request->user();

        $verified = $otpService->verify(
            (int) $user->id,
            $request->session()->getId(),
            $validated['otp']
        );

        if (!$verified) {
            return back()
                ->withInput()
                ->withErrors([
                    'otp' =>
                        'Mã xác thực không đúng, đã hết hạn hoặc đã vượt quá số lần cho phép.',
                ]);
        }

        return redirect()
            ->route('account.password.change')
            ->with(
                'otp_success',
                'Xác thực email thành công.'
            );
    }

    public function changeForm(
        Request $request,
        PasswordOtpService $otpService
    ): View|RedirectResponse {
        $user = $request->user();

        if (
            !$otpService->isVerified(
                (int) $user->id,
                $request->session()->getId()
            )
        ) {
            return redirect()
                ->route('account.password.request')
                ->withErrors([
                    'otp' =>
                        'Bạn cần xác thực email trước khi đổi mật khẩu.',
                ]);
        }

        return view(
            'client.account.password.change'
        );
    }

    public function update(
        Request $request,
        PasswordOtpService $otpService
    ): RedirectResponse {
        $user = $request->user();
        $sessionId = $request->session()->getId();

        if (
            !$otpService->isVerified(
                (int) $user->id,
                $sessionId
            )
        ) {
            return redirect()
                ->route('account.password.request')
                ->withErrors([
                    'otp' =>
                        'Phiên xác thực đã hết hạn. Vui lòng thực hiện lại.',
                ]);
        }

        $validated = $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
                'different:current_password',
            ],
        ], [
            'password.required' =>
                'Vui lòng nhập mật khẩu mới.',

            'password.confirmed' =>
                'Xác nhận mật khẩu mới không khớp.',

            'password.different' =>
                'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ]);

        /*
         * Kiểm tra thêm để chắc chắn không dùng lại mật khẩu cũ.
         * Rule different chỉ so sánh hai input nếu cùng gửi lên.
         */
        if (
            Hash::check(
                $validated['password'],
                $user->password
            )
        ) {
            return back()->withErrors([
                'password' =>
                    'Mật khẩu mới phải khác mật khẩu hiện tại.',
            ]);
        }

        $user->password = Hash::make(
            $validated['password']
        );

        $user->save();

        $otpService->clear(
            (int) $user->id,
            $sessionId
        );

        $request->session()->forget(
            'password_otp_email'
        );

        $request->session()->regenerate();

        return redirect()
            ->route('account.index')
            ->with(
                'account_success',
                'Mật khẩu đã được thay đổi thành công.'
            );
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(
            explode('@', $email, 2),
            2,
            ''
        );

        if ($domain === '') {
            return $email;
        }

        $visibleLength = min(
            2,
            mb_strlen($local)
        );

        $visible = mb_substr(
            $local,
            0,
            $visibleLength
        );

        $hiddenLength = max(
            3,
            mb_strlen($local) - $visibleLength
        );

        return $visible
            . str_repeat('*', $hiddenLength)
            . '@'
            . $domain;
    }
}
