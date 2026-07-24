<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->string('category')->toString();
        $status = $request->string('status')->toString();

        $query = Notification::query()
            ->where('notifiable_type', $request->user()->getMorphClass())
            ->where('notifiable_id', $request->user()->getKey());

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($status === 'unread') {
            $query->whereNull('read_at');
        } elseif ($status === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $unreadCount = Notification::query()
            ->where('notifiable_type', $request->user()->getMorphClass())
            ->where('notifiable_id', $request->user()->getKey())
            ->whereNull('read_at')
            ->count();

        return view('client.account.notifications.index', compact(
            'notifications', 'unreadCount', 'category', 'status'
        ));
    }

    public function open(Request $request, Notification $notification): RedirectResponse
    {
        $this->authorizeNotification($request, $notification);
        $notification->markAsRead();

        $url = $notification->action_url;
        if (! $url || ! $this->isSafeUrl($url)) {
            $url = route('account.notifications.index');
        }

        return redirect()->to($url);
    }

    public function markRead(Request $request, Notification $notification): RedirectResponse
    {
        $this->authorizeNotification($request, $notification);
        $notification->markAsRead();
        return back();
    }

    public function markUnread(Request $request, Notification $notification): RedirectResponse
    {
        $this->authorizeNotification($request, $notification);
        $notification->markAsUnread();
        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        Notification::query()
            ->where('notifiable_type', $request->user()->getMorphClass())
            ->where('notifiable_id', $request->user()->getKey())
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);

        return back()->with('success', 'Đã đánh dấu toàn bộ thông báo là đã đọc.');
    }

    public function destroy(Request $request, Notification $notification): RedirectResponse
    {
        $this->authorizeNotification($request, $notification);
        $notification->delete();
        return back()->with('success', 'Đã xóa thông báo.');
    }

    private function authorizeNotification(Request $request, Notification $notification): void
    {
        abort_unless(
            $notification->notifiable_type === $request->user()->getMorphClass()
            && (string) $notification->notifiable_id === (string) $request->user()->getKey(),
            404
        );
    }

    private function isSafeUrl(string $url): bool
    {
        if (str_starts_with($url, '/')) {
            return true;
        }

        $host = parse_url($url, PHP_URL_HOST);
        return $host === null || $host === request()->getHost();
    }
}
