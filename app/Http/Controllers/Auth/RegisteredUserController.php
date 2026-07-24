<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartMergeService;
use App\Services\EmailVerificationOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * @throws ValidationException
     */
    public function store(
        Request $request,
        CartMergeService $cartMergeService,
        EmailVerificationOtpService $otpService
    ): RedirectResponse {
        $guestSessionId = $request->session()->getId();

        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'string',
                    'lowercase',
                    'email:rfc',
                    'max:255',
                    'regex:/^[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}$/i',
                    'unique:' . User::class,
                ],

                'password' => [
                    'required',
                    'confirmed',
                    Rules\Password::defaults(),
                ],
            ],
            [
                'name.required' =>
                    'Vui lòng nhập họ và tên.',

                'email.required' =>
                    'Vui lòng nhập địa chỉ email.',

                'email.email' =>
                    'Địa chỉ email không hợp lệ.',

                'email.regex' =>
                    'Email phải có tên miền đầy đủ, ví dụ example@gmail.com.',

                'email.unique' =>
                    'Email này đã được sử dụng.',

                'password.required' =>
                    'Vui lòng nhập mật khẩu.',

                'password.confirmed' =>
                    'Xác nhận mật khẩu không khớp.',
            ]
        );

        $user = DB::transaction(
            function () use ($validated): User {
                $customerRoleId = DB::table('roles')
                    ->where('name', 'customer')
                    ->value('id');

                if (! $customerRoleId) {
                    throw new RuntimeException(
                        'Không tìm thấy role customer trong bảng roles.'
                    );
                }

                $user = User::create([
                    'name' => trim($validated['name']),
                    'email' => strtolower(
                        trim($validated['email'])
                    ),
                    'password' => Hash::make(
                        $validated['password']
                    ),
                    'status' => 'active',
                ]);

                DB::table('user_roles')->insert([
                    'user_id' => $user->id,
                    'role_id' => $customerRoleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return $user;
            }
        );

        Auth::login($user);

        $cartMergeService->mergeGuestCartIntoUser(
            $guestSessionId,
            (int) $user->id
        );

        $request->session()->regenerate();

        $otpService->send($user);

        $request->session()->put(
            'email_verification_otp_sent',
            true
        );

        return redirect()
            ->route('verification.notice')
            ->with(
                'success',
                'Mã xác minh đã được gửi tới email của bạn.'
            );
    }
}
