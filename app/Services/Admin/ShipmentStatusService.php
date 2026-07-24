<?php

namespace App\Services\Admin;

use App\Models\Payment;
use App\Models\Shipment;
use App\Services\LoyaltyService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShipmentStatusService
{
    public function __construct(
        private readonly LoyaltyService $loyaltyService,
        private readonly NotificationService $notificationService
    ) {
    }
    /**
     * Luồng chuyển trạng thái hợp lệ.
     */
    private const ALLOWED_TRANSITIONS = [
        'ready_to_ship' => [
            'picked_up',
            'cancelled',
        ],

        'picked_up' => [
            'in_transit',
            'delivery_failed',
        ],

        'in_transit' => [
            'out_for_delivery',
            'delivery_failed',
        ],

        'out_for_delivery' => [
            'delivered',
            'delivery_failed',
        ],

        'delivery_failed' => [
            'in_transit',
            'returned',
        ],

        'delivered' => [],

        'returned' => [],

        'cancelled' => [],
    ];

    /**
     * Cập nhật trạng thái Shipment.
     */
    public function updateStatus(
        Shipment $shipment,
        string $newStatus,
        ?string $location = null,
        ?string $description = null,
        ?int $adminId = null
    ): Shipment {
        $updatedShipment = DB::transaction(function () use (
            $shipment,
            $newStatus,
            $location,
            $description,
            $adminId
        ): Shipment {
            /*
            |--------------------------------------------------------------------------
            | Khóa Shipment và Order
            |--------------------------------------------------------------------------
            */

            $lockedShipment = Shipment::query()
                ->with([
                    'order',
                    'warehouse:id,name',
                ])
                ->lockForUpdate()
                ->findOrFail($shipment->id);

            $lockedOrder = $lockedShipment->order()
                ->lockForUpdate()
                ->firstOrFail();

            $oldShipmentStatus = $lockedShipment->status;
            $oldOrderStatus = $lockedOrder->order_status;
            $oldShippingStatus = $lockedOrder->shipping_status;
            $oldPaymentStatus = $lockedOrder->payment_status;

            /*
            |--------------------------------------------------------------------------
            | Không cập nhật lại cùng trạng thái
            |--------------------------------------------------------------------------
            */

            if ($oldShipmentStatus === $newStatus) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Kiện hàng hiện đã ở trạng thái này.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Kiểm tra bước chuyển hợp lệ
            |--------------------------------------------------------------------------
            */

            $allowedStatuses = self::ALLOWED_TRANSITIONS[
                $oldShipmentStatus
            ] ?? [];

            if (! in_array($newStatus, $allowedStatuses, true)) {
                throw ValidationException::withMessages([
                    'status' => sprintf(
                        'Không thể chuyển kiện hàng từ “%s” sang “%s”.',
                        $this->statusLabel($oldShipmentStatus),
                        $this->statusLabel($newStatus)
                    ),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Dữ liệu cập nhật Shipment
            |--------------------------------------------------------------------------
            */

            $shipmentUpdateData = [
                'status' => $newStatus,
            ];

            switch ($newStatus) {
                case 'picked_up':
                    $shipmentUpdateData['picked_up_at'] = now();
                    break;

                case 'delivered':
                    $shipmentUpdateData['delivered_at'] = now();
                    break;

                case 'delivery_failed':
                    $shipmentUpdateData['failed_at'] = now();
                    break;

                case 'cancelled':
                    $shipmentUpdateData['cancelled_at'] = now();
                    break;
            }

            $lockedShipment->update($shipmentUpdateData);

            /*
            |--------------------------------------------------------------------------
            | Ghi lịch sử Shipment
            |--------------------------------------------------------------------------
            */

            $lockedShipment->statusHistories()->create([
                'from_status' => $oldShipmentStatus,
                'to_status' => $newStatus,
                'location' => $location,
                'description' =>
                    $description
                    ?: $this->defaultDescription($newStatus),

                'source' => 'admin',
                'created_by' => $adminId,
                'provider_data' => null,
                'occurred_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Đồng bộ trạng thái Order
            |--------------------------------------------------------------------------
            */

            $orderUpdateData = [];

            switch ($newStatus) {
                case 'picked_up':
                    $orderUpdateData['shipping_status'] = 'picked_up';
                    break;

                case 'in_transit':
                    $orderUpdateData['order_status'] = 'shipping';
                    $orderUpdateData['shipping_status'] = 'in_transit';

                    if (! $lockedOrder->shipping_at) {
                        $orderUpdateData['shipping_at'] = now();
                    }
                    break;

                case 'out_for_delivery':
                    $orderUpdateData['order_status'] = 'shipping';
                    $orderUpdateData['shipping_status'] = 'in_transit';

                    if (! $lockedOrder->shipping_at) {
                        $orderUpdateData['shipping_at'] = now();
                    }
                    break;

                case 'delivered':
                    $orderUpdateData['order_status'] = 'completed';
                    $orderUpdateData['shipping_status'] = 'delivered';
                    $orderUpdateData['completed_at'] = now();

                    if (
                        $lockedOrder->payment_method === 'cod'
                        && $lockedOrder->payment_status !== 'paid'
                    ) {
                        $orderUpdateData['payment_status'] = 'paid';
                    }
                    break;

                case 'delivery_failed':
                    $orderUpdateData['shipping_status'] = 'failed';
                    break;

                case 'returned':
                    $orderUpdateData['order_status'] = 'returned';
                    $orderUpdateData['shipping_status'] = 'returned';
                    break;

                case 'cancelled':
                    $orderUpdateData['shipping_status'] = 'cancelled';
                    break;
            }

            if (! empty($orderUpdateData)) {
                $lockedOrder->update($orderUpdateData);
            }

            /*
            |--------------------------------------------------------------------------
            | Đồng bộ bản ghi Payment khi giao COD thành công
            |--------------------------------------------------------------------------
            */

            if (
                $newStatus === 'delivered'
                && $lockedOrder->payment_method === 'cod'
            ) {
                Payment::query()
                    ->where('order_id', $lockedOrder->id)
                    ->whereIn('status', ['pending', 'processing'])
                    ->lockForUpdate()
                    ->get()
                    ->each(function (Payment $payment): void {
                        $payment->update([
                            'status' => 'paid',
                            'paid_at' => $payment->paid_at ?: now(),
                            'failure_reason' => null,
                            'cancelled_at' => null,
                        ]);
                    });
            }

            /*
            |--------------------------------------------------------------------------
            | Ghi timeline vận chuyển của Order
            |--------------------------------------------------------------------------
            */

            if (
                array_key_exists('shipping_status', $orderUpdateData)
                && $oldShippingStatus
                    !== $orderUpdateData['shipping_status']
            ) {
                $lockedOrder->statusHistories()->create([
                    'from_status' => $oldShippingStatus,
                    'to_status' =>
                        $orderUpdateData['shipping_status'],

                    'status_type' => 'shipping',
                    'note' =>
                        $description
                        ?: $this->defaultDescription($newStatus),

                    'created_by' => $adminId,
                    'source' => 'admin',
                    'occurred_at' => now(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Ghi timeline trạng thái Order
            |--------------------------------------------------------------------------
            */

            if (
                array_key_exists('order_status', $orderUpdateData)
                && $oldOrderStatus
                    !== $orderUpdateData['order_status']
            ) {
                $lockedOrder->statusHistories()->create([
                    'from_status' => $oldOrderStatus,
                    'to_status' =>
                        $orderUpdateData['order_status'],

                    'status_type' => 'order',
                    'note' =>
                        'Đồng bộ theo vòng đời kiện vận chuyển.',

                    'created_by' => $adminId,
                    'source' => 'admin',
                    'occurred_at' => now(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Ghi timeline thanh toán COD
            |--------------------------------------------------------------------------
            */

            if (
                array_key_exists('payment_status', $orderUpdateData)
                && $oldPaymentStatus
                    !== $orderUpdateData['payment_status']
            ) {
                $lockedOrder->statusHistories()->create([
                    'from_status' => $oldPaymentStatus,
                    'to_status' =>
                        $orderUpdateData['payment_status'],

                    'status_type' => 'payment',
                    'note' =>
                        'Đơn COD đã giao thành công và được ghi nhận thanh toán.',

                    'created_by' => $adminId,
                    'source' => 'admin',
                    'occurred_at' => now(),
                ]);
            }

            return $lockedShipment->fresh([
                'order.user',
                'shippingMethod',
                'warehouse',
                'items.orderItem',
                'statusHistories.creator',
            ]);
        }, 3);

        if ($updatedShipment->order?->order_status === 'completed') {
            try {
                $this->loyaltyService->awardCompletedOrder(
                    $updatedShipment->order,
                    $adminId
                );
            } catch (\Throwable $exception) {
                Log::error('Không thể cộng điểm cho đơn đã giao.', [
                    'order_id' => $updatedShipment->order->id,
                    'shipment_id' => $updatedShipment->id,
                    'exception' => $exception,
                ]);
            }

            try {
                $this->notificationService->notifyOrderCompleted(
                    $updatedShipment->order
                );
            } catch (\Throwable $exception) {
                Log::error('Không thể tạo thông báo đơn hoàn thành.', [
                    'order_id' => $updatedShipment->order->id,
                    'shipment_id' => $updatedShipment->id,
                    'exception' => $exception,
                ]);
            }
        }

        return $updatedShipment->fresh([
            'order.user',
            'shippingMethod',
            'warehouse',
            'items.orderItem',
            'statusHistories.creator',
        ]);
    }

    /**
     * Danh sách trạng thái tiếp theo.
     */
    public function availableTransitions(
        Shipment $shipment
    ): array {
        $statuses = self::ALLOWED_TRANSITIONS[
            $shipment->status
        ] ?? [];

        return collect($statuses)
            ->mapWithKeys(
                fn (string $status): array => [
                    $status => $this->statusLabel($status),
                ]
            )
            ->all();
    }

    /**
     * Tên trạng thái tiếng Việt.
     */
    private function statusLabel(string $status): string
    {
        return match ($status) {
            'ready_to_ship' => 'Sẵn sàng giao',
            'picked_up' => 'Đã lấy hàng',
            'in_transit' => 'Đang vận chuyển',
            'out_for_delivery' => 'Đang giao hàng',
            'delivered' => 'Đã giao hàng',
            'delivery_failed' => 'Giao hàng thất bại',
            'returned' => 'Đã hoàn hàng',
            'cancelled' => 'Đã hủy',
            default => $status,
        };
    }

    /**
     * Mô tả mặc định khi không nhập.
     */
    private function defaultDescription(
        string $status
    ): string {
        return match ($status) {
            'picked_up' =>
                'Đơn vị vận chuyển đã lấy kiện hàng.',

            'in_transit' =>
                'Kiện hàng đang được vận chuyển.',

            'out_for_delivery' =>
                'Kiện hàng đang được giao đến người nhận.',

            'delivered' =>
                'Kiện hàng đã được giao thành công.',

            'delivery_failed' =>
                'Giao kiện hàng không thành công.',

            'returned' =>
                'Kiện hàng đã được hoàn trả.',

            'cancelled' =>
                'Kiện vận chuyển đã bị hủy.',

            default =>
                'Trạng thái kiện hàng đã được cập nhật.',
        };
    }
}
