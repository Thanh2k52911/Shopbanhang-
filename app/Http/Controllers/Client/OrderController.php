<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use App\Services\OrderCancellationService;
class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->toString();

        $allowedStatuses = [
            'pending',
            'confirmed',
            'processing',
            'packed',
            'shipping',
            'completed',
            'cancelled',
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $query = DB::table('orders as o')
            ->where('o.user_id', auth()->id())
            ->whereNull('o.deleted_at')
            ->select([
                'o.id',
                'o.order_code',
                'o.order_status',
                'o.payment_status',
                'o.shipping_status',
                'o.payment_method',
                'o.total_amount',
                'o.total_quantity',
                'o.created_at',
            ])
            ->selectSub(function ($subQuery): void {
                $subQuery
                    ->from('order_items')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn(
                        'order_items.order_id',
                        'o.id'
                    );
            }, 'item_count')
            ->selectSub(function ($subQuery): void {
                $subQuery
                    ->from('order_items')
                    ->select('image_path')
                    ->whereColumn(
                        'order_items.order_id',
                        'o.id'
                    )
                    ->whereNotNull('image_path')
                    ->orderBy('id')
                    ->limit(1);
            }, 'first_image');

        if ($status !== '') {
            $query->where('o.order_status', $status);
        }

        $orders = $query
            ->orderByDesc('o.created_at')
            ->paginate(10)
            ->withQueryString();

        $statusCounts = DB::table('orders')
            ->where('user_id', auth()->id())
            ->whereNull('deleted_at')
            ->select(
                'order_status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        return view('client.account.orders.index', compact(
            'orders',
            'status',
            'statusCounts'
        ));
    }

    /**
 * Hiển thị chi tiết đơn hàng của khách hàng.
 */
public function show(string $orderCode): View
{
    /*
    |--------------------------------------------------------------------------
    | Đơn hàng
    |--------------------------------------------------------------------------
    */

    $order = DB::table('orders')
        ->where('order_code', $orderCode)
        ->where('user_id', auth()->id())
        ->whereNull('deleted_at')
        ->first();

    abort_if(! $order, 404);

    /*
    |--------------------------------------------------------------------------
    | Sản phẩm trong đơn
    |--------------------------------------------------------------------------
    */

    $items = DB::table('order_items')
        ->where('order_id', $order->id)
        ->orderBy('id')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Địa chỉ giao hàng
    |--------------------------------------------------------------------------
    */

    $address = DB::table('order_addresses')
        ->where('order_id', $order->id)
        ->where('type', 'shipping')
        ->first();

    /*
    |--------------------------------------------------------------------------
    | Thanh toán mới nhất
    |--------------------------------------------------------------------------
    */

    $payment = DB::table('payments')
        ->where('order_id', $order->id)
        ->latest('id')
        ->first();

    /*
    |--------------------------------------------------------------------------
    | Lịch sử trạng thái đơn hàng
    |--------------------------------------------------------------------------
    */

    $histories = DB::table('order_status_histories')
        ->where('order_id', $order->id)
        ->orderBy('occurred_at')
        ->orderBy('id')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Yêu cầu trả hàng, đổi hàng và hoàn tiền
    |--------------------------------------------------------------------------
    */

    $returnRequests = DB::table('return_requests')
        ->where('order_id', $order->id)
        ->where('user_id', auth()->id())
        ->orderByDesc('id')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Yêu cầu đang được xử lý
    |--------------------------------------------------------------------------
    */

    $activeReturnRequest = $returnRequests->first(
        function ($returnRequest): bool {
            return in_array(
                $returnRequest->status,
                [
                    'pending',
                    'approved',
                    'waiting_for_return',
                    'returning',
                    'received',
                    'inspecting',
                    'processing',
                ],
                true
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Có thể tạo yêu cầu mới hay không
    |--------------------------------------------------------------------------
    |
    | Controller tạo yêu cầu vẫn kiểm tra lại quyền sở hữu, trạng thái đơn
    | và số lượng từng sản phẩm. Biến này chỉ dùng để điều khiển giao diện.
    |
    */

    $canCreateReturnRequest =
        $order->order_status === 'completed'
        && $order->shipping_status === 'delivered'
        && $items->isNotEmpty();

    $returnRequestStatuses = [
        'pending' => 'Chờ xử lý',
        'approved' => 'Đã chấp thuận',
        'rejected' => 'Đã từ chối',
        'waiting_for_return' => 'Chờ gửi hàng trả',
        'returning' => 'Đang gửi hàng trả',
        'received' => 'Cửa hàng đã nhận hàng',
        'inspecting' => 'Đang kiểm tra sản phẩm',
        'processing' => 'Đang xử lý',
        'completed' => 'Đã hoàn tất',
        'cancelled' => 'Đã hủy',
    ];

    $returnRequestTypes = [
        'return' => 'Trả hàng',
        'exchange' => 'Đổi sản phẩm',
        'refund' => 'Hoàn tiền',
    ];

    return view('client.account.orders.show', [
        'order' => $order,
        'items' => $items,
        'address' => $address,
        'payment' => $payment,
        'histories' => $histories,

        'returnRequests' => $returnRequests,

        'activeReturnRequest' =>
            $activeReturnRequest,

        'canCreateReturnRequest' =>
            $canCreateReturnRequest,

        'returnRequestStatuses' =>
            $returnRequestStatuses,

        'returnRequestTypes' =>
            $returnRequestTypes,
    ]);
}


public function cancel(
    Request $request,
    string $orderCode,
    OrderCancellationService $cancellationService
): RedirectResponse {
    $validated = $request->validate([
        'cancel_reason' => [
            'required',
            'string',
            'min:5',
            'max:500',
        ],
    ], [
        'cancel_reason.required' =>
            'Vui lòng nhập lý do hủy đơn.',
        'cancel_reason.min' =>
            'Lý do hủy phải có ít nhất 5 ký tự.',
        'cancel_reason.max' =>
            'Lý do hủy không được vượt quá 500 ký tự.',
    ]);

    $cancellationService->cancelByCustomer(
        $orderCode,
        (int) auth()->id(),
        $validated['cancel_reason']
    );

    return redirect()
        ->route('account.orders.show', $orderCode)
        ->with(
            'order_success',
            'Đơn hàng đã được hủy thành công.'
        );
}
}
