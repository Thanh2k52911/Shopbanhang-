<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsletterSubscriberSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')
            ->orderBy('id')
            ->limit(3)
            ->get()
            ->values();

        $subscribers = [
            [
                'email' => $users->get(0)?->email ?? 'member1@example.com',
                'user_id' => $users->get(0)?->id,
                'name' => $users->get(0)?->name ?? 'Nguyễn Văn An',
                'status' => true,
                'source' => 'footer',
                'verified_at' => now()->subDays(20),
                'unsubscribed_at' => null,
            ],
            [
                'email' => $users->get(1)?->email ?? 'member2@example.com',
                'user_id' => $users->get(1)?->id,
                'name' => $users->get(1)?->name ?? 'Trần Thị Bình',
                'status' => true,
                'source' => 'checkout',
                'verified_at' => now()->subDays(15),
                'unsubscribed_at' => null,
            ],
            [
                'email' => $users->get(2)?->email ?? 'member3@example.com',
                'user_id' => $users->get(2)?->id,
                'name' => $users->get(2)?->name ?? 'Lê Minh Châu',
                'status' => true,
                'source' => 'popup',
                'verified_at' => now()->subDays(10),
                'unsubscribed_at' => null,
            ],
            [
                'email' => 'thaonguyen@example.com',
                'user_id' => null,
                'name' => 'Nguyễn Thảo',
                'status' => true,
                'source' => 'footer',
                'verified_at' => now()->subDays(5),
                'unsubscribed_at' => null,
            ],
            [
                'email' => 'minhquan@example.com',
                'user_id' => null,
                'name' => 'Minh Quân',
                'status' => false,
                'source' => 'popup',
                'verified_at' => now()->subDays(30),
                'unsubscribed_at' => now()->subDays(2),
            ],
        ];

        foreach ($subscribers as $index => $subscriber) {
            $existing = DB::table('newsletter_subscribers')
                ->where('email', $subscriber['email'])
                ->first();

            DB::table('newsletter_subscribers')->updateOrInsert(
                [
                    'email' => $subscriber['email'],
                ],
                [
                    'user_id' => $subscriber['user_id'],
                    'name' => $subscriber['name'],
                    'status' => $subscriber['status'],
                    'source' => $subscriber['source'],

                    /*
                     * Giữ token cũ nếu bản ghi đã tồn tại,
                     * tránh thay đổi token mỗi lần chạy seeder.
                     */
                    'verification_token' =>
                        $existing?->verification_token
                        ?? Str::random(60),

                    'unsubscribe_token' =>
                        $existing?->unsubscribe_token
                        ?? Str::random(60),

                    'verified_at' => $subscriber['verified_at'],
                    'subscribed_at' => now()->subDays(25 - ($index * 4)),
                    'unsubscribed_at' => $subscriber['unsubscribed_at'],
                    'created_at' => now()->subDays(25 - ($index * 4)),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
