<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClientNotificationService
{
    /** @param array<string,mixed> $data */
    public function send(
        ?User $user,
        string $type,
        string $title,
        ?string $message = null,
        string $category = 'system',
        ?string $actionUrl = null,
        string $priority = 'normal',
        array $data = [],
        bool $unique = true
    ): ?Notification {
        if (! $user) {
            return null;
        }

        if ($unique) {
            $query = Notification::query()
                ->where('notifiable_type', $user->getMorphClass())
                ->where('notifiable_id', $user->getKey())
                ->where('type', $type);

            foreach (['order_id', 'return_request_id', 'refund_id', 'loyalty_account_id', 'saved_coupon_id'] as $key) {
                if (array_key_exists($key, $data) && $data[$key] !== null) {
                    $query->where("data->{$key}", $data[$key]);
                }
            }

            if ($query->exists()) {
                return null;
            }
        }

        return Notification::query()->create([
            'type' => $type,
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->getKey(),
            'title' => $title,
            'message' => $message,
            'category' => $category,
            'action_url' => $actionUrl,
            'image' => null,
            'priority' => $priority,
            'data' => $data,
            'read_at' => null,
        ]);
    }

    public function safely(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            report($exception);
            Log::warning('Không thể tạo thông báo khách hàng.', [
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);
            return null;
        }
    }
}
