<?php

namespace App\Services\Admin;

use App\Models\Payment;
use App\Models\Refund;
use App\Models\ReturnRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReturnRequestStatusService
{
    public function __construct(
        private readonly ReturnInventoryService $returnInventoryService
    ) {
    }
    /**
     * Luồng chuyển trạng thái hợp lệ.
     */
    private const ALLOWED_TRANSITIONS = [
        'pending' => [
            'approved',
            'rejected',
            'cancelled',
        ],

        'approved' => [
            'waiting_for_return',
            'received',
            'cancelled',
        ],

        'waiting_for_return' => [
            'returning',
            'received',
            'cancelled',
        ],

        'returning' => [
            'received',
            'cancelled',
        ],

        'received' => [
            'inspecting',
            'processing',
            'cancelled',
        ],

        'inspecting' => [
            'processing',
            'cancelled',
        ],

        'processing' => [
            'completed',
            'cancelled',
        ],

        'completed' => [],
        'rejected' => [],
        'cancelled' => [],
    ];

    /**
     * Lấy các trạng thái tiếp theo có thể chuyển.
     */
    public function availableTransitions(
        ReturnRequest $returnRequest
    ): array {
        $statuses = self::ALLOWED_TRANSITIONS[
            $returnRequest->status
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
     * Cập nhật trạng thái yêu cầu trả hàng.
     */
    public function updateStatus(
        ReturnRequest $returnRequest,
        array $data,
        int $adminId
    ): ReturnRequest {
        return DB::transaction(function () use (
            $returnRequest,
            $data,
            $adminId
        ): ReturnRequest {
            $lockedReturnRequest = ReturnRequest::query()
                ->with([
                    'order',
                    'items',
                ])
                ->lockForUpdate()
                ->findOrFail($returnRequest->id);

            $oldStatus = (string) $lockedReturnRequest->status;
            $newStatus = (string) $data['status'];

            if ($oldStatus === $newStatus) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Yêu cầu trả hàng hiện đã ở trạng thái này.',
                ]);
            }

            $allowedStatuses = self::ALLOWED_TRANSITIONS[
                $oldStatus
            ] ?? [];

            if (! in_array($newStatus, $allowedStatuses, true)) {
                throw ValidationException::withMessages([
                    'status' => sprintf(
                        'Không thể chuyển yêu cầu từ “%s” sang “%s”.',
                        $this->statusLabel($oldStatus),
                        $this->statusLabel($newStatus)
                    ),
                ]);
            }

            $this->validateBusinessRules(
                returnRequest: $lockedReturnRequest,
                newStatus: $newStatus,
                data: $data
            );

            $updateData = [
                'status' => $newStatus,
                'processed_by' => $adminId,
            ];

            if (
                array_key_exists('approved_amount', $data)
                && $data['approved_amount'] !== null
            ) {
                $updateData['approved_amount'] =
                    $data['approved_amount'];
            }

            if (
                array_key_exists('return_shipping_fee', $data)
                && $data['return_shipping_fee'] !== null
            ) {
                $updateData['return_shipping_fee'] =
                    $data['return_shipping_fee'];
            }

            if (
                array_key_exists('shipping_fee_payer', $data)
                && ! blank($data['shipping_fee_payer'])
            ) {
                $updateData['shipping_fee_payer'] =
                    $data['shipping_fee_payer'];
            }

            if (! blank($data['note'] ?? null)) {
                $updateData['admin_note'] =
                    $this->appendAdminNote(
                        currentNote: $lockedReturnRequest->admin_note,
                        newNote: $data['note'],
                        adminId: $adminId
                    );
            }

            switch ($newStatus) {
                case 'approved':
                    $updateData['approved_at'] = now();
                    $updateData['rejection_reason'] = null;
                    break;

                case 'received':
                    $updateData['received_at'] = now();
                    break;

                case 'returning':
                case 'inspecting':
                case 'processing':
                    break;

                case 'completed':
                    $updateData['completed_at'] = now();
                    break;

                case 'rejected':
                    $updateData['rejected_at'] = now();
                    $updateData['rejection_reason'] =
                        $data['rejection_reason'];
                    break;

                case 'cancelled':
                    $updateData['cancelled_at'] = now();
                    break;
            }

            $lockedReturnRequest->update($updateData);

            if ($newStatus === 'inspecting') {
                $this->updateReturnItems(
                    returnRequest: $lockedReturnRequest,
                    data: $data
                );
            }

            if ($newStatus === 'processing') {
                $this->createRefundIfNeeded(
                    returnRequest: $lockedReturnRequest,
                    adminId: $adminId
                );
            }

            $lockedReturnRequest->statusHistories()->create([
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'note' => $data['note']
                    ?? $this->defaultHistoryNote(
                        $lockedReturnRequest,
                        $newStatus
                    ),
                'created_by' => $adminId,
                'source' => 'admin',
            ]);

            if ($newStatus === 'completed') {
                $this->returnInventoryService->processCompletedReturn(
                    $lockedReturnRequest->fresh(),
                    $adminId
                );
            }

            return $lockedReturnRequest->fresh([
                'order',
                'user',
                'processor',
                'items.orderItem',
                'images',
                'statusHistories.creator',
                'refunds',
            ]);
        }, 3);
    }

    /**
     * Tạo Refund liên kết khi yêu cầu hoàn tất.
     */
    private function createRefundIfNeeded(
        ReturnRequest $returnRequest,
        int $adminId
    ): void {
        if (! in_array(
            $returnRequest->request_type,
            [
                'refund',
                'return_refund',
            ],
            true
        )) {
            return;
        }

        $refundExists = Refund::query()
            ->where('return_request_id', $returnRequest->id)
            ->exists();

        if ($refundExists) {
            return;
        }

        $refundAmount = (float) (
            $returnRequest->approved_amount
            ?? $returnRequest->requested_amount
        );

        if ($refundAmount <= 0) {
            throw ValidationException::withMessages([
                'approved_amount' =>
                    'Số tiền được duyệt phải lớn hơn 0 để tạo hoàn tiền.',
            ]);
        }

        $payment = Payment::query()
            ->where('order_id', $returnRequest->order_id)
            ->whereIn('status', [
                'paid',
                'partially_refunded',
            ])
            ->latest('id')
            ->lockForUpdate()
            ->first();

        if (! $payment) {
            throw ValidationException::withMessages([
                'status' =>
                    'Không tìm thấy Payment hợp lệ để tạo hoàn tiền.',
            ]);
        }

        $completedRefundAmount = (float) $payment
            ->refunds()
            ->where('status', 'completed')
            ->sum('amount');

        $activeRefundAmount = (float) $payment
            ->refunds()
            ->whereIn('status', [
                'pending',
                'processing',
            ])
            ->sum('amount');

        $remainingAmount = max(
            0,
            (float) $payment->amount
                - $completedRefundAmount
                - $activeRefundAmount
        );

        if ($remainingAmount <= 0) {
            throw ValidationException::withMessages([
                'approved_amount' =>
                    'Payment này không còn số tiền có thể hoàn.',
            ]);
        }

        if ($refundAmount > $remainingAmount) {
            throw ValidationException::withMessages([
                'approved_amount' => sprintf(
                    'Số tiền hoàn không được vượt quá %s₫ còn lại của Payment.',
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
            'return_request_id' => $returnRequest->id,
            'order_id' => $returnRequest->order_id,
            'payment_id' => $payment->id,
            'amount' => $refundAmount,
            'method' => 'original_payment',
            'status' => 'pending',
            'provider_transaction_id' => null,
            'bank_name' => null,
            'bank_account_number' => null,
            'bank_account_name' => null,
            'reason' => sprintf(
                'Hoàn tiền theo yêu cầu trả hàng %s.',
                $returnRequest->return_code
            ),
            'admin_note' => sprintf(
                '[%s] Admin #%d: Tự động tạo từ Return Request đã hoàn tất.',
                now()->format('d/m/Y H:i'),
                $adminId
            ),
            'failure_reason' => null,
            'processed_by' => null,
            'processed_at' => null,
            'completed_at' => null,
            'failed_at' => null,
            'cancelled_at' => null,
        ]);

        $payment->transactions()->create([
            'type' => 'refund',
            'transaction_id' => $refund->refund_code,
            'amount' => $refund->amount,
            'status' => 'pending',
            'response_code' => null,
            'message' => sprintf(
                'Tự động tạo yêu cầu hoàn tiền từ Return Request %s.',
                $returnRequest->return_code
            ),
            'request_data' => [
                'return_request_id' => $returnRequest->id,
                'return_code' => $returnRequest->return_code,
                'refund_code' => $refund->refund_code,
                'admin_id' => $adminId,
            ],
            'response_data' => null,
            'ip_address' => request()->ip(),
            'processed_at' => now(),
        ]);
    }

    /**
     * Kiểm tra quy tắc nghiệp vụ.
     */
    private function validateBusinessRules(
        ReturnRequest $returnRequest,
        string $newStatus,
        array $data
    ): void {
        if ($newStatus === 'approved') {
            $approvedAmount = $data['approved_amount'] ?? null;

            if ($approvedAmount === null) {
                throw ValidationException::withMessages([
                    'approved_amount' =>
                        'Vui lòng nhập số tiền được chấp thuận.',
                ]);
            }

            if ((float) $approvedAmount < 0) {
                throw ValidationException::withMessages([
                    'approved_amount' =>
                        'Số tiền được duyệt không được nhỏ hơn 0.',
                ]);
            }

            if (
                (float) $approvedAmount >
                (float) $returnRequest->requested_amount
            ) {
                throw ValidationException::withMessages([
                    'approved_amount' =>
                        'Số tiền được duyệt không được vượt quá số tiền khách hàng yêu cầu.',
                ]);
            }
        }

        if (
            $newStatus === 'rejected'
            && blank($data['rejection_reason'] ?? null)
        ) {
            throw ValidationException::withMessages([
                'rejection_reason' =>
                    'Vui lòng nhập lý do từ chối yêu cầu.',
            ]);
        }

        if (
            in_array($newStatus, ['processing', 'completed'], true)
            && ! $returnRequest->received_at
            && $returnRequest->request_type !== 'refund'
        ) {
            throw ValidationException::withMessages([
                'status' =>
                    'Phải xác nhận đã nhận hàng trả trước khi tiếp tục xử lý yêu cầu.',
            ]);
        }

        if (
            $newStatus === 'completed'
            && in_array(
                $returnRequest->request_type,
                ['refund', 'return_refund'],
                true
            )
        ) {
            $requiredRefundAmount = (float) (
                $returnRequest->approved_amount
                ?: $returnRequest->requested_amount
            );

            $completedRefundAmount = (float) Refund::query()
                ->where('return_request_id', $returnRequest->id)
                ->where('status', 'completed')
                ->sum('amount');

            if (
                $requiredRefundAmount > 0
                && $completedRefundAmount + 0.01 < $requiredRefundAmount
            ) {
                throw ValidationException::withMessages([
                    'status' => sprintf(
                        'Chỉ được hoàn tất sau khi đã hoàn đủ %s₫. Hiện mới hoàn %s₫.',
                        number_format($requiredRefundAmount, 0, ',', '.'),
                        number_format($completedRefundAmount, 0, ',', '.')
                    ),
                ]);
            }
        }
    }

    /**
     * Cập nhật kết quả kiểm tra sản phẩm khi nhận hàng.
     */
    private function updateReturnItems(
        ReturnRequest $returnRequest,
        array $data
    ): void {
        $itemsData = $data['items'] ?? [];

        foreach ($itemsData as $itemId => $itemData) {
            $returnItem = $returnRequest->items
                ->firstWhere('id', (int) $itemId);

            if (! $returnItem) {
                continue;
            }

            $returnItem->update([
                'product_condition' =>
                    $itemData['product_condition']
                    ?? $returnItem->product_condition,

                'inspection_result' =>
                    $itemData['inspection_result']
                    ?? $returnItem->inspection_result,

                'inspection_note' =>
                    $itemData['inspection_note']
                    ?? $returnItem->inspection_note,

                'inventory_action' =>
                    $itemData['inventory_action']
                    ?? $returnItem->inventory_action,

                'approved_refund_amount' =>
                    $itemData['approved_refund_amount']
                    ?? $returnItem->approved_refund_amount,
            ]);
        }
    }

    /**
     * Nối ghi chú nội bộ.
     */
    private function appendAdminNote(
        ?string $currentNote,
        string $newNote,
        int $adminId
    ): string {
        $formattedNote = sprintf(
            '[%s] Admin #%d: %s',
            now()->format('d/m/Y H:i'),
            $adminId,
            trim($newNote)
        );

        if (blank($currentNote)) {
            return $formattedNote;
        }

        return $currentNote
            . PHP_EOL
            . $formattedNote;
    }

    /**
     * Ghi chú mặc định trong lịch sử.
     */
    private function defaultHistoryNote(
        ReturnRequest $returnRequest,
        string $status
    ): string {
        return match ($status) {
            'approved' => sprintf(
                'Admin đã chấp thuận yêu cầu %s.',
                $returnRequest->return_code
            ),

            'waiting_for_return' =>
                'Đang chờ khách hàng gửi sản phẩm về cửa hàng.',

            'returning' =>
                'Sản phẩm đang được khách hàng gửi trả.',

            'received' =>
                'Cửa hàng đã nhận được sản phẩm khách gửi trả.',

            'inspecting' =>
                'Cửa hàng đang kiểm tra tình trạng sản phẩm trả về.',

            'processing' =>
                'Yêu cầu trả hàng đang được xử lý hoàn tiền hoặc hậu kiểm.',

            'completed' =>
                'Yêu cầu trả hàng đã được xử lý hoàn tất.',

            'rejected' =>
                'Admin đã từ chối yêu cầu trả hàng.',

            'cancelled' =>
                'Yêu cầu trả hàng đã bị hủy.',

            default =>
                'Trạng thái yêu cầu trả hàng đã được cập nhật.',
        };
    }

    /**
     * Tên trạng thái tiếng Việt.
     */
    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Chờ xử lý',
            'approved' => 'Đã chấp thuận',
            'waiting_for_return' => 'Chờ khách gửi hàng',
            'returning' => 'Hàng đang gửi trả',
            'received' => 'Đã nhận hàng trả',
            'inspecting' => 'Đang kiểm tra',
            'processing' => 'Đang xử lý',
            'completed' => 'Đã hoàn tất',
            'rejected' => 'Đã từ chối',
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
