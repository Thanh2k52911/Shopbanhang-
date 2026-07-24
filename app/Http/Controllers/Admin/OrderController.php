<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Services\Admin\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\CancelOrderRequest;
use App\Services\OrderCancellationService;

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng trong khu vực Admin.
     */
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with([
    'user:id,name,email',
    'warehouse:id,name',

    'latestShipment' => function ($query): void {
        $query->with('shippingMethod:id,name');
    },
])
            ->when(
                $request->filled('keyword'),
                function ($query) use ($request): void {
                    $keyword = trim((string) $request->input('keyword'));

                    $query->where(function ($subQuery) use ($keyword): void {
                        $subQuery
                            ->where('order_code', 'like', "%{$keyword}%")
                            ->orWhere('customer_name', 'like', "%{$keyword}%")
                            ->orWhere('customer_email', 'like', "%{$keyword}%")
                            ->orWhere('customer_phone', 'like', "%{$keyword}%")
                            ->orWhereHas(
                                'user',
                                function ($userQuery) use ($keyword): void {
                                    $userQuery
                                        ->where('name', 'like', "%{$keyword}%")
                                        ->orWhere('email', 'like', "%{$keyword}%");
                                }
                            );
                    });
                }
            )
            ->when(
                $request->filled('order_status'),
                function ($query) use ($request): void {
                    $query->where(
                        'order_status',
                        $request->input('order_status')
                    );
                }
            )
            ->when(
                $request->filled('payment_status'),
                function ($query) use ($request): void {
                    $query->where(
                        'payment_status',
                        $request->input('payment_status')
                    );
                }
            )
            ->when(
                $request->filled('shipping_status'),
                function ($query) use ($request): void {
                    $query->where(
                        'shipping_status',
                        $request->input('shipping_status')
                    );
                }
            )
            ->when(
                $request->filled('payment_method'),
                function ($query) use ($request): void {
                    $query->where(
                        'payment_method',
                        $request->input('payment_method')
                    );
                }
            )
            ->when(
                $request->filled('date_from'),
                function ($query) use ($request): void {
                    $query->whereDate(
                        'created_at',
                        '>=',
                        $request->input('date_from')
                    );
                }
            )
            ->when(
                $request->filled('date_to'),
                function ($query) use ($request): void {
                    $query->whereDate(
                        'created_at',
                        '<=',
                        $request->input('date_to')
                    );
                }
            )
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $statistics = [
            'all' => Order::query()->count(),

            'pending' => Order::query()
                ->where('order_status', 'pending')
                ->count(),

            'confirmed' => Order::query()
                ->where('order_status', 'confirmed')
                ->count(),

            'processing' => Order::query()
                ->where('order_status', 'processing')
                ->count(),

            'packed' => Order::query()
                ->where('order_status', 'packed')
                ->count(),

            'shipping' => Order::query()
                ->where('order_status', 'shipping')
                ->count(),

            'completed' => Order::query()
                ->where('order_status', 'completed')
                ->count(),

            'cancelled' => Order::query()
                ->where('order_status', 'cancelled')
                ->count(),

            'returned' => Order::query()
                ->where('order_status', 'returned')
                ->count(),
        ];

        $orderStatuses = [
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'processing' => 'Đang xử lý',
            'packed' => 'Đã đóng gói',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'returned' => 'Đã trả hàng',
        ];

        $paymentStatuses = [
            'unpaid' => 'Chưa thanh toán',
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại',
            'cancelled' => 'Đã hủy thanh toán',
            'refunded' => 'Đã hoàn tiền',
            'partially_refunded' => 'Hoàn tiền một phần',
        ];

        $shippingStatuses = [
    'pending' => 'Chờ xử lý',
    'ready_to_ship' => 'Sẵn sàng giao',
    'picked_up' => 'Đã lấy hàng',
    'in_transit' => 'Đang vận chuyển',
    'delivered' => 'Đã giao hàng',
    'failed' => 'Giao hàng thất bại',
    'returned' => 'Đã hoàn hàng',

    // Dữ liệu phát sinh từ chức năng hủy đơn phía Client
    'cancelled' => 'Đã hủy',
];

        $paymentMethods = [
            'cod' => 'Thanh toán khi nhận hàng',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'vnpay' => 'VNPay',
            'momo' => 'MoMo',
            'zalopay' => 'ZaloPay',
        ];

        return view('admin.orders.index', [
            'orders' => $orders,
            'statistics' => $statistics,
            'orderStatuses' => $orderStatuses,
            'paymentStatuses' => $paymentStatuses,
            'shippingStatuses' => $shippingStatuses,
            'paymentMethods' => $paymentMethods,
        ]);
    }
    /**
 * Hiển thị chi tiết một đơn hàng.
 */
