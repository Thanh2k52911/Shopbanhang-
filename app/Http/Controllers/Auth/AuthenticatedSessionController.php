<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\CartMergeService;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(
    LoginRequest $request,
    CartMergeService $cartMergeService
): RedirectResponse {
    /*
     * Phải lấy session cũ trước khi regenerate,
     * vì giỏ khách đang lưu theo session_id này.
     */
    $guestSessionId = $request->session()->getId();

    $request->authenticate();

    $cartMergeService->mergeGuestCartIntoUser(
        $guestSessionId,
        (int) $request->user()->id
    );

    $request->session()->regenerate();

    return redirect()->intended(
        route('home', absolute: false)
    );
}

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
