<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditLogService
{
    /**
     * Các trường tuyệt đối không được ghi vào nhật ký.
     *
     * @var array<int, string>
     */
    private const SENSITIVE_FIELDS = [
        '_token',
        '_method',
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'new_password_confirmation',
        'otp',
        'token',
        'api_token',
        'access_token',
        'refresh_token',
        'secret',
        'client_secret',
        'card_number',
        'cvv',
    ];

    /**
     * Ghi một thao tác Admin từ request hiện tại.
     */
    public function logAdminRequest(
        Request $request,
        int $responseStatus
    ): ?AuditLog {
        try {
            $route = $request->route();
            $routeName = $route?->getName();
            $action = $this->resolveAction(
                $request->method(),
                $routeName
            );

            [$auditableType, $auditableId] =
                $this->resolveAuditable($request);

            $payload = $this->sanitizePayload(
                $request->except(self::SENSITIVE_FIELDS)
            );

            return AuditLog::query()->create([
                'user_id' => $request->user()?->getKey(),
                'action' => $action,
                'auditable_type' => $auditableType,
                'auditable_id' => $auditableId,
                'description' => $this->description(
                    $action,
                    $routeName,
                    $responseStatus
                ),
                'old_values' => null,
                'new_values' => $payload !== []
                    ? $payload
                    : null,
                'route_name' => $routeName,
                'url' => mb_substr(
                    $request->fullUrl(),
                    0,
                    1000
                ),
                'request_method' => strtoupper(
                    $request->method()
                ),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Không thể ghi audit log.', [
                'message' => $exception->getMessage(),
                'route' => $request->route()?->getName(),
                'user_id' => $request->user()?->getKey(),
            ]);

            return null;
        }
    }

    private function resolveAction(
        string $method,
        ?string $routeName
    ): string {
        $routeName = strtolower((string) $routeName);

        if (str_contains($routeName, 'approve')) {
            return 'approved';
        }

        if (str_contains($routeName, 'reject')) {
            return 'rejected';
        }

        if (str_contains($routeName, 'cancel')) {
            return 'cancelled';
        }

        if (str_contains($routeName, 'restore')) {
            return 'restored';
        }

        if (str_contains($routeName, 'toggle')) {
            return 'updated';
        }

        return match (strtoupper($method)) {
            'POST' => 'created',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted',
            default => 'executed',
        };
    }

    /**
     * @return array{0: string|null, 1: int|null}
     */
    private function resolveAuditable(Request $request): array
    {
        $parameters = $request->route()?->parameters() ?? [];

        foreach (array_reverse($parameters, true) as $value) {
            if (is_object($value) && method_exists($value, 'getKey')) {
                return [
                    $value::class,
                    is_numeric($value->getKey())
                        ? (int) $value->getKey()
                        : null,
                ];
            }
        }

        foreach (array_reverse($parameters, true) as $name => $value) {
            if (is_numeric($value)) {
                return [
                    $this->guessModelClass((string) $name),
                    (int) $value,
                ];
            }
        }

        return [null, null];
    }

    private function guessModelClass(string $routeParameter): ?string
    {
        $class = 'App\\Models\\' . str($routeParameter)
            ->singular()
            ->studly();

        return class_exists($class)
            ? $class
            : null;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function sanitizePayload(array $payload): array
    {
        $payload = Arr::except(
            $payload,
            self::SENSITIVE_FIELDS
        );

        return collect($payload)
            ->map(function (mixed $value): mixed {
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    return [
                        'filename' => $value->getClientOriginalName(),
                        'size' => $value->getSize(),
                        'mime_type' => $value->getClientMimeType(),
                    ];
                }

                if (is_string($value)) {
                    return mb_substr($value, 0, 2000);
                }

                if (is_array($value)) {
                    return $this->sanitizePayload($value);
                }

                return $value;
            })
            ->all();
    }

    private function description(
        string $action,
        ?string $routeName,
        int $responseStatus
    ): string {
        $label = match ($action) {
            'created' => 'Tạo dữ liệu',
            'updated' => 'Cập nhật dữ liệu',
            'deleted' => 'Xóa dữ liệu',
            'restored' => 'Khôi phục dữ liệu',
            'approved' => 'Phê duyệt dữ liệu',
            'rejected' => 'Từ chối dữ liệu',
            'cancelled' => 'Hủy dữ liệu',
            default => 'Thực hiện thao tác',
        };

        return sprintf(
            '%s qua route %s, HTTP %d.',
            $label,
            $routeName ?: 'không xác định',
            $responseStatus
        );
    }
}
