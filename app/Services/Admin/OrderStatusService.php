<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Services\LoyaltyService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderStatusService
{
    public function __construct(
        private readonly LoyaltyService $loyaltyService,
        private readonly NotificationService $notificationService
    ) {
    }
    private const ALLOWED_TRANSITIONS = [
        'pending' => ['confirmed'],
        'confirmed' => ['processing'],
        'processing' => ['packed'],
        'packed' => ['shipping'],
        'shipping' => ['completed'],
        'completed' => [],
        'cancelled' => [],
        'returned' => [],
    ];

    /**
     * @throws Throwable
     */
    public function updateStatus(
        Order $order,
        string $newStatus,
        ?string $note = null,
        ?int $adminId = null
    ): Order {
        $updatedOrder = DB::transaction(function () use ($order, $newStatus, $note, $adminId): Order {
            $lockedOrder = Order::query()
                ->with('payments')
                ->lockForUpdate()
                ->findOrFail($order->id);

            $oldStatus = (string) $lockedOrder->order_status;
            $oldShippingStatus = (string) $lockedOrder->shipping_status;
            $oldPaymentStatus = (string) $lockedOrder->payment_status;

            if ($oldStatus === $newStatus) {
                throw ValidationException::withMessages([
                    'order_status' => 'Đơn hàng hiện đã ở trạng thái này.',
                ]);
            }

            if ($newStatus === 'cancelled') {
                throw ValidationException::withMessages([
                    'order_status' => 'Hủy đơn phải sử dụng quy trình hủy đơn riêng.',
                ]);
            }

            if ($newStatus === 'returned') {
                throw ValidationException::withMessages([
                    'order_status' => 'Trả hàng phải được xử lý qua yêu cầu trả hàng.',
                ]);
            }

            $allowedNextStatuses = self::ALLOWED_TRANSITIONS[$oldStatus] ?? [];

            if (! in_array($newStatus, $allowedNextStatuses, true)) {
                throw ValidationException::withMessages([
                    'order_status' => sprintf(
                        'Không thể chuyển đơn hàng từ “%s” sang “%s”.',
                        $this->statusLabel($oldStatus),
                        $this->statusLabel($newStatus)
                    ),
                ]);
            }

            $updateData = ['order_status' => $newStatus];

            switch ($newStatus) {
                case 'confirmed':
                    $updateData['confirmed_at'] = now();
                    $updateData['confirmed_by'] = $adminId;
                    break;

                case 'processing':
                    $updateData['processing_at'] = now();
                    break;

                case 'packed':
                    $updateData['packed_at'] = now();
                    $updateData['shipping_status'] = 'ready_to_ship';
                    break;

                case 'shipping':
                    if (! $lockedOrder->shipments()->exists()) {
                        throw ValidationException::withMessages([
                            'order_status' => 'Phải tạo kiện vận chuyển trước khi chuyển đơn sang đang giao.',
                        ]);
                    }

                    $updateData['shipping_at'] = now();
                    $updateData['shipping_status'] = 'in_transit';
                    break;

                case 'completed':
                    if ($oldShippingStatus !== 'delivered') {
                        throw ValidationException::withMessages([
                            'order_status' => 'Chỉ được hoàn thành đơn sau khi kiện hàng đã giao thành công.',
                        ]);
                    }

                    $updateData['completed_at'] = now();
                    $updateData['shipping_status'] = 'delivered';

                    if (
                        $lockedOrder->payment_method === 'cod'
                        && $oldPaymentStatus !== 'paid'
                    ) {
                        $updateData['payment_status'] = 'paid';
                    }
                    break;
            }

            $lockedOrder->update($updateData);

            $this->createHistory(
                order: $lockedOrder,
                type: 'order',
                from: $oldStatus,
                to: $newStatus,
                note: $note,
                adminId: $adminId
            );

            if (
                array_key_exists('shipping_status', $updateData)
                && $oldShippingStatus !== $updateData['shipping_status']
            ) {
                $this->createHistory(
                    order: $lockedOrder,
                    type: 'shipping',
                    from: $oldShippingStatus,
                    to: (string) $updateData['shipping_status'],
                    note: 'Đồng bộ theo trạng thái xử lý đơn hàng.',
                    adminId: $adminId
                );
            }

            if (
                array_key_exists('payment_status', $updateData)
                && $oldPaymentStatus !== $updateData['payment_status']
            ) {
                $lockedOrder->payments()
                    ->whereNotIn('status', ['paid', 'refunded', 'cancelled'])
                    ->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'updated_at' => now(),
                    ]);

                $this->createHistory(
                    order: $lockedOrder,
                    type: 'payment',
                    from: $oldPaymentStatus,
                    to: (string) $updateData['payment_status'],
                    note: 'Đơn COD đã giao thành công và được ghi nhận thanh toán.',
                    adminId: $adminId
                );
            }

            return $lockedOrder->fresh([
                'user',
                'statusHistories',
                'payments',
                'shipments',
            ]);
        }, 3);

        if ($updatedOrder->order_status === 'completed') {
            try {
                $this->loyaltyService->awardCompletedOrder(
                    $updatedOrder,
                    $adminId
                );
            } catch (\Throwable $exception) {
                Log::error('Không thể cộng điểm cho đơn hoàn thành.', [
                    'order_id' => $updatedOrder->id,
                    'exception' => $exception,
                ]);
            }

            try {
                $this->notificationService->notifyOrderCompleted(
                    $updatedOrder
                );
            } catch (\Throwable $exception) {
                Log::error('Không thể tạo thông báo đơn hoàn thành.', [
                    'order_id' => $updatedOrder->id,
                    'exception' => $exception,
                ]);
            }
        }

        return $updatedOrder->fresh([
            'user',
            'statusHistories',
            'payments',
            'shipments',
        ]);
    }

    public function availableTransitions(Order $order): array
    {
        return collect(self::ALLOWED_TRANSITIONS[$order->order_status] ?? [])
            ->mapWithKeys(fn (string $status): array => [
                $status => $this->statusLabel($status),
            ])
            ->all();
    }

    private function createHistory(
        Order $order,
        string $type,
        string $from,
        string $to,
        ?string $note,
        ?int $adminId
    ): void {
        $order->statusHistories()->create([
            'status_type' => $type,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
            'source' => 'admin',
            'created_by' => $adminId,
            'occurred_at' => now(),
        ]);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'processing' => 'Đang xử lý',
            'packed' => 'Đã đóng gói',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'returned' => 'Đã trả hàng',
            default => $status,
        };
    }
}
