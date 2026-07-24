<?php

namespace App\Listeners;

use App\Services\LoginHistoryService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class AuthHistorySubscriber
{
    public function __construct(
        private readonly LoginHistoryService
            $loginHistoryService
    ) {
    }

    public function handleLogin(
        Login $event
    ): void {
        $this->loginHistoryService
            ->recordSuccessful(
                $event->user,
                request()
            );
    }

    public function handleFailed(
        Failed $event
    ): void {
        $this->loginHistoryService
            ->recordFailed(
                (string) (
                    $event
                        ->credentials['email']
                    ?? ''
                ),

                request(),

                'Email hoặc mật khẩu không chính xác.'
            );
    }

    public function handleLockout(
        Lockout $event
    ): void {
        $this->loginHistoryService
            ->recordFailed(
                (string) $event
                    ->request
                    ->input(
                        'email',
                        ''
                    ),

                $event->request,

                'Tạm khóa đăng nhập do thử sai quá nhiều lần.'
            );
    }

    public function handleLogout(
        Logout $event
    ): void {
        $this->loginHistoryService
            ->recordLogout(
                $event->user,
                request()
            );
    }

    public function subscribe(
        Dispatcher $events
    ): void {
        $events->listen(
            Login::class,
            [
                self::class,
                'handleLogin',
            ]
        );

        $events->listen(
            Failed::class,
            [
                self::class,
                'handleFailed',
            ]
        );

        $events->listen(
            Lockout::class,
            [
                self::class,
                'handleLockout',
            ]
        );

        $events->listen(
            Logout::class,
            [
                self::class,
                'handleLogout',
            ]
        );
    }
}
