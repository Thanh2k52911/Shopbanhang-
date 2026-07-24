<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\Admin\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {
    }

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:50'],
            'status' => [
                'nullable',
                Rule::in(['all', 'read', 'unread']),
            ],
            'per_page' => [
                'nullable',
                'integer',
                Rule::in([10, 20, 30, 50, 100]),
            ],
        ]);

        $status = $validated['status'] ?? 'all';

        $notifications = $this->notificationService
            ->paginateFor(
                $user,
                (int) ($validated['per_page'] ?? 20),
                $validated['category'] ?? null,
                $status === 'all' ? null : $status
            );

        $statistics = [
            'total' => $this->baseUserQuery($user)->count(),
            'unread' => $this->baseUserQuery($user)
                ->unread()
                ->count(),
            'read' => $this->baseUserQuery($user)
                ->read()
                ->count(),
            'urgent' => $this->baseUserQuery($user)
                ->where('priority', 'urgent')
                ->count(),
            'high' => $this->baseUserQuery($user)
                ->where('priority', 'high')
                ->count(),
        ];

        $categories = $this->baseUserQuery($user)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'statistics' => $statistics,
            'categories' => $categories,
        ]);
    }

    public function latest(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $notifications = $this->notificationService
            ->latestFor(
                $user,
                (int) ($validated['limit'] ?? 10)
            )
            ->map(
                fn (Notification $notification): array =>
                    $this->notificationPayload($notification)
            );

        return response()->json([
            'success' => true,
            'unread_count' => $this->notificationService
                ->unreadCountFor($user),
            'notifications' => $notifications,
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'success' => true,
            'unread_count' => $this->notificationService
                ->unreadCountFor($user),
        ]);
    }

    public function markAsRead(
        Request $request,
        string $notification
    ): JsonResponse|RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $updatedNotification = $this->notificationService
            ->markAsRead($user, $notification);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu thông báo là đã đọc.',
                'notification' => $this->notificationPayload(
                    $updatedNotification
                ),
                'unread_count' => $this->notificationService
                    ->unreadCountFor($user),
            ]);
        }

        return back()->with(
            'success',
            'Đã đánh dấu thông báo là đã đọc.'
        );
    }

    public function markAsUnread(
        Request $request,
        string $notification
    ): JsonResponse|RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $updatedNotification = $this->notificationService
            ->markAsUnread($user, $notification);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu thông báo là chưa đọc.',
                'notification' => $this->notificationPayload(
                    $updatedNotification
                ),
                'unread_count' => $this->notificationService
                    ->unreadCountFor($user),
            ]);
        }

        return back()->with(
            'success',
            'Đã đánh dấu thông báo là chưa đọc.'
        );
    }

    public function markAllAsRead(
        Request $request
    ): JsonResponse|RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $updatedCount = $this->notificationService
            ->markAllAsRead($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' =>
                    'Đã đánh dấu toàn bộ thông báo là đã đọc.',
                'updated_count' => $updatedCount,
                'unread_count' => 0,
            ]);
        }

        return back()->with(
            'success',
            'Đã đánh dấu toàn bộ thông báo là đã đọc.'
        );
    }

    public function open(
        Request $request,
        string $notification
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $notificationModel = $this->notificationService
            ->markAsRead($user, $notification);

        $actionUrl = trim(
            (string) $notificationModel->action_url
        );

        if ($actionUrl !== '') {
            return redirect()->to($actionUrl);
        }

        return redirect()
            ->route('admin.notifications.index');
    }

    public function destroy(
        Request $request,
        string $notification
    ): JsonResponse|RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $this->notificationService
            ->delete($user, $notification);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa thông báo.',
                'unread_count' => $this->notificationService
                    ->unreadCountFor($user),
            ]);
        }

        return back()->with(
            'success',
            'Đã xóa thông báo.'
        );
    }

    private function baseUserQuery(User $user)
    {
        return Notification::query()
            ->where(
                'notifiable_type',
                $user->getMorphClass()
            )
            ->where(
                'notifiable_id',
                $user->getKey()
            );
    }

    /**
     * @return array<string, mixed>
     */
    private function notificationPayload(
        Notification $notification
    ): array {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'category' => $notification->category,
            'category_label' => $notification->categoryLabel(),
            'priority' => $notification->priority,
            'priority_label' => $notification->priorityLabel(),
            'action_url' => $notification->action_url,
            'open_url' => route(
                'admin.notifications.open',
                $notification->id
            ),
            'image' => $notification->image,
            'data' => $notification->data ?? [],
            'read_at' => optional(
                $notification->read_at
            )?->toIso8601String(),
            'is_read' => $notification->isRead(),
            'is_unread' => $notification->isUnread(),
            'created_at' => optional(
                $notification->created_at
            )?->toIso8601String(),
            'created_at_human' => optional(
                $notification->created_at
            )?->diffForHumans(),
            'created_at_formatted' => optional(
                $notification->created_at
            )?->format('d/m/Y H:i:s'),
        ];
    }
}
