<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class RepairPaidOrderPayments extends Command
{
    protected $signature = 'project:repair-paid-payments
        {--dry-run : Chỉ hiển thị dữ liệu sẽ sửa, không cập nhật database}
        {--order=* : Chỉ sửa một hoặc nhiều mã đơn cụ thể}';

    protected $description = 'Đồng bộ payment paid cho các đơn lịch sử đã có payment_status=paid nhưng chưa có payment paid.';

    public function handle(): int
    {
        $orderCodes = array_values(array_filter(
            (array) $this->option('order')
        ));

        $query = Order::query()
            ->with([
                'payments' => fn ($paymentQuery) => $paymentQuery
                    ->orderByDesc('id'),
                'shipments' => fn ($shipmentQuery) => $shipmentQuery
                    ->orderByDesc('id'),
            ])
            ->where('payment_status', 'paid')
            ->whereDoesntHave(
                'payments',
                fn ($paymentQuery) => $paymentQuery->where('status', 'paid')
            )
            ->whereNull('deleted_at')
            ->orderBy('id');

        if ($orderCodes !== []) {
            $query->whereIn('order_code', $orderCodes);
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            $this->info('Không có đơn nào cần sửa payment.');

            return self::SUCCESS;
        }

        $rows = $orders->map(function (Order $order): array {
            $latestPayment = $order->payments->first();
            $latestShipment = $order->shipments->first();

            return [
                'order_code' => $order->order_code,
                'order_status' => $order->order_status,
                'payment_method' => $order->payment_method,
                'order_total' => number_format(
                    (float) $order->total_amount,
                    0,
                    ',',
                    '.'
                ) . 'đ',
                'payment_action' => $latestPayment
                    ? "Cập nhật payment #{$latestPayment->id}"
                    : 'Tạo payment mới',
                'current_payment_status' => $latestPayment?->status ?? 'không có',
                'shipment_status' => $latestShipment?->status ?? 'không có',
            ];
        })->all();

        $this->table(
            [
                'order_code',
                'order_status',
                'payment_method',
                'order_total',
                'payment_action',
                'current_payment_status',
                'shipment_status',
            ],
            $rows
        );

        $this->line('Tổng số đơn cần sửa: ' . $orders->count());

        if ($this->option('dry-run')) {
            $this->warn('Dry-run: chưa cập nhật database.');

            return self::SUCCESS;
        }

        $repaired = 0;

        foreach ($orders as $order) {
            try {
                DB::transaction(function () use ($order): void {
                    $lockedOrder = Order::query()
                        ->whereKey($order->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($lockedOrder->payment_status !== 'paid') {
                        throw new RuntimeException(
                            "Đơn {$lockedOrder->order_code} không còn payment_status=paid."
                        );
                    }

                    $alreadyPaid = Payment::query()
                        ->where('order_id', $lockedOrder->id)
                        ->where('status', 'paid')
                        ->lockForUpdate()
                        ->exists();

                    if ($alreadyPaid) {
                        return;
                    }

                    $payment = Payment::query()
                        ->where('order_id', $lockedOrder->id)
                        ->orderByDesc('id')
                        ->lockForUpdate()
                        ->first();

                    $paidAt = $lockedOrder->completed_at
                        ?? $lockedOrder->updated_at
                        ?? now();

                    if ($payment === null) {
                        $payment = Payment::query()->create([
                            'order_id' => $lockedOrder->id,
                            'payment_code' => $this->generatePaymentCode(
                                $lockedOrder
                            ),
                            'method' => $lockedOrder->payment_method ?: 'cod',
                            'status' => 'paid',
                            'amount' => $lockedOrder->total_amount,
                            'currency' => 'VND',
                            'provider_transaction_id' => null,
                            'bank_code' => null,
                            'card_type' => null,
                            'payment_url' => null,
                            'failure_reason' => null,
                            'paid_at' => $paidAt,
                            'expired_at' => null,
                            'cancelled_at' => null,
                        ]);
                    } else {
                        $payment->forceFill([
                            'method' => $payment->method
                                ?: ($lockedOrder->payment_method ?: 'cod'),
                            'status' => 'paid',
                            'amount' => $lockedOrder->total_amount,
                            'failure_reason' => null,
                            'paid_at' => $payment->paid_at ?? $paidAt,
                            'cancelled_at' => null,
                        ])->save();
                    }

                    $transactionExists = PaymentTransaction::query()
                        ->where('payment_id', $payment->id)
                        ->whereIn('status', [
                            'success',
                            'completed',
                            'paid',
                        ])
                        ->exists();

                    if (! $transactionExists) {
                        PaymentTransaction::query()->create([
                            'payment_id' => $payment->id,
                            'type' => 'payment',
                            'transaction_id' => null,
                            'amount' => $payment->amount,
                            'status' => 'paid',
                            'response_code' => 'HISTORY_REPAIR',
                            'message' => 'Đồng bộ giao dịch thanh toán lịch sử từ trạng thái đơn đã thanh toán.',
                            'request_data' => [
                                'source' => 'project:repair-paid-payments',
                                'order_code' => $lockedOrder->order_code,
                                'payment_method' => $payment->method,
                            ],
                            'response_data' => [
                                'repaired' => true,
                                'previous_order_payment_status' => 'paid',
                            ],
                            'ip_address' => null,
                            'processed_at' => $payment->paid_at ?? $paidAt,
                        ]);
                    }
                }, 3);

                $repaired++;
                $this->info("✓ Đã sửa {$order->order_code}");
            } catch (Throwable $exception) {
                report($exception);

                $this->error(
                    "✗ Không sửa được {$order->order_code}: {$exception->getMessage()}"
                );
            }
        }

        $this->newLine();
        $this->info("Đã xử lý {$repaired}/{$orders->count()} đơn.");

        return $repaired === $orders->count()
            ? self::SUCCESS
            : self::FAILURE;
    }

    private function generatePaymentCode(Order $order): string
    {
        $baseCode = 'PAY-' . $order->order_code;

        if (
            mb_strlen($baseCode) <= 50
            && ! Payment::query()
                ->where('payment_code', $baseCode)
                ->exists()
        ) {
            return $baseCode;
        }

        do {
            $code = 'PAY'
                . now()->format('YmdHis')
                . strtoupper(Str::random(6));
        } while (
            Payment::query()
                ->where('payment_code', $code)
                ->exists()
        );

        return mb_substr($code, 0, 50);
    }
}
