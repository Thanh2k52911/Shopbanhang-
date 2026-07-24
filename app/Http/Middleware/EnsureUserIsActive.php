<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Không cho tài khoản inactive hoặc blocked tiếp tục sử dụng hệ thống.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->status === 'active') {
            return $next($request);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = match ($user->status) {
            'blocked' =>
                'Tài khoản của bạn đã bị khóa.'
                . (
                    $user->blocked_reason
                        ? ' Lý do: '
                            . $user->blocked_reason
                        : ''
                ),

            'inactive' =>
                'Tài khoản của bạn đang tạm ngừng hoạt động.',

            default =>
                'Tài khoản không được phép truy cập.',
        };

        return redirect()
            ->route('login')
            ->with('error', $message);
    }
}
