<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'email' => [
                    'required',
                    'email',
                    'max:255',
                ],
            ],
            [
                'email.required' => 'Vui lòng nhập địa chỉ email.',
                'email.email' => 'Địa chỉ email không hợp lệ.',
                'email.max' => 'Email không được vượt quá 255 ký tự.',
            ]
        );

        $subscriber = DB::table('newsletter_subscribers')
            ->where('email', $validated['email'])
            ->first();

        if ($subscriber) {
            if ((bool) $subscriber->status) {
                return back()->with(
                    'newsletter_info',
                    'Email này đã đăng ký nhận ưu đãi.'
                );
            }

            DB::table('newsletter_subscribers')
                ->where('id', $subscriber->id)
                ->update([
                    'status' => true,
                    'source' => 'home',
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                    'updated_at' => now(),
                ]);

            return back()->with(
                'newsletter_success',
                'Bạn đã đăng ký nhận ưu đãi trở lại thành công.'
            );
        }

        DB::table('newsletter_subscribers')->insert([
            'email' => $validated['email'],
            'user_id' => auth()->id(),
            'name' => auth()->user()?->name,
            'status' => true,
            'source' => 'home',
            'verification_token' => Str::random(64),
            'unsubscribe_token' => Str::random(64),
            'verified_at' => null,
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with(
            'newsletter_success',
            'Đăng ký thành công! Bạn sẽ nhận được các ưu đãi mới nhất.'
        );
    }
}
