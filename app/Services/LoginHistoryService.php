<?php

namespace App\Services;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoginHistoryService
{
    public const SESSION_KEY = 'login_history_id';

    public function recordSuccessful(
        Authenticatable $user,
        Request $request
    ): LoginHistory {
        $client = $this->parseUserAgent(
            (string) $request->userAgent()
        );

        $history = LoginHistory::query()->create([
            'user_id' => (int) $user->getAuthIdentifier(),

            'email' => (string) (
                $user->email ?? ''
            ),

            'session_id' =>
                $request->session()->getId(),

            'ip_address' => $request->ip(),

            'user_agent' =>
                $request->userAgent(),

            'device' => $client['device'],

            'browser' => $client['browser'],

            'platform' => $client['platform'],

            'country' => null,

            'city' => null,

            'is_success' => true,

            'failure_reason' => null,

            'logged_in_at' => now(),

            'logged_out_at' => null,
        ]);

        $request->session()->put(
            self::SESSION_KEY,
            $history->id
        );

        if ($user instanceof User) {
            $user->forceFill([
                'last_login_at' => now(),

                'last_login_ip' =>
                    $request->ip(),
            ])->saveQuietly();
        }

        return $history;
    }

    public function recordFailed(
        string $email,
        Request $request,
        string $reason
    ): LoginHistory {
        $normalizedEmail = Str::lower(
            trim($email)
        );

        $user = User::query()
            ->where(
                'email',
                $normalizedEmail
            )
            ->first();

        $client = $this->parseUserAgent(
            (string) $request->userAgent()
        );

        return LoginHistory::query()->create([
            'user_id' => $user?->id,

            'email' =>
                $normalizedEmail !== ''
                    ? $normalizedEmail
                    : null,

            'session_id' =>
                $request->session()->getId(),

            'ip_address' => $request->ip(),

            'user_agent' =>
                $request->userAgent(),

            'device' => $client['device'],

            'browser' => $client['browser'],

            'platform' => $client['platform'],

            'country' => null,

            'city' => null,

            'is_success' => false,

            'failure_reason' => Str::limit(
                $reason,
                255,
                ''
            ),

            'logged_in_at' => now(),

            'logged_out_at' => null,
        ]);
    }

    public function recordLogout(
        ?Authenticatable $user,
        Request $request
    ): void {
        if (! $user) {
            return;
        }

        $historyId = $request
            ->session()
            ->get(self::SESSION_KEY);

        $query = LoginHistory::query()
            ->where(
                'user_id',
                $user->getAuthIdentifier()
            )
            ->where(
                'is_success',
                true
            )
            ->whereNull('logged_out_at');

        if ($historyId) {
            $query->whereKey($historyId);
        } else {
            $query
                ->where(
                    'session_id',
                    $request
                        ->session()
                        ->getId()
                )
                ->latestLogin();
        }

        $history = $query->first();

        if ($history) {
            $history->forceFill([
                'logged_out_at' => now(),
            ])->save();
        }

        $request->session()->forget(
            self::SESSION_KEY
        );
    }

    /**
     * @return array{
     *     device: string,
     *     browser: string,
     *     platform: string
     * }
     */
    private function parseUserAgent(
        string $userAgent
    ): array {
        $agent = Str::lower($userAgent);

        $device = match (true) {
            Str::contains(
                $agent,
                [
                    'iphone',
                    'ipod',
                ]
            ) => 'iPhone',

            Str::contains(
                $agent,
                ['ipad']
            ) => 'iPad',

            Str::contains(
                $agent,
                ['android']
            )
            && Str::contains(
                $agent,
                ['mobile']
            ) => 'Android Phone',

            Str::contains(
                $agent,
                ['android']
            ) => 'Android Tablet',

            Str::contains(
                $agent,
                ['mobile']
            ) => 'Mobile',

            Str::contains(
                $agent,
                ['tablet']
            ) => 'Tablet',

            default => 'Desktop',
        };

        $browser = match (true) {
            Str::contains(
                $agent,
                ['edg/']
            ) => 'Microsoft Edge',

            Str::contains(
                $agent,
                [
                    'opr/',
                    'opera',
                ]
            ) => 'Opera',

            Str::contains(
                $agent,
                ['firefox/']
            ) => 'Firefox',

            Str::contains(
                $agent,
                ['chrome/']
            ) => 'Chrome',

            Str::contains(
                $agent,
                ['safari/']
            )
            && ! Str::contains(
                $agent,
                [
                    'chrome/',
                    'chromium/',
                ]
            ) => 'Safari',

            default => 'Không xác định',
        };

        $platform = match (true) {
            Str::contains(
                $agent,
                ['windows nt 10.0']
            ) => 'Windows 10/11',

            Str::contains(
                $agent,
                ['windows']
            ) => 'Windows',

            Str::contains(
                $agent,
                ['android']
            ) => 'Android',

            Str::contains(
                $agent,
                [
                    'iphone',
                    'ipad',
                    'ipod',
                ]
            ) => 'iOS',

            Str::contains(
                $agent,
                [
                    'macintosh',
                    'mac os x',
                ]
            ) => 'macOS',

            Str::contains(
                $agent,
                ['linux']
            ) => 'Linux',

            default => 'Không xác định',
        };

        return compact(
            'device',
            'browser',
            'platform'
        );
    }
}
