<?php

namespace App\Observers;

use App\Models\Refund;
use App\Services\ClientNotificationService;

class RefundObserver
{
    public function __construct(private readonly ClientNotificationService $notifications) {}

    public function created(Refund $refund): void { $this->notify($refund, (string) $refund->status); }
    public function updated(Refund $refund): void { if ($refund->wasChanged('status')) $this->notify($refund, (string) $refund->status); }

    private function notify(Refund $refund, string $status): void
    {
        $labels = [
            'pending' => ['Yêu cầu hoàn tiền đã tạo', 'Khoản hoàn %s đang chờ xử lý.'],
            'processing' => ['Đang xử lý hoàn tiền', 'Khoản hoàn %s đang được xử lý.'],
            'completed' => ['Hoàn tiền thành công', 'Khoản tiền %sđ đã được hoàn thành.'],
            'failed' => ['Hoàn tiền chưa thành công', 'Khoản hoàn %s chưa thành công.%s'],
            'cancelled' => ['Yêu cầu hoàn tiền đã hủy', 'Khoản hoàn %s đã được hủy.'],
        ][$status] ?? null;
        if (! $labels) return;

        $refund->loadMissing(['order.user', 'returnRequest']);
        $user = $refund->order?->user;
        $code = $refund->refund_code ?: '#'.$refund->id;
        $value = $status === 'completed' ? number_format((float) $refund->amount, 0, ',', '.') : $code;
        $reason = $status === 'failed' && $refund->failure_reason ? ' Lý do: '.$refund->failure_reason : '';
        $url = $refund->returnRequest
            ? route('account.return-requests.show', $refund->returnRequest->return_code)
            : ($refund->order ? route('account.orders.show', $refund->order->order_code) : route('account.notifications.index'));

        $this->notifications->safely(fn () => $this->notifications->send(
            $user, 'refund.'.$status, $labels[0], sprintf($labels[1], $value, $reason),
            'return', $url, in_array($status, ['completed', 'failed'], true) ? 'high' : 'normal',
            ['refund_id' => $refund->id, 'refund_code' => $code, 'status' => $status]
        ));
    }
}
