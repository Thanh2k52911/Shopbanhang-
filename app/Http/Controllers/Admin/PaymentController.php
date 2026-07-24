<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateRefundRequest;
use App\Http\Requests\Admin\UpdatePaymentStatusRequest;
use App\Http\Requests\Admin\UpdateRefundStatusRequest;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\Admin\PaymentStatusService;
use App\Services\Admin\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Danh sách thanh toán.
     */
    public function index(Request $request): View
    {
        $query = Payment::query()
            ->with([
                'order:id,order_code,customer_name,customer_phone',
            ])
            ->latest('id');

        /*
        |--------------------------------------------------------------------------
        | Tìm kiếm
        |--------------------------------------------------------------------------
        */

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));

            $query->where(function ($builder) use ($keyword): void {
                $builder
                    ->where(
                        'payment_code',
                        'like',
                        '%' . $keyword . '%'
                    )
                    ->orWhere(
                        'provider_transaction_id',
                        'like',
                        '%' . $keyword . '%'
                    )
                    ->orWhereHas(
                        'order',
                        function ($orderQuery) use ($keyword): void {
                            $orderQuery
                                ->where(
                                    'order_code',
                                    'like',
                                    '%' . $keyword . '%'
                                )
                                ->orWhere(
                                    'customer_name',
                                    'like',
                                    '%' . $keyword . '%'
                                )
                                ->orWhere(
                                    'customer_phone',
                                    'like',
                                    '%' . $keyword . '%'
                                );
                        }
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Lọc trạng thái
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->input('status')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lọc phương thức
        |--------------------------------------------------------------------------
        */

        if ($request->filled('method')) {
            $query->where(
                'method',
                $request->input('method')
            );
        }

        $payments = $query
            ->paginate(20)
            ->withQueryString();

        $statistics = [
            'total' => Payment::query()->count(),

            'pending' => Payment::query()
                ->where('status', 'pending')
                ->count(),

            'processing' => Payment::query()
                ->where('status', 'processing')
                ->count(),

            'paid' => Payment::query()
                ->where('status', 'paid')
                ->count(),

            'failed' => Payment::query()
                ->where('status', 'failed')
                ->count(),

            'cancelled' => Payment::query()
                ->where('status', 'cancelled')
                ->count(),
        ];

        return view('admin.payments.index', [
            'payments' => $payments,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Chi tiết thanh toán.
     */
    public function show(
        Payment $payment,
        PaymentStatusService $paymentStatusService,
        RefundService $refundService
    ): View {
        $payment->load([
            'order.user',
            'order.shippingAddress',
            'transactions' => fn ($query) => $query->latest('id'),
            'refunds' => fn ($query) => $query->latest('id'),
            'refunds.processor',
        ]);

        $nextPaymentStatuses = $paymentStatusService
            ->availableTransitions($payment);

        $remainingRefundAmount = $payment->canBeRefunded()
            ? $refundService->remainingRefundableAmount($payment)
            : 0;

        $refundMethods = [
            'original_payment' => 'Hoàn về phương thức thanh toán',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'cash' => 'Tiền mặt',
            'store_credit' => 'Ví cửa hàng',
            'coupon' => 'Mã giảm giá',
        ];

        /*
        |--------------------------------------------------------------------------
        | Trạng thái tiếp theo của từng Refund
        |--------------------------------------------------------------------------
        */

        $refundTransitions = $payment
            ->refunds
            ->mapWithKeys(
                fn (Refund $refund): array => [
                    $refund->id =>
                        $refundService->availableTransitions($refund),
                ]
            )
            ->all();

        return view('admin.payments.show', [
            'payment' => $payment,
            'nextPaymentStatuses' => $nextPaymentStatuses,
            'remainingRefundAmount' => $remainingRefundAmount,
            'refundMethods' => $refundMethods,
            'refundTransitions' => $refundTransitions,
        ]);
    }

    /**
     * Tạo yêu cầu hoàn tiền.
     */
    public function storeRefund(
        CreateRefundRequest $request,
        Payment $payment,
        RefundService $refundService
    ): RedirectResponse {
        $refund = $refundService->create(
            payment: $payment,
            data: $request->validated(),
            adminId: $request->user()->id
        );

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with(
                'success',
                sprintf(
                    'Đã tạo yêu cầu hoàn tiền %s.',
                    $refund->refund_code
                )
            );
    }

    /**
     * Cập nhật trạng thái Refund.
     */
    public function updateRefundStatus(
        UpdateRefundStatusRequest $request,
        Payment $payment,
        Refund $refund,
        RefundService $refundService
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Kiểm tra Refund thuộc đúng Payment
        |--------------------------------------------------------------------------
        */

        if ((int) $refund->payment_id !== (int) $payment->id) {
            abort(404);
        }

        $updatedRefund = $refundService->updateStatus(
            refund: $refund,
            data: $request->validated(),
            adminId: $request->user()->id
        );

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with(
                'success',
                sprintf(
                    'Đã cập nhật hoàn tiền %s sang trạng thái “%s”.',
                    $updatedRefund->refund_code,
                    $this->refundStatusLabel(
                        $updatedRefund->status
                    )
                )
            );
    }

    /**
     * Cập nhật trạng thái thanh toán.
     */
    public function updateStatus(
        UpdatePaymentStatusRequest $request,
        Payment $payment,
        PaymentStatusService $paymentStatusService
    ): RedirectResponse {
        $updatedPayment = $paymentStatusService->updateStatus(
            payment: $payment,
            data: $request->validated(),
            adminId: $request->user()->id
        );

        return redirect()
            ->route('admin.payments.show', $updatedPayment)
            ->with(
                'success',
                sprintf(
                    'Đã cập nhật thanh toán %s sang trạng thái “%s”.',
                    $updatedPayment->payment_code,
                    $this->paymentStatusLabel(
                        $updatedPayment->status
                    )
                )
            );
    }

    /**
     * Tên trạng thái Payment.
     */
    private function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Chờ thanh toán',
            'processing' => 'Đang xử lý',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền',
            'partially_refunded' => 'Hoàn tiền một phần',
            default => $status,
        };
    }

    /**
     * Tên trạng thái Refund.
     */
    private function refundStatusLabel(string $status): string
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
}
