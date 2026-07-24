<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Đánh dấu địa chỉ email của người dùng đã được xác minh.
     */
    public function __invoke(
        EmailVerificationRequest $request
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Email đã được xác minh trước đó
        |--------------------------------------------------------------------------
        */

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()
                ->route('home')
                ->with(
                    'success',
                    'Địa chỉ email của bạn đã được xác minh trước đó.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Xác minh email
        |--------------------------------------------------------------------------
        */

        if ($request->user()->markEmailAsVerified()) {
            event(
                new Verified(
                    $request->user()
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Chuyển về trang chủ
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('home')
            ->with(
                'success',
                'Xác minh địa chỉ email thành công.'
            );
    }
}
