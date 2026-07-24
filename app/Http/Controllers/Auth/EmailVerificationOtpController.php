<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EmailVerificationOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class EmailVerificationOtpController extends Controller
{
    public function show(
        Request $request,
        EmailVerificationOtpService $otpService
    ): View|RedirectResponse {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('products.index')
                ->with('success', 'Email của bạn đã được xác minh.');
        }

        if (! $request->session()->has('email_verification_otp_sent')) {
            try {
                $otpService->send($user);

                $request->session()->put(
                    'email_verification_otp_sent',
                    true
                );

                $request->session()->flash(
                    'success',
                    'Mã xác minh đã được gửi tới email của bạn.'
                );
            } catch (ValidationException) {
                //
            } catch (Throwable $exception) {
                report($exception);

                $request->session()->flash(
                    'error',
                    app()->isLocal()
                        ? 'Không thể gửi mã: ' . $exception->getMessage()
                        : 'Không thể gửi mã xác minh. Vui lòng thử lại.'
                );
            }
        }

        return view('auth.verify-email-otp', [
            'email' => $user->email,
        ]);
    }

    public function send(
        Request $request,
        EmailVerificationOtpService $otpService
    ): RedirectResponse {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('products.index')
                ->with('success', 'Email của bạn đã được xác minh.');
        }

        try {
            $otpService->send($user);

            return back()->with(
                'success',
                'Mã xác minh mới đã được gửi tới email của bạn.'
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Không thể gửi mã: ' . $exception->getMessage()
                    : 'Không thể gửi mã xác minh. Vui lòng thử lại.'
            );
        }
    }

    public function verify(
        Request $request,
        EmailVerificationOtpService $otpService
    ): RedirectResponse {
        $validated = $request->validate(
            [
                'otp' => [
                    'required',
                    'digits:6',
                ],
            ],
            [
                'otp.required' => 'Vui lòng nhập mã xác minh.',
                'otp.digits' => 'Mã xác minh phải gồm đúng 6 chữ số.',
            ]
        );

        $otpService->verify(
            $request->user(),
            $validated['otp']
        );

        $request->session()->forget(
            'email_verification_otp_sent'
        );

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Xác minh email thành công.'
            );
    }
}
