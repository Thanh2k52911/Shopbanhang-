<?php

namespace App\Services\Admin;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderCancellationService
{
    /**
     * Hủy đơn hàng từ khu vực Admin.
     *
     * @throws Throwable
     */
    public function cancel(
        Order $order,
        string $cancelReason,
        ?string $adminNote = null,
        ?int $adminId = null
    ): Order {
        return DB::transaction(function () use (
            $order,
            $cancelReason,
            $adminNote,
            $adminId
        ): Order {
            /*
            |--------------------------------------------------------------------------
            | Khóa đơn hàng
            |--------------------------------------------------------------------------
            |
            | Ngăn hai nhân viên đồng thời xử lý cùng một đơn.
            |
            */

            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            $oldOrderStatus = $lockedOrder->order_status;
            $oldShippingStatus = $lockedOrder->shipping_status;
            $oldPaymentStatus = $lockedOrder->payment_status;

            /*
            |--------------------------------------------------------------------------
            | Kiểm tra trạng thái được phép hủy
            |--------------------------------------------------------------------------
            */

            if (! $lockedOrder->canBeCancelled()) {
                throw ValidationException::withMessages([
                    'cancel_reason' => sprintf(
                        'Không thể hủy đơn hàng khi đang ở trạng thái “%s”.',
                        $this->orderStatusLabel($oldOrderStatus)
                    ),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Không hủy trực tiếp đơn đã thanh toán
            |--------------------------------------------------------------------------
            |
            | Đơn đã thanh toán phải đi qua quy trình hoàn tiền để tránh mất cân
            | bằng dữ liệu giữa order, payment và refund.
            |
            */

            if (in_array(
                $oldPaymentStatus,
                ['paid', 'partially_refunded', 'refunded'],
                true
            )) {
                throw ValidationException::withMessages([
                    'cancel_reason' =>
                        'Đơn hàng đã thanh toán không thể hủy trực tiếp. '
                        . 'Vui lòng thực hiện quy trình hoàn tiền.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Lấy và khóa sản phẩm trong đơn
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | Hoàn số lượng đang giữ trong kho
            |--------------------------------------------------------------------------
            |
            | Khi đặt hàng, hệ thống tăng reserved_quantity.
            | Hủy đơn sẽ giảm lại số lượng giữ, không tăng quantity vì hàng chưa
            | thực sự được xuất khỏi kho.
            |
            */

            foreach ($orderItems as $item) {
                if (! $item->sku_id || ! $lockedOrder->warehouse_id) {
                    continue;
                }

                $inventory = Inventory::query()
                    ->where(
                        'warehouse_id',
                        $lockedOrder->warehouse_id
                    )
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
                        (int) $inventory->reserved_quantity
                        - $releasedQuantity
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
                        'Hoàn giữ tồn kho do Admin hủy đơn %s. Sản phẩm: %s.',
                        $lockedOrder->order_code,
                        $item->product_name
                    ),
                    'created_by' => $adminId,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Hoàn lượt sử dụng coupon
            |--------------------------------------------------------------------------
            */

            if ($lockedOrder->coupon_id) {
                $couponUsage = CouponUsage::query()
                    ->where('order_id', $lockedOrder->id)
                    ->where('coupon_id', $lockedOrder->coupon_id)
                    ->lockForUpdate()
                    ->first();

                if ($couponUsage) {
                    $coupon = Coupon::withTrashed()
                        ->whereKey($lockedOrder->coupon_id)
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
            }

            /*
            |--------------------------------------------------------------------------
            | Hủy các payment chưa hoàn tất
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | Ghép ghi chú nội bộ
            |--------------------------------------------------------------------------
            */

            $newAdminNote = $lockedOrder->admin_note;

            if ($adminNote) {
                $noteLine = sprintf(
                    '[%s] %s',
                    now()->format('d/m/Y H:i'),
                    $adminNote
                );

                $newAdminNote = filled($newAdminNote)
                    ? $newAdminNote . PHP_EOL . $noteLine
                    : $noteLine;
            }

            /*
            |--------------------------------------------------------------------------
            | Cập nhật đơn hàng
            |--------------------------------------------------------------------------
            */

            $lockedOrder->update([
                'order_status' => 'cancelled',
                'shipping_status' => 'cancelled',
                'payment_status' => 'cancelled',

                'cancel_reason' => $cancelReason,
                'cancelled_by' => $adminId,
                'cancelled_at' => now(),

                'admin_note' => $newAdminNote,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Ghi lịch sử trạng thái đơn hàng
            |--------------------------------------------------------------------------
            */

            $lockedOrder->statusHistories()->create([
                'from_status' => $oldOrderStatus,
                'to_status' => 'cancelled',
                'status_type' => 'order',
                'note' => 'Admin hủy đơn. Lý do: ' . $cancelReason,
                'created_by' => $adminId,
                'source' => 'admin',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Ghi lịch sử trạng thái vận chuyển
            |--------------------------------------------------------------------------
            */

            if ($oldShippingStatus !== 'cancelled') {
                $lockedOrder->statusHistories()->create([
                    'from_status' => $oldShippingStatus,
                    'to_status' => 'cancelled',
                    'status_type' => 'shipping',
                    'note' =>
                        'Hủy luồng vận chuyển do đơn hàng bị hủy.',
                    'created_by' => $adminId,
                    'source' => 'admin',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Ghi lịch sử trạng thái thanh toán
            |--------------------------------------------------------------------------
            */

            if ($oldPaymentStatus !== 'cancelled') {
                $lockedOrder->statusHistories()->create([
                    'from_status' => $oldPaymentStatus,
                    'to_status' => 'cancelled',
                    'status_type' => 'payment',
                    'note' =>
                        'Hủy trạng thái thanh toán do đơn hàng bị hủy.',
                    'created_by' => $adminId,
                    'source' => 'admin',
                ]);
            }

            return $lockedOrder->fresh([
                'items',
                'payments',
                'statusHistories',
                'canceller',
            ]);
        }, 3);
    }

    /**
     * Tên trạng thái đơn bằng tiếng Việt.
     */
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
