<?php

namespace App\Http\Middleware;

use App\Services\Admin\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditAdminRequest
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $response = $next($request);

        if ($this->shouldAudit($request, $response)) {
            $this->auditLogService->logAdminRequest(
                $request,
                $response->getStatusCode()
            );
        }

        return $response;
    }

    private function shouldAudit(
        Request $request,
        Response $response
    ): bool {
        if (! $request->user()) {
            return false;
        }

        if (in_array(
            strtoupper($request->method()),
            ['GET', 'HEAD', 'OPTIONS'],
            true
        )) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        $routeName = (string) $request->route()?->getName();

        if ($routeName === '') {
            return false;
        }

        return ! str_starts_with(
            $routeName,
            'admin.audit-logs.'
        );
    }
}
