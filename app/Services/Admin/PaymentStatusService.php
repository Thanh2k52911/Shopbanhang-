<?php

namespace App\Services\Admin;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentStatusService
{
    private const STATUS_PENDING = 'pending';
    private const STATUS_PROCESSING = 'processing';
    private const STATUS_PAID = 'paid';
    private const STATUS_FAILED = 'failed';
    private const STATUS_CANCELLED = 'cancelled';

    /**
     * Luồng chuyển trạng thái thanh toán hợp lệ.
     */
    private const ALLOWED_TRANSITIONS = [
        self::STATUS_PENDING => [
            self::STATUS_PROCESSING,
            self::STATUS_PAID,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_PROCESSING => [
            self::STATUS_PAID,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_PAID => [],
        self::STATUS_FAILED => [],
        self::STATUS_CANCELLED => [],
    ];

    /**
     * Cập nhật trạng thái Payment và đồng bộ trạng thái thanh toán của Order.
     */
    public function updateStatus(
        Payment $payment,
        array $data,
        int $adminId
    ): Payment {
        return DB::transaction(function () use ($payment, $data, $adminId): Payment {
            $lockedPayment = Payment::query()
                ->with('order')
                ->lockForUpdate()
                ->findOrFail($payment->id);

            $order = $lockedPayment->order()
                ->lockForUpdate()
                ->first();

            $oldStatus = (string) $lockedPayment->status;
            $newStatus = (string) $data['status'];

            if ($oldStatus === $newStatus) {
                throw ValidationException::withMessages([
                    'status' => 'Thanh toán đã ở trạng thái này.',
                ]);
            }

            $allowedStatuses = self::ALLOWED_TRANSITIONS[$oldStatus] ?? [];

            if (! in_array($newStatus, $allowedStatuses, true)) {
                throw ValidationException::withMessages([
                    'status' => sprintf(
                        'Không thể chuyển thanh toán từ “%s” sang “%s”.',
                        $this->label($oldStatus),
                        $this->label($newStatus)
                    ),
                ]);
            }

            /*
             * COD chỉ được xác nhận đã thu tiền sau khi đơn đã giao thành công.
             * Các phương thức chuyển khoản/cổng thanh toán có thể được xác nhận
             * độc lập với trạng thái vận chuyển.
             */
            if (
                $newStatus === self::STATUS_PAID
                && $lockedPayment->method === 'cod'
                && $order
                && $order->shipping_status !== 'delivered'
            ) {
                throw ValidationException::withMessages([
                    'status' => 'Đơn COD chỉ được đánh dấu đã thanh toán sau khi giao hàng thành công.',
                ]);
            }

            $updateData = [
                'status' => $newStatus,
            ];

            if (! empty($data['provider_transaction_id'])) {
                $updateData['provider_transaction_id'] = $data['provider_transaction_id'];
            }

            switch ($newStatus) {
                case self::STATUS_PROCESSING:
                    $updateData['failure_reason'] = null;
                    break;

                case self::STATUS_PAID:
                    $updateData['paid_at'] = $lockedPayment->paid_at ?? now();
                    $updateData['failure_reason'] = null;
                    $updateData['cancelled_at'] = null;
                    break;

                case self::STATUS_FAILED:
                    $updateData['failure_reason'] = $data['failure_reason'] ?? null;
                    break;

                case self::STATUS_CANCELLED:
                    $updateData['cancelled_at'] = $lockedPayment->cancelled_at ?? now();
                    break;
            }

            $lockedPayment->update($updateData);

            $lockedPayment->transactions()->create([
                'type' => $newStatus === self::STATUS_CANCELLED
                    ? 'cancel'
                    : 'payment',
                'transaction_id' => $lockedPayment->provider_transaction_id,
                'amount' => $lockedPayment->amount,
                'status' => $newStatus,
                'message' => $data['note']
                    ?? sprintf(
                        'Admin cập nhật thanh toán từ “%s” sang “%s”.',
                        $this->label($oldStatus),
                        $this->label($newStatus)
                    ),
                'response_code' => null,
                'request_data' => [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'updated_by' => $adminId,
                ],
                'response_data' => null,
                'ip_address' => request()->ip(),
                'processed_at' => now(),
            ]);

            if ($order && $order->payment_status !== $newStatus) {
                $oldOrderPaymentStatus = (string) $order->payment_status;

                $order->update([
                    'payment_status' => $newStatus,
                ]);

                $order->statusHistories()->create([
                    'from_status' => $oldOrderPaymentStatus,
                    'to_status' => $newStatus,
                    'status_type' => 'payment',
                    'note' => $data['note']
                        ?? sprintf(
                            'Admin cập nhật trạng thái thanh toán từ “%s” sang “%s”.',
                            $this->label($oldOrderPaymentStatus),
                            $this->label($newStatus)
                        ),
                    'created_by' => $adminId,
                    'source' => 'admin',
                    'occurred_at' => now(),
                ]);
            }

            return $lockedPayment->fresh([
                'order',
                'transactions' => fn ($query) => $query->latest('id'),
            ]);
        }, 3);
    }

    /**
     * Danh sách trạng thái có thể chuyển tiếp.
     */
    public function availableTransitions(Payment $payment): array
    {
        return collect(self::ALLOWED_TRANSITIONS[$payment->status] ?? [])
            ->mapWithKeys(fn (string $status): array => [
                $status => $this->label($status),
            ])
            ->all();
    }

    private function label(string $status): string
    {
        return match ($status) {
            'unpaid' => 'Chưa thanh toán',
            self::STATUS_PENDING => 'Chờ thanh toán',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_PAID => 'Đã thanh toán',
            self::STATUS_FAILED => 'Thanh toán thất bại',
            self::STATUS_CANCELLED => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền',
            'partially_refunded' => 'Hoàn tiền một phần',
            default => $status,
        };
    }
}
