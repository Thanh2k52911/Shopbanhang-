<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoginHistorySeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')
            ->orderBy('id')
            ->limit(5)
            ->get()
            ->values();

        if ($users->isEmpty()) {
            $this->command?->warn(
                'Không có user để tạo login histories.'
            );

            return;
        }

        $histories = [
            [
                'user_index' => 0,
                'email' => $users->get(0)?->email,
                'session_id' => 'session-demo-0001',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 Chrome Windows',
                'device' => 'Desktop',
                'browser' => 'Chrome',
                'platform' => 'Windows 10',
                'country' => 'Việt Nam',
                'city' => 'Hà Nội',
                'is_success' => true,
                'failure_reason' => null,
                'logged_in_at' => now()->subHours(2),
                'logged_out_at' => null,
            ],
            [
                'user_index' => 1,
                'email' => $users->get(1)?->email,
                'session_id' => 'session-demo-0002',
                'ip_address' => '192.168.1.20',
                'user_agent' => 'Mozilla/5.0 Safari iPhone',
                'device' => 'iPhone',
                'browser' => 'Safari',
                'platform' => 'iOS',
                'country' => 'Việt Nam',
                'city' => 'Hà Nội',
                'is_success' => true,
                'failure_reason' => null,
                'logged_in_at' => now()->subHours(6),
                'logged_out_at' => now()->subHours(4),
            ],
            [
                'user_index' => 2,
                'email' => $users->get(2)?->email,
                'session_id' => 'session-demo-0003',
                'ip_address' => '192.168.1.21',
                'user_agent' => 'Mozilla/5.0 Chrome Android',
                'device' => 'Android Phone',
                'browser' => 'Chrome',
                'platform' => 'Android',
                'country' => 'Việt Nam',
                'city' => 'Hải Phòng',
                'is_success' => true,
                'failure_reason' => null,
                'logged_in_at' => now()->subDay(),
                'logged_out_at' => now()->subHours(20),
            ],
            [
                'user_index' => null,
                'email' => 'wrong-email@example.com',
                'session_id' => null,
                'ip_address' => '10.0.0.15',
                'user_agent' => 'Mozilla/5.0 Firefox Windows',
                'device' => 'Desktop',
                'browser' => 'Firefox',
                'platform' => 'Windows 11',
                'country' => 'Việt Nam',
                'city' => 'Hà Nội',
                'is_success' => false,
                'failure_reason' => 'Email hoặc mật khẩu không chính xác.',
                'logged_in_at' => now()->subMinutes(45),
                'logged_out_at' => null,
            ],
            [
                'user_index' => 3,
                'email' => $users->get(3)?->email,
                'session_id' => null,
                'ip_address' => '10.0.0.20',
                'user_agent' => 'Mozilla/5.0 Chrome Windows',
                'device' => 'Laptop',
                'browser' => 'Chrome',
                'platform' => 'Windows 11',
                'country' => 'Việt Nam',
                'city' => 'Thanh Hóa',
                'is_success' => false,
                'failure_reason' => 'Mật khẩu không chính xác.',
                'logged_in_at' => now()->subMinutes(20),
                'logged_out_at' => null,
            ],
        ];

        foreach ($histories as $history) {
            $userId = $history['user_index'] !== null
                ? $users->get($history['user_index'])?->id
                : null;

            $exists = DB::table('login_histories')
                ->where('email', $history['email'])
                ->where('ip_address', $history['ip_address'])
                ->where('logged_in_at', $history['logged_in_at'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('login_histories')->insert([
                'user_id' => $userId,
                'email' => $history['email'],
                'session_id' => $history['session_id'],
                'ip_address' => $history['ip_address'],
                'user_agent' => $history['user_agent'],
                'device' => $history['device'],
                'browser' => $history['browser'],
                'platform' => $history['platform'],
                'country' => $history['country'],
                'city' => $history['city'],
                'is_success' => $history['is_success'],
                'failure_reason' => $history['failure_reason'],
                'logged_in_at' => $history['logged_in_at'],
                'logged_out_at' => $history['logged_out_at'],
                'created_at' => $history['logged_in_at'],
                'updated_at' => now(),
            ]);
        }
    }
}
