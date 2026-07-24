<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\ClientNotificationService;

class OrderObserver
{
    public function __construct(private readonly ClientNotificationService $notifications) {}

    public function updated(Order $order): void
    {
        if ($order->wasChanged('order_status')) {
            $this->notifyOrderStatus($order, (string) $order->order_status);
        }

        if ($order->wasChanged('shipping_status')) {
            $this->notifyShippingStatus($order, (string) $order->shipping_status);
        }
    }

    private function notifyOrderStatus(Order $order, string $status): void
    {
        $config = [
            'confirmed' => ['Đơn hàng đã được xác nhận', 'Cửa hàng đã xác nhận đơn %s.', 'order.confirmed'],
            'processing' => ['Đơn hàng đang được xử lý', 'Đơn %s đang được chuẩn bị.', 'order.processing'],
            'packed' => ['Đơn hàng đã đóng gói', 'Đơn %s đã được đóng gói và sẵn sàng giao.', 'order.packed'],
            'shipping' => ['Đơn hàng đang vận chuyển', 'Đơn %s đã được bàn giao cho đơn vị vận chuyển.', 'order.shipping'],
            'completed' => ['Đơn hàng đã hoàn thành', 'Đơn %s đã giao thành công.', 'order.completed'],
            'cancelled' => ['Đơn hàng đã bị hủy', 'Đơn %s đã được hủy.%s', 'order.cancelled'],
            'returned' => ['Đơn hàng đã được trả lại', 'Quy trình trả hàng của đơn %s đã hoàn tất.', 'order.returned'],
        ][$status] ?? null;

        if (! $config) return;
        $order->loadMissing('user');
        $extra = $status === 'cancelled' && $order->cancel_reason
            ? ' Lý do: '.$order->cancel_reason
            : '';

        $this->notifications->safely(fn () => $this->notifications->send(
            $order->user,
            $config[2],
            $config[0],
            sprintf($config[1], $order->order_code, $extra),
            'order',
            route('account.orders.show', $order->order_code),
            in_array($status, ['cancelled', 'returned'], true) ? 'high' : 'normal',
            ['order_id' => $order->id, 'order_code' => $order->order_code, 'status' => $status]
        ));
    }

    private function notifyShippingStatus(Order $order, string $status): void
    {
        $config = [
            'ready_to_ship' => ['Đơn hàng sẵn sàng giao', 'Đơn %s đã sẵn sàng bàn giao cho đơn vị vận chuyển.', 'shipping.ready'],
            'in_transit' => ['Đơn hàng đang trên đường giao', 'Đơn %s đang được vận chuyển đến bạn.', 'shipping.in_transit'],
            'delivered' => ['Đơn hàng đã giao thành công', 'Đơn %s đã được giao thành công.', 'shipping.delivered'],
            'failed' => ['Giao hàng chưa thành công', 'Đơn %s giao chưa thành công. Cửa hàng sẽ tiếp tục hỗ trợ bạn.', 'shipping.failed'],
            'cancelled' => ['Vận chuyển đã hủy', 'Luồng vận chuyển của đơn %s đã bị hủy.', 'shipping.cancelled'],
        ][$status] ?? null;

        if (! $config) return;
        $order->loadMissing('user');
        $this->notifications->safely(fn () => $this->notifications->send(
            $order->user, $config[2], $config[0], sprintf($config[1], $order->order_code),
            'shipping', route('account.orders.show', $order->order_code),
            $status === 'failed' ? 'high' : 'normal',
            ['order_id' => $order->id, 'order_code' => $order->order_code, 'shipping_status' => $status]
        ));
    }
}