public function show(
    Order $order,
    OrderStatusService $orderStatusService
): View
{
    $order->load([
        /*
        |--------------------------------------------------------------------------
        | Khách hàng và nhân viên xử lý
        |--------------------------------------------------------------------------
        */

        'user:id,name,email,avatar',

        'confirmer:id,name,email',

        'canceller:id,name,email',


        /*
        |--------------------------------------------------------------------------
        | Kho và coupon
        |--------------------------------------------------------------------------
        */

        'warehouse:id,name,address,status',

        'coupon:id,code,name,discount_type,discount_value',

        /*
        |--------------------------------------------------------------------------
        | Địa chỉ đơn hàng
        |--------------------------------------------------------------------------
        */

        'shippingAddress',

        'billingAddress',

        /*
        |--------------------------------------------------------------------------
        | Sản phẩm trong đơn
        |--------------------------------------------------------------------------
        */

        'items' => function ($query): void {
            $query
                ->with([
                    'product:id,name,slug',
                    'variant:id,product_id,name',
                    'sku:id,product_id,variant_id,sku_code,barcode',
                ])
                ->orderBy('id');
        },

        /*
        |--------------------------------------------------------------------------
        | Thanh toán
        |--------------------------------------------------------------------------
        */

        'payments' => function ($query): void {
            $query
                ->with([
                    'transactions',
                    'refunds',
                ])
                ->latest('id');
        },

        /*
        |--------------------------------------------------------------------------
        | Vận chuyển
        |--------------------------------------------------------------------------
        */

        'shipments' => function ($query): void {
            $query
                ->with([
                    'shippingMethod',
                    'warehouse',
                    'items',
                    'statusHistories',
                ])
                ->latest('id');
        },

        /*
        |--------------------------------------------------------------------------
        | Lịch sử trạng thái đơn
        |--------------------------------------------------------------------------
        */

        'statusHistories' => function ($query): void {
            $query
                ->with([
                    'creator:id,name,email,avatar',
                ])
                ->latest('id');
        },

        /*
        |--------------------------------------------------------------------------
        | Trả hàng và hoàn tiền
        |--------------------------------------------------------------------------
        */

        'returnRequests' => function ($query): void {
            $query->latest('id');
        },

        'refunds' => function ($query): void {
            $query->latest('id');
        },
    ]);
    /*
|--------------------------------------------------------------------------
| Trạng thái xử lý tiếp theo
|--------------------------------------------------------------------------
*/

$nextOrderStatuses = $orderStatusService->availableTransitions($order);

    $orderStatuses = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'processing' => 'Đang xử lý',
        'packed' => 'Đã đóng gói',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
        'returned' => 'Đã trả hàng',
    ];

    $paymentStatuses = [
        'unpaid' => 'Chưa thanh toán',
        'pending' => 'Chờ thanh toán',
        'paid' => 'Đã thanh toán',
        'failed' => 'Thanh toán thất bại',
        'cancelled' => 'Đã hủy thanh toán',
        'refunded' => 'Đã hoàn tiền',
        'partially_refunded' => 'Hoàn tiền một phần',
    ];

    $shippingStatuses = [
        'pending' => 'Chờ xử lý',
        'ready_to_ship' => 'Sẵn sàng giao',
        'picked_up' => 'Đã lấy hàng',
        'in_transit' => 'Đang vận chuyển',
        'delivered' => 'Đã giao hàng',
        'failed' => 'Giao hàng thất bại',
        'returned' => 'Đã hoàn hàng',
        'cancelled' => 'Đã hủy',
    ];

    $paymentMethods = [
        'cod' => 'Thanh toán khi nhận hàng',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'vnpay' => 'VNPay',
        'momo' => 'MoMo',
        'zalopay' => 'ZaloPay',
    ];

    return view('admin.orders.show', [
    'order' => $order,
    'orderStatuses' => $orderStatuses,
    'paymentStatuses' => $paymentStatuses,
    'shippingStatuses' => $shippingStatuses,
    'paymentMethods' => $paymentMethods,
    'nextOrderStatuses' => $nextOrderStatuses,
]);
}
/**
 * Cập nhật trạng thái xử lý của đơn hàng.
 */
public function updateStatus(
    UpdateOrderStatusRequest $request,
    Order $order,
    OrderStatusService $orderStatusService
): RedirectResponse {
    $validated = $request->validated();

    $updatedOrder = $orderStatusService->updateStatus(
        order: $order,
        newStatus: $validated['order_status'],
        note: $validated['note'] ?? null,
        adminId: $request->user()->id
    );

    return redirect()
        ->route('admin.orders.show', $updatedOrder)
        ->with(
            'success',
            sprintf(
                'Đã cập nhật đơn hàng %s sang trạng thái “%s”.',
                $updatedOrder->order_code,
                $this->orderStatusLabel(
                    $updatedOrder->order_status
                )
            )
        );
}
/**
 * Hủy đơn hàng từ khu vực Admin.
 */
public function cancel(
    CancelOrderRequest $request,
    Order $order,
    OrderCancellationService $orderCancellationService
): RedirectResponse {
    $validated = $request->validated();

    $cancelledOrder = $orderCancellationService->cancelByAdmin(
        order: $order,
        cancelReason: $validated['cancel_reason'],
        adminNote: $validated['note'] ?? null,
        adminId: $request->user()->id
    );

    return redirect()
        ->route('admin.orders.show', $cancelledOrder)
        ->with(
            'success',
            sprintf(
                'Đã hủy đơn hàng %s thành công.',
                $cancelledOrder->order_code
            )
        );
}
/**
 * Trả về tên trạng thái đơn hàng bằng tiếng Việt.
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
