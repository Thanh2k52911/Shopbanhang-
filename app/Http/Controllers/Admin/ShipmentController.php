<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateShipmentRequest;
use App\Http\Requests\Admin\UpdateShipmentStatusRequest;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShippingMethod;
use App\Services\Admin\DemoShippingProvider;
use App\Services\Admin\ShipmentService;
use App\Services\Admin\ShipmentStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    /**
     * Danh sách kiện vận chuyển.
     */
    public function index(
        Request $request,
        ShipmentStatusService $shipmentStatusService
    ): View {
        $query = Shipment::query()
            ->with([
                'order:id,order_code,customer_name,customer_phone,order_status',
                'shippingMethod:id,name',
                'warehouse:id,name',
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
                        'shipment_code',
                        'like',
                        '%' . $keyword . '%'
                    )
                    ->orWhere(
                        'tracking_code',
                        'like',
                        '%' . $keyword . '%'
                    )
                    ->orWhere(
                        'carrier_name',
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
        | Lọc đơn vị vận chuyển
        |--------------------------------------------------------------------------
        */

        if ($request->filled('carrier_name')) {
            $query->where(
                'carrier_name',
                $request->input('carrier_name')
            );
        }

        $shipments = $query
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Trạng thái tiếp theo của từng Shipment
        |--------------------------------------------------------------------------
        |
        | Mỗi kiện chỉ được hiển thị các bước chuyển hợp lệ theo đúng quy tắc
        | trong ShipmentStatusService.
        |
        */

        $shipmentTransitions = $shipments
            ->getCollection()
            ->mapWithKeys(
                fn (Shipment $shipment): array => [
                    $shipment->id =>
                        $shipmentStatusService->availableTransitions(
                            $shipment
                        ),
                ]
            )
            ->all();

        $statuses = $this->shipmentStatuses();

        $carriers = Shipment::query()
            ->whereNotNull('carrier_name')
            ->where('carrier_name', '!=', '')
            ->distinct()
            ->orderBy('carrier_name')
            ->pluck('carrier_name');

        $ordersAwaitingShipment = Order::query()
            ->with(['warehouse:id,name', 'shippingMethod:id,name,provider'])
            ->where('order_status', 'packed')
            ->whereDoesntHave('shipments', function ($builder): void {
                $builder->whereNotIn('status', ['cancelled', 'returned']);
            })
            ->latest('packed_at')
            ->limit(10)
            ->get();

        return view('admin.shipments.index', [
            'shipments' => $shipments,
            'shipmentTransitions' => $shipmentTransitions,
            'statuses' => $statuses,
            'carriers' => $carriers,
            'ordersAwaitingShipment' => $ordersAwaitingShipment,
        ]);
    }

    /**
     * Chi tiết kiện vận chuyển.
     */
    public function show(
        Shipment $shipment,
        ShipmentStatusService $shipmentStatusService
    ): View {
        $shipment->load([
            'order',
            'shippingMethod',
            'warehouse',
            'items.orderItem',
            'statusHistories.creator',
        ]);

        $nextShipmentStatuses = $shipmentStatusService
            ->availableTransitions($shipment);

        return view('admin.shipments.show', [
            'shipment' => $shipment,
            'shipmentStatuses' => $this->shipmentStatuses(),
            'nextShipmentStatuses' => $nextShipmentStatuses,
        ]);
    }

    /**
     * Hiển thị form tạo kiện hàng.
     */
    public function create(
        Order $order,
        DemoShippingProvider $demoShippingProvider
    ): View|RedirectResponse
    {
        $order->load([
            'items.product',
            'items.sku',
            'warehouse',
            'shippingMethod',
            'shippingAddress',
            'shipments:id,order_id,shipment_code,status',
        ]);

        if ($order->order_status !== 'packed') {
            return redirect()
                ->route('admin.orders.show', $order)
                ->with(
                    'error',
                    'Chỉ có thể tạo kiện vận chuyển khi đơn hàng đã đóng gói.'
                );
        }

        if (! $order->warehouse_id) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->with(
                    'error',
                    'Đơn hàng chưa được gán kho xử lý.'
                );
        }

        $hasActiveShipment = $order->shipments->contains(
            fn (Shipment $shipment): bool => ! in_array(
                $shipment->status,
                ['cancelled', 'returned'],
                true
            )
        );

        if ($hasActiveShipment) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->with(
                    'error',
                    'Đơn hàng đã có kiện vận chuyển đang hoạt động.'
                );
        }

        $shippingMethods = ShippingMethod::query()
            ->active()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'provider',
                'estimated_days_min',
                'estimated_days_max',
            ]);

        return view('admin.shipments.create', [
            'order' => $order,
            'shippingMethods' => $shippingMethods,
            'automaticShipment' => $demoShippingProvider->prepare($order),
        ]);
    }

    /**
     * Lưu kiện vận chuyển.
     */
    public function store(
        CreateShipmentRequest $request,
        Order $order,
        ShipmentService $shipmentService
    ): RedirectResponse {
        $shipment = $shipmentService->create(
            order: $order,
            data: $request->validated(),
            adminId: $request->user()->id
        );

        return redirect()
            ->route('admin.orders.show', $order)
            ->with(
                'success',
                sprintf(
                    'Đã tạo kiện vận chuyển %s.',
                    $shipment->shipment_code
                )
            );
    }

    /**
     * Tạo kiện tự động chỉ bằng một nút bấm.
     */
    public function storeAutomatic(
        Request $request,
        Order $order,
        ShipmentService $shipmentService
    ): RedirectResponse {
        $shipment = $shipmentService->create(
            order: $order,
            data: [],
            adminId: $request->user()->id
        );

        return redirect()
            ->route('admin.shipments.show', $shipment)
            ->with(
                'success',
                sprintf(
                    'Đã tự động tạo kiện %s với mã vận đơn %s.',
                    $shipment->shipment_code,
                    $shipment->tracking_code
                )
            );
    }

    /**
     * Cập nhật trạng thái kiện vận chuyển.
     */
    public function updateStatus(
        UpdateShipmentStatusRequest $request,
        Shipment $shipment,
        ShipmentStatusService $shipmentStatusService
    ): RedirectResponse {
        $validated = $request->validated();

        $updatedShipment = $shipmentStatusService->updateStatus(
            shipment: $shipment,
            newStatus: $validated['status'],
            location: $validated['location'] ?? null,
            description: $validated['description'] ?? null,
            adminId: $request->user()->id
        );

        /*
        |--------------------------------------------------------------------------
        | Giữ lại trang trước đó
        |--------------------------------------------------------------------------
        |
        | Nếu cập nhật từ danh sách thì quay lại danh sách.
        | Nếu cập nhật từ trang chi tiết thì quay lại trang chi tiết.
        |
        */

        return redirect()
            ->back()
            ->with(
                'success',
                sprintf(
                    'Đã cập nhật kiện %s sang trạng thái “%s”.',
                    $updatedShipment->shipment_code,
                    $this->shipmentStatusLabel(
                        $updatedShipment->status
                    )
                )
            );
    }

    /**
     * Danh sách trạng thái Shipment.
     */
    private function shipmentStatuses(): array
    {
        return [
            'ready_to_ship' => 'Sẵn sàng giao',
            'picked_up' => 'Đã lấy hàng',
            'in_transit' => 'Đang vận chuyển',
            'out_for_delivery' => 'Đang giao hàng',
            'delivered' => 'Đã giao hàng',
            'delivery_failed' => 'Giao hàng thất bại',
            'returned' => 'Đã hoàn hàng',
            'cancelled' => 'Đã hủy',
        ];
    }

    /**
     * Tên trạng thái Shipment bằng tiếng Việt.
     */
    private function shipmentStatusLabel(string $status): string
    {
        return $this->shipmentStatuses()[$status] ?? $status;
    }
}
