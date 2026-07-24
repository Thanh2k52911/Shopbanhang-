<?php

namespace App\Observers;

use App\Models\ReturnRequest;
use App\Services\ClientNotificationService;

class ReturnRequestObserver
{
    public function __construct(private readonly ClientNotificationService $notifications) {}

    public function created(ReturnRequest $request): void
    {
        $this->notify($request, 'pending');
    }

    public function updated(ReturnRequest $request): void
    {
        if ($request->wasChanged('status')) {
            $this->notify($request, (string) $request->status);
        }
    }

    private function notify(ReturnRequest $request, string $status): void
    {
        $labels = [
            'pending' => ['Yêu cầu đã được tiếp nhận', 'Yêu cầu %s đang chờ cửa hàng xem xét.'],
            'approved' => ['Yêu cầu đã được chấp nhận', 'Yêu cầu %s đã được cửa hàng chấp nhận.'],
            'rejected' => ['Yêu cầu đã bị từ chối', 'Yêu cầu %s đã bị từ chối.%s'],
            'waiting_for_return' => ['Chờ bạn gửi hàng trả', 'Yêu cầu %s đang chờ bạn gửi sản phẩm về cửa hàng.'],
            'returning' => ['Hàng trả đang vận chuyển', 'Sản phẩm của yêu cầu %s đang được gửi về cửa hàng.'],
            'received' => ['Cửa hàng đã nhận hàng trả', 'Cửa hàng đã nhận sản phẩm của yêu cầu %s.'],
            'inspecting' => ['Sản phẩm đang được kiểm tra', 'Cửa hàng đang kiểm tra sản phẩm của yêu cầu %s.'],
            'processing' => ['Yêu cầu đang được xử lý', 'Yêu cầu %s đang được xử lý bước cuối.'],
            'completed' => ['Yêu cầu đã hoàn tất', 'Yêu cầu %s đã được xử lý hoàn tất.'],
            'cancelled' => ['Yêu cầu đã hủy', 'Yêu cầu %s đã được hủy.'],
        ][$status] ?? null;
        if (! $labels) return;

        $request->loadMissing('user');
        $reason = $status === 'rejected' && $request->rejection_reason
            ? ' Lý do: '.$request->rejection_reason
            : '';

        $this->notifications->safely(fn () => $this->notifications->send(
            $request->user,
            'return_request.'.$status,
            $labels[0],
            sprintf($labels[1], $request->return_code, $reason),
            'return',
            route('account.return-requests.show', $request->return_code),
            in_array($status, ['rejected', 'completed'], true) ? 'high' : 'normal',
            ['return_request_id' => $request->id, 'return_code' => $request->return_code, 'status' => $status]
        ));
    }
}
