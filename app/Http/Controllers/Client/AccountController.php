<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $userId = (int) auth()->id();

        $orderCounts = DB::table('orders')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->select(
                'order_status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        $totalOrders = (int) $orderCounts->sum();

        $pendingOrders = (int) $orderCounts->get(
            'pending',
            0
        );

        $shippingOrders = (int) $orderCounts->get(
            'shipping',
            0
        );

        $completedOrders = (int) $orderCounts->get(
            'completed',
            0
        );

        $cancelledOrders = (int) $orderCounts->get(
            'cancelled',
            0
        );

        $totalSpent = (float) DB::table('orders')
            ->where('user_id', $userId)
            ->where('order_status', 'completed')
            ->whereNull('deleted_at')
            ->sum('total_amount');

        $recentOrders = DB::table('orders')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get([
                'order_code',
                'order_status',
                'payment_status',
                'total_amount',
                'total_quantity',
                'created_at',
            ]);

        return view('client.account.index', compact(
            'totalOrders',
            'pendingOrders',
            'shippingOrders',
            'completedOrders',
            'cancelledOrders',
            'totalSpent',
            'recentOrders'
        ));
    }

    public function edit(): View
    {
        return view('client.account.profile', [
            'user' => auth()->user(),
        ]);
    }

    public function update(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        $validated = $request->validate([
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
                Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],

            'avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'name.required' =>
                'Vui lòng nhập họ và tên.',

            'name.max' =>
                'Họ và tên không được vượt quá 255 ký tự.',

            'email.required' =>
                'Vui lòng nhập địa chỉ email.',

            'email.email' =>
                'Địa chỉ email không hợp lệ.',

            'email.regex' =>
                'Email phải có tên miền đầy đủ, ví dụ example@gmail.com.',

            'email.unique' =>
                'Email này đã được sử dụng.',

            'avatar.image' =>
                'Ảnh đại diện phải là tệp hình ảnh.',

            'avatar.mimes' =>
                'Ảnh đại diện chỉ hỗ trợ JPG, JPEG, PNG hoặc WEBP.',

            'avatar.max' =>
                'Ảnh đại diện không được vượt quá 2MB.',
        ]);

        $emailChanged =
            strtolower($user->email)
            !== strtolower($validated['email']);

        $user->name = trim($validated['name']);

        $user->email = strtolower(
            trim($validated['email'])
        );

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete(
                    $user->avatar
                );
            }

            $user->avatar = $request
                ->file('avatar')
                ->store('avatars', 'public');
        }

        $user->save();

        return redirect()
            ->route('account.profile.edit')
            ->with(
                'profile_success',
                'Thông tin tài khoản đã được cập nhật.'
            );
    }
}
