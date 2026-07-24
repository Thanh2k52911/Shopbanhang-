<?php

namespace App\Services\Admin;

use App\Models\Payment;
use App\Models\Refund;
use App\Models\ReturnRequest;
use App\Services\LoyaltyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function __construct(
        private readonly ReturnInventoryService $returnInventoryService,
        private readonly LoyaltyService $loyaltyService
    ) {
    }
    /**
     * Các bước chuyển trạng thái hợp lệ.
     */
    private const ALLOWED_TRANSITIONS = [
        'pending' => [
            'processing',
            'cancelled',
        ],

        'processing' => [
            'completed',
            'failed',
            'cancelled',
        ],

        'completed' => [],

        'failed' => [],

        'cancelled' => [],
    ];

    /**
     * Tạo yêu cầu hoàn tiền cho Payment.
     */
    public function create(
        Payment $payment,
        array $data,
        int $adminId
    ): Refund {
        return DB::transaction(function () use (
            $payment,
            $data,
            $adminId
        ): Refund {
            $lockedPayment = Payment::query()
                ->with('order')
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if (! $lockedPayment->order) {
                throw ValidationException::withMessages([
                    'refund' =>
                        'Không tìm thấy đơn hàng liên quan đến thanh toán này.',
                ]);
            }

            if (! $lockedPayment->canBeRefunded()) {
                throw ValidationException::withMessages([
                    'refund' =>
                        'Chỉ Payment đã thanh toán hoặc đã hoàn một phần mới có thể hoàn tiền.',
                ]);
            }

            $remainingAmount = $this->calculateRemainingAmount(
                $lockedPayment
            );

            $refundAmount = (float) $data['amount'];

            if ($refundAmount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Số tiền hoàn phải lớn hơn 0.',
                ]);
            }

            if ($remainingAmount <= 0) {
                throw ValidationException::withMessages([
                    'amount' =>
                        'Payment này không còn số tiền có thể tạo yêu cầu hoàn.',
                ]);
            }

            if ($refundAmount > $remainingAmount) {
                throw ValidationException::withMessages([
                    'amount' => sprintf(
                        'Số tiền hoàn không được vượt quá %s₫.',
                        number_format(
                            $remainingAmount,
                            0,
                            ',',
                            '.'
                        )
                    ),
                ]);
            }

            $refund = Refund::query()->create([
                'refund_code' => $this->generateRefundCode(),

                'return_request_id' => null,

                'order_id' => $lockedPayment->order_id,

                'payment_id' => $lockedPayment->id,

                'amount' => $refundAmount,

                'method' => $data['method'],

                'status' => 'pending',

                'provider_transaction_id' => null,

                'bank_name' =>
                    $data['bank_name'] ?? null,

                'bank_account_number' =>
                    $data['bank_account_number'] ?? null,

                'bank_account_name' =>
                    $data['bank_account_name'] ?? null,

                'reason' => $data['reason'],

                'admin_note' =>
                    $data['admin_note'] ?? null,

                'failure_reason' => null,

                'processed_by' => null,

                'processed_at' => null,

                'completed_at' => null,

                'failed_at' => null,

                'cancelled_at' => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Ghi giao dịch tạo yêu cầu Refund
            |--------------------------------------------------------------------------
            */

            $lockedPayment->transactions()->create([
                'type' => 'refund',

                'transaction_id' => $refund->refund_code,

                'amount' => $refund->amount,

                'status' => 'pending',

                'response_code' => null,

                'message' => sprintf(
                    'Admin đã tạo yêu cầu hoàn tiền %s.',
                    $refund->refund_code
                ),

                'request_data' => [
                    'refund_code' => $refund->refund_code,
                    'method' => $refund->method,
                    'reason' => $refund->reason,
                    'admin_id' => $adminId,
                ],

                'response_data' => null,

                'ip_address' => request()->ip(),

                'processed_at' => now(),
            ]);

            return $refund->fresh([
                'order',
                'payment',
                'processor',
                'returnRequest',
            ]);
        }, 3);
    }

    /**
     * Cập nhật trạng thái Refund.
     */
    public function updateStatus(
        Refund $refund,
        array $data,
        int $adminId
    ): Refund {
        return DB::transaction(function () use (
            $refund,
            $data,
            $adminId
        ): Refund {
            /*
            |--------------------------------------------------------------------------
            | Khóa Refund, Payment và Order
            |--------------------------------------------------------------------------
            */

            $lockedRefund = Refund::query()
                ->lockForUpdate()
                ->findOrFail($refund->id);

            $lockedPayment = $lockedRefund->payment()
                ->lockForUpdate()
                ->firstOrFail();

            $lockedOrder = $lockedRefund->order()
                ->lockForUpdate()
                ->first();

            $oldRefundStatus = $lockedRefund->status;
            $newRefundStatus = $data['status'];

            /*
            |--------------------------------------------------------------------------
            | Không cập nhật lại cùng trạng thái
            |--------------------------------------------------------------------------
            */

            if ($oldRefundStatus === $newRefundStatus) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Yêu cầu hoàn tiền hiện đã ở trạng thái này.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Kiểm tra luồng trạng thái
            |--------------------------------------------------------------------------
            */

            $allowedStatuses = self::ALLOWED_TRANSITIONS[
                $oldRefundStatus
            ] ?? [];

            if (! in_array(
                $newRefundStatus,
                $allowedStatuses,
                true
            )) {
                throw ValidationException::withMessages([
                    'status' => sprintf(
                        'Không thể chuyển Refund từ “%s” sang “%s”.',
                        $this->statusLabel($oldRefundStatus),
                        $this->statusLabel($newRefundStatus)
                    ),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Dữ liệu cập nhật Refund
            |--------------------------------------------------------------------------
            */

            $refundUpdateData = [
                'status' => $newRefundStatus,
            ];

            if (! empty($data['provider_transaction_id'])) {
                $refundUpdateData['provider_transaction_id'] =
                    $data['provider_transaction_id'];
            }

            if (array_key_exists('admin_note', $data)) {
                $refundUpdateData['admin_note'] =
                    $this->appendAdminNote(
                        currentNote: $lockedRefund->admin_note,
                        newNote: $data['admin_note'],
                        adminId: $adminId
                    );
            }

            switch ($newRefundStatus) {
                case 'processing':
                    $refundUpdateData['processed_by'] = $adminId;
                    $refundUpdateData['processed_at'] = now();
                    $refundUpdateData['failure_reason'] = null;
                    break;

                case 'completed':
                    $refundUpdateData['processed_by'] =
                        $lockedRefund->processed_by ?: $adminId;

                    $refundUpdateData['processed_at'] =
                        $lockedRefund->processed_at ?: now();

                    $refundUpdateData['completed_at'] = now();
                    $refundUpdateData['failure_reason'] = null;
                    break;

                case 'failed':
                    $refundUpdateData['processed_by'] =
                        $lockedRefund->processed_by ?: $adminId;

                    $refundUpdateData['processed_at'] =
                        $lockedRefund->processed_at ?: now();

                    $refundUpdateData['failed_at'] = now();

                    $refundUpdateData['failure_reason'] =
                        $data['failure_reason'];
                    break;

                case 'cancelled':
                    $refundUpdateData['cancelled_at'] = now();
                    break;
            }

            $lockedRefund->update($refundUpdateData);

            /*
            |--------------------------------------------------------------------------
            | Ghi PaymentTransaction
            |--------------------------------------------------------------------------
            */

            $lockedPayment->transactions()->create([
                'type' => 'refund',

                'transaction_id' =>
                    $lockedRefund->provider_transaction_id
                    ?: $lockedRefund->refund_code,

                'amount' => $lockedRefund->amount,

                'status' => $newRefundStatus,

                'response_code' => null,

                'message' =>
                    $data['admin_note']
                    ?: $this->defaultTransactionMessage(
                        $lockedRefund,
                        $newRefundStatus
                    ),

                'request_data' => [
                    'refund_id' => $lockedRefund->id,
                    'refund_code' => $lockedRefund->refund_code,
                    'from_status' => $oldRefundStatus,
                    'to_status' => $newRefundStatus,
                    'admin_id' => $adminId,
                ],

                'response_data' => null,

                'ip_address' => request()->ip(),

                'processed_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Đồng bộ Payment khi Refund hoàn tất
            |--------------------------------------------------------------------------
            */

            if ($newRefundStatus === 'completed') {
                $this->synchronizeCompletedRefund(
                    payment: $lockedPayment,
                    order: $lockedOrder,
                    adminId: $adminId,
                    refund: $lockedRefund
                );

                $this->completeReturnRequestIfEligible(
                    refund: $lockedRefund,
                    adminId: $adminId
                );

                $this->loyaltyService->reverseCompletedRefund(
                    $lockedRefund,
                    $adminId
                );
            }

            return $lockedRefund->fresh([
                'order',
                'payment',
                'processor',
                'returnRequest',
            ]);
        }, 3);
    }

    /**
     * Các trạng thái tiếp theo của Refund.
     */
    public function availableTransitions(
        Refund $refund
    ): array {
        $statuses = self::ALLOWED_TRANSITIONS[
            $refund->status
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
     * Số tiền còn có thể tạo yêu cầu hoàn.
     *
     * Refund pending và processing được xem là số tiền đang được giữ,
     * nhằm tránh tạo nhiều yêu cầu làm tổng tiền vượt Payment.
     */
    public function remainingRefundableAmount(
        Payment $payment
    ): float {
        return $this->calculateRemainingAmount($payment);
    }

    /**
     * Tính số tiền còn có thể tạo yêu cầu.
     */
    private function calculateRemainingAmount(
        Payment $payment
    ): float {
        $reservedRefundAmount = (float) $payment
            ->refunds()
            ->whereIn('status', [
                'pending',
                'processing',
                'completed',
            ])
            ->sum('amount');

        return max(
            0,
            (float) $payment->amount - $reservedRefundAmount
        );
    }

    /**
     * Đồng bộ Payment và Order khi Refund hoàn thành.
     */
    private function synchronizeCompletedRefund(
        Payment $payment,
        mixed $order,
        int $adminId,
        Refund $refund
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Tổng tiền Refund đã hoàn thành
        |--------------------------------------------------------------------------
        */

        $completedRefundAmount = (float) $payment
            ->refunds()
            ->where('status', 'completed')
            ->sum('amount');

        $paymentAmount = (float) $payment->amount;

        $newPaymentStatus = $completedRefundAmount >= $paymentAmount
            ? 'refunded'
            : 'partially_refunded';

        $oldPaymentStatus = $payment->status;

        if ($oldPaymentStatus !== $newPaymentStatus) {
            $payment->update([
                'status' => $newPaymentStatus,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Đồng bộ trạng thái thanh toán trên Order
        |--------------------------------------------------------------------------
        */

        if ($order) {
            $oldOrderPaymentStatus = $order->payment_status;

            if ($oldOrderPaymentStatus !== $newPaymentStatus) {
                $order->update([
                    'payment_status' => $newPaymentStatus,
                ]);

                $order->statusHistories()->create([
                    'from_status' => $oldOrderPaymentStatus,

                    'to_status' => $newPaymentStatus,

                    'status_type' => 'payment',

                    'note' => sprintf(
                        'Hoàn tiền %s đã hoàn tất với số tiền %s₫.',
                        $refund->refund_code,
                        number_format(
                            (float) $refund->amount,
                            0,
                            ',',
                            '.'
                        )
                    ),

                    'created_by' => $adminId,

                    'source' => 'admin',

                    'occurred_at' => now(),
                ]);
            }
        }
    }

    private function completeReturnRequestIfEligible(
        Refund $refund,
        int $adminId
    ): void {
        if (! $refund->return_request_id) {
            return;
        }

        $returnRequest = ReturnRequest::query()
            ->lockForUpdate()
            ->find($refund->return_request_id);

        if (! $returnRequest || $returnRequest->isTerminal()) {
            return;
        }

        $requiredAmount = (float) (
            $returnRequest->approved_amount
            ?: $returnRequest->requested_amount
        );

        $completedAmount = (float) Refund::query()
            ->where('return_request_id', $returnRequest->id)
            ->where('status', 'completed')
            ->sum('amount');

        if ($requiredAmount > 0 && $completedAmount + 0.01 < $requiredAmount) {
            return;
        }

        $oldStatus = $returnRequest->status;
        $returnRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
            'processed_by' => $adminId,
        ]);

        $returnRequest->statusHistories()->create([
            'from_status' => $oldStatus,
            'to_status' => 'completed',
            'note' => 'Hệ thống tự động hoàn tất yêu cầu sau khi đã hoàn đủ tiền.',
            'created_by' => $adminId,
            'source' => 'system',
        ]);

        $this->returnInventoryService->processCompletedReturn(
            $returnRequest->fresh(),
            $adminId
        );
    }

    /**
     * Gắn thêm ghi chú nội bộ.
     */
    private function appendAdminNote(
        ?string $currentNote,
        ?string $newNote,
        int $adminId
    ): ?string {
        if (blank($newNote)) {
            return $currentNote;
        }

        $formattedNote = sprintf(
            '[%s] Admin #%d: %s',
            now()->format('d/m/Y H:i'),
            $adminId,
            $newNote
        );

        if (blank($currentNote)) {
            return $formattedNote;
        }

        return $currentNote
            . PHP_EOL
            . $formattedNote;
    }

    /**
     * Nội dung giao dịch mặc định.
     */
    private function defaultTransactionMessage(
        Refund $refund,
        string $status
    ): string {
        return match ($status) {
            'processing' => sprintf(
                'Yêu cầu hoàn tiền %s đang được xử lý.',
                $refund->refund_code
            ),

            'completed' => sprintf(
                'Yêu cầu hoàn tiền %s đã hoàn tất.',
                $refund->refund_code
            ),

            'failed' => sprintf(
                'Yêu cầu hoàn tiền %s xử lý thất bại.',
                $refund->refund_code
            ),

            'cancelled' => sprintf(
                'Yêu cầu hoàn tiền %s đã bị hủy.',
                $refund->refund_code
            ),

            default => sprintf(
                'Trạng thái Refund %s đã được cập nhật.',
                $refund->refund_code
            ),
        };
    }

    /**
     * Tên trạng thái Refund.
     */
    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'completed' => 'Đã hoàn tiền',
            'failed' => 'Hoàn tiền thất bại',
            'cancelled' => 'Đã hủy',
            default => $status,
        };
    }

    /**
     * Sinh mã Refund duy nhất.
     */
    private function generateRefundCode(): string
    {
        do {
            $refundCode = 'REF'
                . now()->format('YmdHis')
                . strtoupper(Str::random(6));
        } while (
            Refund::query()
                ->where('refund_code', $refundCode)
                ->exists()
        );

        return $refundCode;
    }
}
