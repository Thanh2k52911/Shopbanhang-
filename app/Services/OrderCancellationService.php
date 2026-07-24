<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;
use App\Services\Admin\NotificationService;

class OrderCancellationService
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {
    }
    public function cancelByCustomer(
        string $orderCode,
        int $customerId,
        string $cancelReason
    ): Order {
        $order = Order::query()
            ->where('order_code', $orderCode)
            ->firstOrFail();

        return $this->cancel(
            order: $order,
            cancelReason: $cancelReason,
            source: 'customer',
            actorId: $customerId
        );
    }

    public function cancelByAdmin(
        Order $order,
        string $cancelReason,
        ?string $adminNote,
        int $adminId
    ): Order {
        return $this->cancel(
            order: $order,
            cancelReason: $cancelReason,
            source: 'admin',
            actorId: $adminId,
            adminNote: $adminNote
        );
    }

    /**
     * Hủy đơn hàng và đồng bộ toàn bộ dữ liệu liên quan.
     *
     * @throws Throwable
     */
    public function cancel(
        Order $order,
        string $cancelReason,
        string $source,
        ?int $actorId = null,
        ?string $adminNote = null
    ): Order {
        $cancelledOrder = DB::transaction(function () use (
            $order,
            $cancelReason,
            $source,
            $actorId,
            $adminNote
        ): Order {
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            $oldOrderStatus = (string) $lockedOrder->order_status;
            $oldShippingStatus = (string) $lockedOrder->shipping_status;
            $oldPaymentStatus = (string) $lockedOrder->payment_status;

            $this->validateCancellation(
                $lockedOrder,
                $source,
                $actorId
            );

            $orderItems = $lockedOrder->items()
                ->lockForUpdate()
                ->get([
                    'id',
                    'order_id',
                    'sku_id',
                    'quantity',
                    'product_name',
                    'sku_code',
                ]);

            foreach ($orderItems as $item) {
                if (! $item->sku_id || ! $lockedOrder->warehouse_id) {
                    continue;
                }

                $inventory = Inventory::query()
                    ->where('warehouse_id', $lockedOrder->warehouse_id)
                    ->where('sku_id', $item->sku_id)
                    ->lockForUpdate()
                    ->first();

                if (! $inventory) {
                    continue;
                }

                $releasedQuantity = min(
                    (int) $inventory->reserved_quantity,
                    (int) $item->quantity
                );

                if ($releasedQuantity <= 0) {
                    continue;
                }

                $inventory->update([
                    'reserved_quantity' => max(
                        0,
                        (int) $inventory->reserved_quantity - $releasedQuantity
                    ),
                ]);

                InventoryTransaction::query()->create([
                    'warehouse_id' => $lockedOrder->warehouse_id,
                    'sku_id' => $item->sku_id,
                    'type' => 'cancel',
                    'quantity' => $releasedQuantity,
                    'reference_type' => 'order',
                    'reference_id' => $lockedOrder->id,
                    'note' => sprintf(
                        'Hoàn giữ tồn kho do %s hủy đơn %s. Sản phẩm: %s.',
                        $source === 'customer' ? 'khách hàng' : 'Admin',
                        $lockedOrder->order_code,
                        $item->product_name
                    ),
                    'created_by' => $actorId,
                ]);
            }

            $this->restoreCouponUsage($lockedOrder);

            Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->whereIn('status', [
                    'pending',
                    'unpaid',
                    'failed',
                ])
                ->lockForUpdate()
                ->get()
                ->each(function (Payment $payment): void {
                    $payment->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                    ]);
                });

            $newAdminNote = $lockedOrder->admin_note;

            if (filled($adminNote)) {
                $noteLine = sprintf(
                    '[%s] %s',
                    now()->format('d/m/Y H:i'),
                    $adminNote
                );

                $newAdminNote = filled($newAdminNote)
                    ? $newAdminNote . PHP_EOL . $noteLine
                    : $noteLine;
            }

            $lockedOrder->update([
                'order_status' => 'cancelled',
                'shipping_status' => 'cancelled',
                'payment_status' => 'cancelled',
                'cancel_reason' => $cancelReason,
                'cancelled_by' => $actorId,
                'cancelled_at' => now(),
                'admin_note' => $newAdminNote,
            ]);

            $lockedOrder->statusHistories()->create([
                'from_status' => $oldOrderStatus,
                'to_status' => 'cancelled',
                'status_type' => 'order',
                'note' => sprintf(
                    '%s hủy đơn. Lý do: %s',
                    $source === 'customer' ? 'Khách hàng' : 'Admin',
                    $cancelReason
                ),
                'created_by' => $actorId,
                'source' => $source,
                'occurred_at' => now(),
            ]);

            if ($oldShippingStatus !== 'cancelled') {
                $lockedOrder->statusHistories()->create([
                    'from_status' => $oldShippingStatus,
                    'to_status' => 'cancelled',
                    'status_type' => 'shipping',
                    'note' => 'Hủy luồng vận chuyển do đơn hàng bị hủy.',
                    'created_by' => $actorId,
                    'source' => $source,
                    'occurred_at' => now(),
                ]);
            }

            if ($oldPaymentStatus !== 'cancelled') {
                $lockedOrder->statusHistories()->create([
                    'from_status' => $oldPaymentStatus,
                    'to_status' => 'cancelled',
                    'status_type' => 'payment',
                    'note' => 'Hủy trạng thái thanh toán do đơn hàng bị hủy.',
                    'created_by' => $actorId,
                    'source' => $source,
                    'occurred_at' => now(),
                ]);
            }

            return $lockedOrder->fresh([
                'items',
                'payments',
                'statusHistories',
                'canceller',
            ]);
        }, 3);

        if ($source === 'customer') {
            $this->notificationService->safely(function () use ($cancelledOrder): void {
                $this->notificationService->notifyCustomerCancelledOrder(
                    $cancelledOrder
                );
            });
        }

        return $cancelledOrder;
    }

    private function validateCancellation(
        Order $order,
        string $source,
        ?int $actorId
    ): void {
        if (! in_array($source, ['customer', 'admin'], true)) {
            throw ValidationException::withMessages([
                'order' => 'Nguồn hủy đơn không hợp lệ.',
            ]);
        }

        if ($source === 'customer') {
            if ((int) $order->user_id !== (int) $actorId) {
                throw ValidationException::withMessages([
                    'order' => 'Bạn không có quyền hủy đơn hàng này.',
                ]);
            }

            if ($order->order_status !== 'pending') {
                throw ValidationException::withMessages([
                    'order' => 'Bạn chỉ có thể hủy đơn đang chờ xác nhận.',
                ]);
            }
        } elseif (! $order->canBeCancelled()) {
            throw ValidationException::withMessages([
                'cancel_reason' => sprintf(
                    'Không thể hủy đơn hàng ở trạng thái “%s”.',
                    $this->orderStatusLabel((string) $order->order_status)
                ),
            ]);
        }

        if (in_array(
            (string) $order->payment_status,
            ['paid', 'partially_refunded', 'refunded'],
            true
        )) {
            throw ValidationException::withMessages([
                'cancel_reason' => 'Đơn hàng đã thanh toán phải đi qua quy trình hoàn tiền.',
            ]);
        }
    }

    private function restoreCouponUsage(Order $order): void
    {
        if (! $order->coupon_id) {
            return;
        }

        $couponUsage = CouponUsage::query()
            ->where('order_id', $order->id)
            ->where('coupon_id', $order->coupon_id)
            ->lockForUpdate()
            ->first();

        if (! $couponUsage) {
            return;
        }

        $coupon = Coupon::withTrashed()
            ->whereKey($order->coupon_id)
            ->lockForUpdate()
            ->first();

        $couponUsage->delete();

        if ($coupon) {
            $coupon->update([
                'used_count' => max(
                    0,
                    (int) $coupon->used_count - 1
                ),
            ]);
        }
    }

    private function orderStatusLabel(string $status): string
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
