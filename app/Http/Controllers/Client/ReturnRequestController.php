<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreReturnRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use App\Models\ReturnRequestItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;
use App\Services\Admin\NotificationService;

class ReturnRequestController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {
    }
    /**
     * Những trạng thái Return Request đang giữ số lượng sản phẩm.
     */
    private const ACTIVE_STATUSES = [
        'pending',
        'approved',
        'waiting_for_return',
        'returning',
        'received',
        'inspecting',
        'processing',
    ];

    /**
     * Danh sách yêu cầu đổi trả của khách hàng.
     */
    public function index(): View
    {
        $returnRequests = ReturnRequest::query()
            ->where('user_id', auth()->id())
            ->with([
                'order:id,order_code,order_status,payment_status,total_amount',
            ])
            ->withCount([
                'items',
                'images',
            ])
            ->latest('id')
            ->paginate(10);

        return view('client.account.return-requests.index', [
            'returnRequests' => $returnRequests,
            'statuses' => $this->statuses(),
            'requestTypes' => $this->requestTypes(),
        ]);
    }

    /**
     * Hiển thị form tạo yêu cầu từ một đơn hàng.
     */
    public function create(string $orderCode): View
    {
        $order = $this->findOwnedOrder($orderCode);

        $this->ensureOrderCanCreateReturnRequest($order);

        $order->load([
            'items',
            'returnRequests.items',
        ]);

        $items = $order->items
            ->map(function (OrderItem $orderItem): OrderItem {
                $activeRequestedQuantity =
                    $this->activeRequestedQuantity($orderItem);

                $returnAvailableQuantity = max(
                    0,
                    (int) $orderItem->quantity
                        - (int) $orderItem->returned_quantity
                        - $activeRequestedQuantity
                );

                $refundAvailableQuantity = max(
                    0,
                    (int) $orderItem->quantity
                        - (int) $orderItem->refunded_quantity
                        - $activeRequestedQuantity
                );

                $orderItem->setAttribute(
                    'active_return_quantity',
                    $activeRequestedQuantity
                );

                /*
                 * Trang tạo chưa biết khách sẽ chọn loại yêu cầu nào,
                 * nên giữ sản phẩm nếu còn số lượng cho ít nhất một loại.
                 */
                $orderItem->setAttribute(
                    'available_request_quantity',
                    max(
                        $returnAvailableQuantity,
                        $refundAvailableQuantity
                    )
                );

                return $orderItem;
            })
            ->filter(
                fn (OrderItem $orderItem): bool =>
                    (int) $orderItem->available_request_quantity > 0
            )
            ->values();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'order' =>
                    'Các sản phẩm trong đơn hàng này không còn số lượng có thể yêu cầu xử lý.',
            ]);
        }

        return view('client.account.return-requests.create', [
            'order' => $order,
            'items' => $items,
            'requestTypes' => $this->requestTypes(),
            'productConditions' => $this->productConditions(),
        ]);
    }

    /**
     * Lưu yêu cầu trả hàng/hoàn tiền.
     */
    public function store(
        StoreReturnRequest $request,
        string $orderCode
    ): RedirectResponse {
        $storedImagePaths = [];

        try {
            $returnRequest = DB::transaction(function () use (
                $request,
                $orderCode,
                &$storedImagePaths
            ): ReturnRequest {
                $order = Order::query()
                    ->where('order_code', $orderCode)
                    ->where('user_id', $request->user()->id)
                    ->whereNull('deleted_at')
                    ->with('items')
                    ->lockForUpdate()
                    ->first();

                if (! $order) {
                    throw ValidationException::withMessages([
                        'order' =>
                            'Không tìm thấy đơn hàng của bạn.',
                    ]);
                }

                $this->ensureOrderCanCreateReturnRequest($order);

                $data = $request->validated();

                $selectedItems = $this->selectedItems(
                    $data['items'] ?? []
                );

                if ($selectedItems->isEmpty()) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Vui lòng chọn ít nhất một sản phẩm.',
                    ]);
                }

                $selectedItemIds = $selectedItems
                    ->keys()
                    ->map(
                        fn ($itemId): int => (int) $itemId
                    )
                    ->values();

                $orderItems = OrderItem::query()
                    ->where('order_id', $order->id)
                    ->whereIn('id', $selectedItemIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if (
                    $orderItems->count()
                    !== $selectedItemIds->count()
                ) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Có sản phẩm không thuộc đơn hàng này.',
                    ]);
                }

                $preparedItems = [];
                $requestedAmount = 0.0;

                foreach ($selectedItems as $itemId => $itemData) {
                    /** @var OrderItem|null $orderItem */
                    $orderItem = $orderItems->get((int) $itemId);

                    if (! $orderItem) {
                        throw ValidationException::withMessages([
                            'items' =>
                                'Không tìm thấy sản phẩm được chọn.',
                        ]);
                    }

                    $quantity = (int) (
                        $itemData['quantity'] ?? 0
                    );

                    $availableQuantity =
                        $this->availableRequestQuantity(
                            $orderItem,
                            $data['request_type']
                        );

                    if ($quantity < 1) {
                        throw ValidationException::withMessages([
                            "items.{$itemId}.quantity" =>
                                'Số lượng yêu cầu phải từ 1 trở lên.',
                        ]);
                    }

                    if ($quantity > $availableQuantity) {
                        throw ValidationException::withMessages([
                            "items.{$itemId}.quantity" =>
                                sprintf(
                                    'Sản phẩm “%s” chỉ còn %d sản phẩm có thể yêu cầu xử lý.',
                                    $orderItem->product_name,
                                    $availableQuantity
                                ),
                        ]);
                    }

                    $unitRefundAmount = $orderItem->quantity > 0
                        ? (float) $orderItem->total_price
                            / (int) $orderItem->quantity
                        : (float) $orderItem->unit_price;

                    $itemRefundAmount = round(
                        $unitRefundAmount * $quantity,
                        2
                    );

                    $requestedAmount += $itemRefundAmount;

                    $preparedItems[] = [
                        'order_item_id' => $orderItem->id,
                        'quantity' => $quantity,

                        'reason' =>
                            $itemData['reason']
                            ?? $data['reason'],

                        'description' =>
                            $itemData['description']
                            ?? null,

                        'product_condition' =>
                            $itemData['product_condition']
                            ?? null,

                        'requested_refund_amount' =>
                            $itemRefundAmount,

                        'approved_refund_amount' => 0,
                        'inspection_result' => null,
                        'inspection_note' => null,
                        'inventory_action' => null,
                    ];
                }

                if ($data['request_type'] === 'exchange') {
                    $requestedAmount = 0;
                }

                if ($data['request_type'] === 'refund') {
                    $hasRefundablePayment = $order
                        ->payments()
                        ->whereIn('status', [
                            'paid',
                            'partially_refunded',
                        ])
                        ->exists();

                    if (! $hasRefundablePayment) {
                        throw ValidationException::withMessages([
                            'request_type' =>
                                'Đơn hàng không có thanh toán hợp lệ để hoàn tiền.',
                        ]);
                    }
                }

                $returnRequest = ReturnRequest::query()->create([
                    'return_code' =>
                        $this->generateReturnCode(),

                    'order_id' => $order->id,
                    'user_id' => $request->user()->id,

                    'request_type' =>
                        $data['request_type'],

                    'status' => 'pending',
                    'reason' => $data['reason'],

                    'description' =>
                        $data['description'] ?? null,

                    'requested_amount' =>
                        $requestedAmount,

                    'approved_amount' => 0,
                    'return_shipping_fee' => 0,
                    'shipping_fee_payer' => 'customer',

                    'customer_note' =>
                        $data['customer_note'] ?? null,

                    'admin_note' => null,
                    'rejection_reason' => null,
                    'processed_by' => null,

                    'approved_at' => null,
                    'rejected_at' => null,
                    'received_at' => null,
                    'completed_at' => null,
                    'cancelled_at' => null,
                ]);

                foreach ($preparedItems as $preparedItem) {
                    $returnRequest->items()->create(
                        $preparedItem
                    );
                }

                foreach (
                    $request->file('images', [])
                    as $sortOrder => $image
                ) {
                    $imagePath = $image->store(
                        'return-requests/'
                            . $returnRequest->return_code,
                        'public'
                    );

                    $storedImagePaths[] = $imagePath;

                    $returnRequest->images()->create([
                        'return_request_item_id' => null,
                        'image_path' => $imagePath,
                        'caption' => null,
                        'uploaded_by_type' => 'customer',
                        'uploaded_by' => $request->user()->id,
                        'sort_order' => $sortOrder,
                    ]);
                }

                $returnRequest->statusHistories()->create([
                    'from_status' => null,
                    'to_status' => 'pending',

                    'note' => sprintf(
                        'Khách hàng đã tạo yêu cầu %s.',
                        $this->requestTypeLabel(
                            $data['request_type']
                        )
                    ),

                    'source' => 'customer',
                    'created_by' => $request->user()->id,
                ]);

                return $returnRequest;
            }, 3);

            $this->notificationService->safely(function () use ($returnRequest, $request): void {
                $this->notificationService->notifyNewReturnRequest(
                    (int) $returnRequest->id,
                    (string) $returnRequest->return_code,
                    (string) ($request->user()->name ?? 'Khách hàng'),
                    [
                        'order_id' => $returnRequest->order_id,
                        'user_id' => $returnRequest->user_id,
                        'request_type' => $returnRequest->request_type,
                        'requested_amount' => (float) $returnRequest->requested_amount,
                    ]
                );
            });

            return redirect()
                ->route(
                    'account.return-requests.show',
                    $returnRequest->return_code
                )
                ->with(
                    'success',
                    sprintf(
                        'Đã gửi yêu cầu %s thành công.',
                        $returnRequest->return_code
                    )
                );
        } catch (Throwable $exception) {
            foreach ($storedImagePaths as $storedImagePath) {
                Storage::disk('public')->delete(
                    $storedImagePath
                );
            }

            throw $exception;
        }
    }

    /**
     * Chi tiết yêu cầu của khách hàng.
     */
    public function show(string $returnCode): View
    {
        $returnRequest = ReturnRequest::query()
            ->where('return_code', $returnCode)
            ->where('user_id', auth()->id())
            ->with([
                'order:id,order_code,order_status,payment_status,shipping_status,total_amount,created_at',
                'items.orderItem',
                'images',
                'statusHistories.creator',
                'refunds.payment',
            ])
            ->first();

        abort_if(! $returnRequest, 404);

        return view('client.account.return-requests.show', [
            'returnRequest' => $returnRequest,
            'statuses' => $this->statuses(),
            'requestTypes' => $this->requestTypes(),
            'productConditions' => $this->productConditions(),
        ]);
    }

    /**
     * Hủy yêu cầu khi còn trong trạng thái cho phép.
     */
    public function cancel(
        string $returnCode
    ): RedirectResponse {
        $returnRequest = DB::transaction(
            function () use ($returnCode): ReturnRequest {
                $returnRequest = ReturnRequest::query()
                    ->where('return_code', $returnCode)
                    ->where('user_id', auth()->id())
                    ->lockForUpdate()
                    ->first();

                if (! $returnRequest) {
                    throw ValidationException::withMessages([
                        'return_request' =>
                            'Không tìm thấy yêu cầu của bạn.',
                    ]);
                }

                if (! $returnRequest->canBeCancelled()) {
                    throw ValidationException::withMessages([
                        'return_request' =>
                            'Yêu cầu này không thể hủy ở trạng thái hiện tại.',
                    ]);
                }

                $oldStatus = (string) $returnRequest->status;

                $returnRequest->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

                $returnRequest->statusHistories()->create([
                    'from_status' => $oldStatus,
                    'to_status' => 'cancelled',
                    'note' =>
                        'Khách hàng đã hủy yêu cầu.',
                    'source' => 'customer',
                    'created_by' => auth()->id(),
                ]);

                return $returnRequest;
            },
            3
        );

        return redirect()
            ->route(
                'account.return-requests.show',
                $returnRequest->return_code
            )
            ->with(
                'success',
                'Đã hủy yêu cầu thành công.'
            );
    }

    /**
     * Tìm đơn hàng thuộc đúng khách hàng.
     */
    private function findOwnedOrder(
        string $orderCode
    ): Order {
        $order = Order::query()
            ->where('order_code', $orderCode)
            ->where('user_id', auth()->id())
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $order, 404);

        return $order;
    }

    /**
     * Kiểm tra đơn có thể tạo yêu cầu hay không.
     */
    private function ensureOrderCanCreateReturnRequest(
        Order $order
    ): void {
        if ($order->order_status !== 'completed') {
            throw ValidationException::withMessages([
                'order' =>
                    'Chỉ đơn hàng đã hoàn thành mới có thể gửi yêu cầu trả hàng hoặc hoàn tiền.',
            ]);
        }

        if ($order->shipping_status !== 'delivered') {
            throw ValidationException::withMessages([
                'order' =>
                    'Đơn hàng phải được giao thành công trước khi tạo yêu cầu.',
            ]);
        }
    }

    /**
     * Lấy các item đã được khách chọn.
     */
    private function selectedItems(
        array $items
    ): Collection {
        return collect($items)
            ->filter(
                fn (array $item): bool =>
                    filter_var(
                        $item['selected'] ?? false,
                        FILTER_VALIDATE_BOOL
                    )
            );
    }

    /**
     * Số lượng đang nằm trong các yêu cầu chưa kết thúc.
     */
    private function activeRequestedQuantity(
        OrderItem $orderItem
    ): int {
        return (int) ReturnRequestItem::query()
            ->where('order_item_id', $orderItem->id)
            ->whereHas(
                'returnRequest',
                fn ($query) => $query->whereIn(
                    'status',
                    self::ACTIVE_STATUSES
                )
            )
            ->sum('quantity');
    }

    /**
     * Số lượng còn có thể gửi yêu cầu.
     */
    private function availableRequestQuantity(
        OrderItem $orderItem,
        string $requestType
    ): int {
        $alreadyProcessedQuantity =
            $requestType === 'refund'
                ? (int) $orderItem->refunded_quantity
                : (int) $orderItem->returned_quantity;

        return max(
            0,
            (int) $orderItem->quantity
                - $alreadyProcessedQuantity
                - $this->activeRequestedQuantity($orderItem)
        );
    }

    /**
     * Sinh mã yêu cầu duy nhất.
     */
    private function generateReturnCode(): string
    {
        do {
            $returnCode = 'RET'
                . now()->format('YmdHis')
                . strtoupper(Str::random(6));
        } while (
            ReturnRequest::query()
                ->where('return_code', $returnCode)
                ->exists()
        );

        return $returnCode;
    }

    /**
     * Danh sách loại yêu cầu.
     */
    private function requestTypes(): array
    {
        return [
            'return' => 'Trả hàng',
            'exchange' => 'Đổi sản phẩm',
            'refund' => 'Hoàn tiền',
        ];
    }

    /**
     * Tên loại yêu cầu.
     */
    private function requestTypeLabel(
        string $requestType
    ): string {
        return $this->requestTypes()[$requestType]
            ?? $requestType;
    }

    /**
     * Danh sách trạng thái.
     */
    private function statuses(): array
    {
        return [
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
    }

    /**
     * Tình trạng sản phẩm theo migration thật.
     */
    private function productConditions(): array
    {
        return [
            'unopened' => 'Chưa mở',
            'opened' => 'Đã mở',
            'damaged' => 'Hư hỏng',
            'defective' => 'Lỗi sản phẩm',
            'wrong_item' => 'Giao sai sản phẩm',
            'expired' => 'Hết hạn',
            'allergic' => 'Gây kích ứng',
            'other' => 'Khác',
        ];
    }
}
