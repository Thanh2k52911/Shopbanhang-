<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReturnRequestStatusRequest;
use App\Models\ReturnRequest;
use App\Services\Admin\ReturnRequestStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnRequestController extends Controller
{
    /**
     * Danh sách yêu cầu trả hàng.
     */
    public function index(Request $request): View
    {
        $query = ReturnRequest::query()
            ->with([
                'order:id,order_code,customer_name,customer_phone,order_status,payment_status',
                'user:id,name,email',
                'processor:id,name,email',
            ])
            ->withCount([
                'items',
                'images',
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
                        'return_code',
                        'like',
                        '%' . $keyword . '%'
                    )
                    ->orWhere(
                        'reason',
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
                    )
                    ->orWhereHas(
                        'user',
                        function ($userQuery) use ($keyword): void {
                            $userQuery
                                ->where(
                                    'name',
                                    'like',
                                    '%' . $keyword . '%'
                                )
                                ->orWhere(
                                    'email',
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
        | Lọc loại yêu cầu
        |--------------------------------------------------------------------------
        */

        if ($request->filled('request_type')) {
            $query->where(
                'request_type',
                $request->input('request_type')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lọc ngày tạo
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->input('date_to')
            );
        }

        $returnRequests = $query
            ->paginate(20)
            ->withQueryString();

        $statistics = [
            'total' => ReturnRequest::query()->count(),

            'pending' => ReturnRequest::query()
                ->where('status', 'pending')
                ->count(),

            'approved' => ReturnRequest::query()
                ->whereIn('status', [
                    'approved',
                    'waiting_for_return',
                ])
                ->count(),

            'received' => ReturnRequest::query()
                ->where('status', 'received')
                ->count(),

            'processing' => ReturnRequest::query()
                ->where('status', 'processing')
                ->count(),

            'completed' => ReturnRequest::query()
                ->where('status', 'completed')
                ->count(),

            'rejected' => ReturnRequest::query()
                ->where('status', 'rejected')
                ->count(),
        ];

        return view('admin.return-requests.index', [
            'returnRequests' => $returnRequests,
            'statistics' => $statistics,
            'statuses' => $this->statuses(),
            'requestTypes' => $this->requestTypes(),
        ]);
    }

    /**
     * Chi tiết yêu cầu trả hàng.
     */
    public function show(
        ReturnRequest $returnRequest,
        ReturnRequestStatusService $returnRequestStatusService
    ): View {
        $returnRequest->load([
            'order.user',
            'order.shippingAddress',
            'user',
            'processor',
            'items.orderItem',
            'images',
            'statusHistories.creator',
            'refunds.payment',
        ]);

        $nextReturnStatuses = $returnRequestStatusService
            ->availableTransitions($returnRequest);

        return view('admin.return-requests.show', [
            'returnRequest' => $returnRequest,
            'statuses' => $this->statuses(),
            'requestTypes' => $this->requestTypes(),
            'productConditions' => $this->productConditions(),
            'inspectionResults' => $this->inspectionResults(),
            'inventoryActions' => $this->inventoryActions(),
            'shippingFeePayers' => $this->shippingFeePayers(),
            'nextReturnStatuses' => $nextReturnStatuses,
        ]);
    }

    /**
     * Cập nhật trạng thái yêu cầu trả hàng.
     */
    public function updateStatus(
        UpdateReturnRequestStatusRequest $request,
        ReturnRequest $returnRequest,
        ReturnRequestStatusService $returnRequestStatusService
    ): RedirectResponse {
        $updatedReturnRequest = $returnRequestStatusService->updateStatus(
            returnRequest: $returnRequest,
            data: $request->validated(),
            adminId: $request->user()->id
        );

        return redirect()
            ->route(
                'admin.return-requests.show',
                $updatedReturnRequest
            )
            ->with(
                'success',
                sprintf(
                    'Đã cập nhật yêu cầu %s sang trạng thái “%s”.',
                    $updatedReturnRequest->return_code,
                    $this->statusLabel(
                        $updatedReturnRequest->status
                    )
                )
            );
    }

    /**
     * Danh sách trạng thái Return Request.
     */
    private function statuses(): array
    {
        return [
            'pending' => 'Chờ xử lý',
            'approved' => 'Đã chấp thuận',
            'waiting_for_return' => 'Chờ khách gửi hàng',
            'received' => 'Đã nhận hàng trả',
            'processing' => 'Đang xử lý',
            'completed' => 'Đã hoàn tất',
            'rejected' => 'Đã từ chối',
            'cancelled' => 'Đã hủy',
        ];
    }

    /**
     * Danh sách loại yêu cầu.
     */
    private function requestTypes(): array
    {
        return [
            'return' => 'Trả hàng',
            'refund' => 'Hoàn tiền',
            'return_refund' => 'Trả hàng và hoàn tiền',
            'exchange' => 'Đổi hàng',
        ];
    }

    /**
     * Tình trạng sản phẩm.
     */
    private function productConditions(): array
    {
        return [
            'unopened' => 'Chưa mở hộp',
            'opened' => 'Đã mở hộp',
            'used' => 'Đã sử dụng',
            'damaged' => 'Bị hư hỏng',
            'defective' => 'Sản phẩm lỗi',
            'wrong_item' => 'Giao sai sản phẩm',
            'missing_parts' => 'Thiếu phụ kiện/bộ phận',
            'other' => 'Khác',
        ];
    }

    /**
     * Kết quả kiểm tra sản phẩm.
     */
    private function inspectionResults(): array
    {
        return [
            'pending' => 'Chưa kiểm tra',
            'accepted' => 'Chấp nhận',
            'partially_accepted' => 'Chấp nhận một phần',
            'rejected' => 'Từ chối',
        ];
    }

    /**
     * Hướng xử lý tồn kho.
     */
    private function inventoryActions(): array
    {
        return [
            'none' => 'Không nhập lại kho',
            'restock' => 'Nhập lại kho bán',
            'damaged' => 'Chuyển kho hàng hỏng',
            'dispose' => 'Tiêu hủy',
            'return_supplier' => 'Trả nhà cung cấp',
        ];
    }

    /**
     * Bên chịu phí gửi trả.
     */
    private function shippingFeePayers(): array
    {
        return [
            'customer' => 'Khách hàng',
            'shop' => 'Cửa hàng',
            'shared' => 'Hai bên cùng chịu',
        ];
    }

    /**
     * Tên trạng thái tiếng Việt.
     */
    private function statusLabel(string $status): string
    {
        return $this->statuses()[$status] ?? $status;
    }
}
