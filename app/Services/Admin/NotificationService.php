<?php

namespace App\Services\Admin;

use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    public const DEFAULT_ADMIN_ROLES = [
        'super_admin',
        'admin',
        'staff',
        'customer_support',
    ];

    public const INVENTORY_ADMIN_ROLES = [
        'super_admin',
        'admin',
        'staff',
        'warehouse_staff',
    ];

    /**
     * Tạo một thông báo cho một model nhận thông báo.
     *
     * @param array<string, mixed> $data
     */
    public function create(
        Model $notifiable,
        string $title,
        ?string $message = null,
        string $category = 'system',
        ?string $actionUrl = null,
        ?string $image = null,
        string $priority = 'normal',
        array $data = [],
        ?string $type = null
    ): Notification {
        $this->validatePriority($priority);

        return Notification::query()->create([
            'type' => $type
                ?: 'admin.database',
            'notifiable_type' => $notifiable->getMorphClass(),
            'notifiable_id' => $notifiable->getKey(),
            'title' => $title,
            'message' => $message,
            'category' => $category,
            'action_url' => $actionUrl,
            'image' => $image,
            'priority' => $priority,
            'data' => $data,
            'read_at' => null,
        ]);
    }

    /**
     * Gửi cùng một thông báo cho nhiều model nhận thông báo.
     *
     * @param iterable<int, Model> $notifiables
     * @param array<string, mixed> $data
     */
    public function sendToMany(
        iterable $notifiables,
        string $title,
        ?string $message = null,
        string $category = 'system',
        ?string $actionUrl = null,
        ?string $image = null,
        string $priority = 'normal',
        array $data = [],
        ?string $type = null
    ): Collection {
        $created = collect();

        DB::transaction(
            function () use (
                $notifiables,
                $title,
                $message,
                $category,
                $actionUrl,
                $image,
                $priority,
                $data,
                $type,
                $created
            ): void {
                foreach ($notifiables as $notifiable) {
                    if (! $notifiable instanceof Model) {
                        continue;
                    }

                    $created->push(
                        $this->create(
                            $notifiable,
                            $title,
                            $message,
                            $category,
                            $actionUrl,
                            $image,
                            $priority,
                            $data,
                            $type
                        )
                    );
                }
            }
        );

        return $created;
    }

    /**
     * Gửi thông báo tới các tài khoản quản trị theo role.
     *
     * @param array<int, string> $roleNames
     * @param array<string, mixed> $data
     */
    public function sendToAdminRoles(
        array $roleNames,
        string $title,
        ?string $message = null,
        string $category = 'system',
        ?string $actionUrl = null,
        ?string $image = null,
        string $priority = 'normal',
        array $data = [],
        ?string $type = null
    ): Collection {
        $admins = $this->adminUsers(
            $roleNames
        );

        return $this->sendToMany(
            $admins,
            $title,
            $message,
            $category,
            $actionUrl,
            $image,
            $priority,
            $data,
            $type
        );
    }

    /**
     * Gửi thông báo tới nhóm quản trị mặc định.
     *
     * @param array<string, mixed> $data
     */
    public function sendToAdmins(
        string $title,
        ?string $message = null,
        string $category = 'system',
        ?string $actionUrl = null,
        ?string $image = null,
        string $priority = 'normal',
        array $data = [],
        ?string $type = null
    ): Collection {
        return $this->sendToAdminRoles(
            self::DEFAULT_ADMIN_ROLES,
            $title,
            $message,
            $category,
            $actionUrl,
            $image,
            $priority,
            $data,
            $type
        );
    }

    /**
     * Danh sách thông báo mới nhất của một người dùng.
     */
    public function latestFor(
        User $user,
        int $limit = 10
    ): EloquentCollection {
        return Notification::query()
            ->where(
                'notifiable_type',
                $user->getMorphClass()
            )
            ->where(
                'notifiable_id',
                $user->getKey()
            )
            ->latest('created_at')
            ->limit(max(1, min($limit, 100)))
            ->get();
    }

    /**
     * Phân trang toàn bộ thông báo của một người dùng.
     */
    public function paginateFor(
        User $user,
        int $perPage = 20,
        ?string $category = null,
        ?string $status = null
    ): LengthAwarePaginator {
        $query = Notification::query()
            ->where(
                'notifiable_type',
                $user->getMorphClass()
            )
            ->where(
                'notifiable_id',
                $user->getKey()
            )
            ->category($category);

        if ($status === 'unread') {
            $query->unread();
        } elseif ($status === 'read') {
            $query->read();
        }

        return $query
            ->latest('created_at')
            ->paginate(
                max(1, min($perPage, 100))
            )
            ->withQueryString();
    }

    /**
     * Số thông báo chưa đọc.
     */
    public function unreadCountFor(
        User $user
    ): int {
        return Notification::query()
            ->where(
                'notifiable_type',
                $user->getMorphClass()
            )
            ->where(
                'notifiable_id',
                $user->getKey()
            )
            ->unread()
            ->count();
    }

    /**
     * Đánh dấu một thông báo đã đọc.
     */
    public function markAsRead(
        User $user,
        string $notificationId
    ): Notification {
        $notification = $this->findForUserOrFail(
            $user,
            $notificationId
        );

        $notification->markAsRead();

        return $notification->fresh();
    }

    /**
     * Đánh dấu một thông báo chưa đọc.
     */
    public function markAsUnread(
        User $user,
        string $notificationId
    ): Notification {
        $notification = $this->findForUserOrFail(
            $user,
            $notificationId
        );

        $notification->markAsUnread();

        return $notification->fresh();
    }

    /**
     * Đánh dấu toàn bộ thông báo đã đọc.
     */
    public function markAllAsRead(
        User $user
    ): int {
        return Notification::query()
            ->where(
                'notifiable_type',
                $user->getMorphClass()
            )
            ->where(
                'notifiable_id',
                $user->getKey()
            )
            ->unread()
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * Xóa một thông báo thuộc về người dùng.
     */
    public function delete(
        User $user,
        string $notificationId
    ): bool {
        $notification = $this->findForUserOrFail(
            $user,
            $notificationId
        );

        return (bool) $notification->delete();
    }

    /**
     * Thông báo cho khách khi đơn hàng đã hoàn thành.
     *
     * Có kiểm tra idempotent để ShipmentStatusService và OrderStatusService
     * cùng gọi cũng không tạo thông báo trùng.
     */
    public function notifyOrderCompleted(
        Order $order
    ): ?Notification {
        $order->loadMissing('user');

        if (! $order->user) {
            return null;
        }

        $exists = Notification::query()
            ->where('notifiable_type', $order->user->getMorphClass())
            ->where('notifiable_id', $order->user->getKey())
            ->where('type', 'order.completed')
            ->where('data->order_id', $order->id)
            ->exists();

        if ($exists) {
            return null;
        }

        return $this->create(
            $order->user,
            'Đơn hàng đã hoàn thành',
            sprintf(
                'Đơn %s đã được giao thành công. Bạn đã được cộng điểm thành viên nếu đơn đủ điều kiện.',
                $order->order_code
            ),
            'order',
            route('account.orders.show', $order->order_code),
            null,
            'normal',
            [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'order_status' => $order->order_status,
                'shipping_status' => $order->shipping_status,
                'payment_status' => $order->payment_status,
            ],
            'order.completed'
        );
    }

    /**
     * Thông báo cho Admin khi khách hàng hủy đơn.
     */
    public function notifyCustomerCancelledOrder(Order $order): Collection
    {
        $order->loadMissing('user');

        return $this->sendToAdmins(
            'Khách hàng đã hủy đơn',
            sprintf(
                'Đơn %s của %s đã bị khách hàng hủy. Lý do: %s',
                $order->order_code,
                $order->user?->name ?? $order->customer_name ?? 'Khách hàng',
                $order->cancel_reason ?: 'Không cung cấp'
            ),
            'order',
            route('admin.orders.show', $order->id),
            null,
            'high',
            [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'cancel_reason' => $order->cancel_reason,
                'cancelled_by' => $order->cancelled_by,
            ],
            'order.cancelled_by_customer'
        );
    }

    /**
     * Tạo thông báo đơn hàng mới cho Admin.
     *
     * @param array<string, mixed> $extraData
     */
    public function notifyNewOrder(
        int $orderId,
        string $orderCode,
        string $customerName,
        float|int $totalAmount,
        array $extraData = []
    ): Collection {
        return $this->sendToAdmins(
            'Có đơn hàng mới',
            sprintf(
                'Đơn %s của %s có tổng giá trị %sđ.',
                $orderCode,
                $customerName,
                number_format(
                    (float) $totalAmount,
                    0,
                    ',',
                    '.'
                )
            ),
            'order',
            route(
                'admin.orders.show',
                $orderId
            ),
            null,
            'high',
            array_merge(
                [
                    'order_id' => $orderId,
                    'order_code' => $orderCode,
                    'customer_name' => $customerName,
                    'total_amount' => (float) $totalAmount,
                ],
                $extraData
            ),
            'order.created'
        );
    }

    /**
     * Tạo thông báo câu hỏi mới.
     *
     * @param array<string, mixed> $extraData
     */
    public function notifyNewQuestion(
        int $questionId,
        string $productName,
        string $questionText,
        array $extraData = []
    ): Collection {
        return $this->sendToAdminRoles(
            self::DEFAULT_ADMIN_ROLES,
            'Có câu hỏi sản phẩm mới',
            sprintf(
                '%s: %s',
                $productName,
                str($questionText)->limit(140)
            ),
            'question',
            route(
                'admin.questions.show',
                $questionId
            ),
            null,
            'normal',
            array_merge(
                [
                    'question_id' => $questionId,
                    'product_name' => $productName,
                ],
                $extraData
            ),
            'question.created'
        );
    }

    /**
     * Tạo thông báo đánh giá mới.
     *
     * @param array<string, mixed> $extraData
     */
    public function notifyNewReview(
        int $reviewId,
        string $productName,
        int $rating,
        array $extraData = []
    ): Collection {
        return $this->sendToAdminRoles(
            self::DEFAULT_ADMIN_ROLES,
            'Có đánh giá sản phẩm mới',
            sprintf(
                '%s nhận được đánh giá %d sao.',
                $productName,
                $rating
            ),
            'review',
            route(
                'admin.reviews.show',
                $reviewId
            ),
            null,
            $rating <= 2
                ? 'high'
                : 'normal',
            array_merge(
                [
                    'review_id' => $reviewId,
                    'product_name' => $productName,
                    'rating' => $rating,
                ],
                $extraData
            ),
            'review.created'
        );
    }

    /**
     * Tạo thông báo tồn kho thấp.
     *
     * @param array<string, mixed> $extraData
     */
    public function notifyLowStock(
        int $inventoryId,
        string $productName,
        string $skuCode,
        int $availableQuantity,
        int $minimumStock,
        array $extraData = []
    ): Collection {
        return $this->sendToAdminRoles(
            self::INVENTORY_ADMIN_ROLES,
            'Cảnh báo tồn kho thấp',
            sprintf(
                '%s (%s) còn %d sản phẩm, mức tối thiểu là %d.',
                $productName,
                $skuCode,
                $availableQuantity,
                $minimumStock
            ),
            'inventory',
            route(
                'admin.inventory.index'
            ),
            null,
            $availableQuantity <= 0
                ? 'urgent'
                : 'high',
            array_merge(
                [
                    'inventory_id' => $inventoryId,
                    'product_name' => $productName,
                    'sku_code' => $skuCode,
                    'available_quantity' => $availableQuantity,
                    'minimum_stock' => $minimumStock,
                ],
                $extraData
            ),
            'inventory.low_stock'
        );
    }

    /**
     * Tạo thông báo yêu cầu đổi trả mới.
     *
     * @param array<string, mixed> $extraData
     */
    public function notifyNewReturnRequest(
        int $returnRequestId,
        string $requestCode,
        string $customerName,
        array $extraData = []
    ): Collection {
        return $this->sendToAdmins(
            'Có yêu cầu đổi trả mới',
            sprintf(
                'Yêu cầu %s của %s đang chờ xử lý.',
                $requestCode,
                $customerName
            ),
            'return',
            route(
                'admin.return-requests.show',
                $returnRequestId
            ),
            null,
            'high',
            array_merge(
                [
                    'return_request_id' => $returnRequestId,
                    'request_code' => $requestCode,
                    'customer_name' => $customerName,
                ],
                $extraData
            ),
            'return_request.created'
        );
    }

    /**
     * Tạo thông báo liên hệ mới.
     *
     * @param array<string, mixed> $extraData
     */
    public function notifyNewContact(
        int $contactMessageId,
        string $contactCode,
        string $subject,
        array $extraData = []
    ): Collection {
        return $this->sendToAdminRoles(
            self::DEFAULT_ADMIN_ROLES,
            'Có liên hệ mới',
            sprintf(
                '%s: %s',
                $contactCode,
                str($subject)->limit(140)
            ),
            'contact',
            route(
                'admin.contact-messages.show',
                $contactMessageId
            ),
            null,
            'normal',
            array_merge(
                [
                    'contact_message_id' => $contactMessageId,
                    'contact_code' => $contactCode,
                    'subject' => $subject,
                ],
                $extraData
            ),
            'contact.created'
        );
    }

    /**
     * Lấy các tài khoản quản trị đang hoạt động.
     *
     * @param array<int, string> $roleNames
     */
    public function adminUsers(
        array $roleNames = self::DEFAULT_ADMIN_ROLES
    ): EloquentCollection {
        return User::query()
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->whereHas(
                'roles',
                function ($query) use ($roleNames): void {
                    $query->whereIn(
                        'roles.name',
                        $roleNames
                    );
                }
            )
            ->distinct()
            ->get();
    }

    /**
     * Thực thi tạo notification nhưng không làm hỏng
     * nghiệp vụ chính nếu hệ thống notification gặp lỗi.
     */
    public function safely(
        callable $callback
    ): mixed {
        try {
            return $callback();
        } catch (Throwable $exception) {
            report($exception);

            Log::warning(
                'Không thể tạo notification.',
                [
                    'message' => $exception->getMessage(),
                    'exception' => $exception::class,
                ]
            );

            return null;
        }
    }

    private function findForUserOrFail(
        User $user,
        string $notificationId
    ): Notification {
        return Notification::query()
            ->whereKey($notificationId)
            ->where(
                'notifiable_type',
                $user->getMorphClass()
            )
            ->where(
                'notifiable_id',
                $user->getKey()
            )
            ->firstOrFail();
    }

    private function validatePriority(
        string $priority
    ): void {
        if (! in_array(
            $priority,
            [
                'low',
                'normal',
                'high',
                'urgent',
            ],
            true
        )) {
            throw new \InvalidArgumentException(
                'Độ ưu tiên notification không hợp lệ.'
            );
        }
    }
}
