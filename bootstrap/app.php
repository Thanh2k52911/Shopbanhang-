<?php

use App\Http\Middleware\AuditAdminRequest;
use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RecordProductActivity;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web:
            __DIR__
            . '/../routes/web.php',

        commands:
            __DIR__
            . '/../routes/console.php',

        health: '/up',
    )
    ->withMiddleware(
        function (
            Middleware $middleware
        ): void {
            $middleware->alias([
                'admin.access' =>
                    EnsureAdminAccess::class,

                'admin.audit' =>
                    AuditAdminRequest::class,

                'user.active' =>
                    EnsureUserIsActive::class,
            ]);

            $middleware->web(
                append: [
                    SecurityHeaders::class,

                    RecordProductActivity::class,
                ]
            );
        }
    )
    ->withExceptions(
        function (
            Exceptions $exceptions
        ): void {
            //
        }
    )
    ->create();
