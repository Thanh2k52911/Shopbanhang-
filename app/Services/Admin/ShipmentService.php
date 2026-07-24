<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShipmentService
{
    public function __construct(
        private readonly DemoShippingProvider $demoShippingProvider
    ) {
    }
    /**
     * Tạo kiện vận chuyển cho đơn hàng.
     */
    public function create(
        Order $order,
        array $data,
        int $adminId
    ): Shipment {
        return DB::transaction(function () use (
            $order,
            $data,
            $adminId
        ): Shipment {
            /*
            |--------------------------------------------------------------------------
            | Khóa đơn hàng và tải dữ liệu cần thiết
            |--------------------------------------------------------------------------
            */

            $lockedOrder = Order::query()
                ->with([
                    'items:id,order_id,sku_id,quantity',
                    'items.sku:id,weight',
                    'warehouse:id,name',
                    'shippingAddress',
                ])
                ->lockForUpdate()
                ->findOrFail($order->id);

            /*
             * Dữ liệu tự động luôn được lấy từ đơn hàng. Dữ liệu form chỉ được
             * phép ghi đè các trường đóng gói như trọng lượng, kích thước, ghi chú.
             */
            $automaticData = $this->demoShippingProvider->prepare($lockedOrder);

            $data = array_merge(
                $automaticData,
                array_filter(
                    $data,
                    static fn ($value): bool => $value !== null && $value !== ''
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Chỉ tạo shipment khi đơn đã đóng gói
            |--------------------------------------------------------------------------
            */

            if ($lockedOrder->order_status !== 'packed') {
                throw ValidationException::withMessages([
                    'shipment' =>
                        'Chỉ có thể tạo kiện vận chuyển khi đơn hàng đã đóng gói.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Đơn phải có kho xử lý
            |--------------------------------------------------------------------------
            */

            if (! $lockedOrder->warehouse_id) {
                throw ValidationException::withMessages([
                    'shipment' =>
                        'Đơn hàng chưa được gán kho xử lý.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Đơn phải có sản phẩm
            |--------------------------------------------------------------------------
            */

            if ($lockedOrder->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'shipment' =>
                        'Đơn hàng không có sản phẩm để tạo kiện vận chuyển.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Chặn tạo kiện trùng
            |--------------------------------------------------------------------------
            |
            | Cho phép tạo kiện mới nếu tất cả kiện cũ đã cancelled.
            |
            */

            $hasActiveShipment = $lockedOrder->shipments()
                ->where('status', '!=', 'cancelled')
                ->lockForUpdate()
                ->exists();

            if ($hasActiveShipment) {
                throw ValidationException::withMessages([
                    'shipment' =>
                        'Đơn hàng đã có kiện vận chuyển đang hoạt động.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Xác định phương thức và tên đơn vị vận chuyển
            |--------------------------------------------------------------------------
            */

            $shippingMethod = null;

            if (! empty($data['shipping_method_id'])) {
                $shippingMethod = ShippingMethod::query()
                    ->whereKey($data['shipping_method_id'])
                    ->where('status', true)
                    ->lockForUpdate()
                    ->first();

                if (! $shippingMethod) {
                    throw ValidationException::withMessages([
                        'shipping_method_id' =>
                            'Phương thức vận chuyển không tồn tại hoặc đã ngừng hoạt động.',
                    ]);
                }
            }

            $carrierName = $data['carrier_name']
                ?? $shippingMethod?->provider
                ?? $shippingMethod?->name;

            if (! $carrierName) {
                throw ValidationException::withMessages([
                    'carrier_name' =>
                        'Vui lòng chọn phương thức vận chuyển hoặc nhập tên đơn vị vận chuyển.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Tính số tiền thu hộ COD
            |--------------------------------------------------------------------------
            |
            | Không nhận cod_amount từ form để tránh Admin sửa sai tổng tiền.
            |
            */

            $codAmount = (
                $lockedOrder->payment_method === 'cod'
                && $lockedOrder->payment_status !== 'paid'
            )
                ? (float) $lockedOrder->total_amount
                : 0;

            /*
            |--------------------------------------------------------------------------
            | Tạo shipment
            |--------------------------------------------------------------------------
            */

            $shipment = Shipment::query()->create([
                'order_id' => $lockedOrder->id,

                'shipping_method_id' => $shippingMethod?->id,

                'warehouse_id' => $lockedOrder->warehouse_id,

                'shipment_code' => $this->generateShipmentCode(),

                'tracking_code' =>
                    $data['tracking_code'] ?? null,

                'carrier_name' => $carrierName,

                'service_name' =>
                    $data['service_name'] ?? null,

                'status' => 'ready_to_ship',

                'shipping_fee' => (float) $lockedOrder->shipping_fee,

                'cod_amount' => $codAmount,

                'weight' =>
                    $data['weight'] ?? null,

                'length' =>
                    $data['length'] ?? null,

                'width' =>
                    $data['width'] ?? null,

                'height' =>
                    $data['height'] ?? null,

                'note' =>
                    $data['note'] ?? null,

                'provider_data' => $data['provider_data'] ?? null,

                'estimated_delivery_at' =>
                    $data['estimated_delivery_at'] ?? null,

                'picked_up_at' => null,
                'delivered_at' => null,
                'failed_at' => null,
                'cancelled_at' => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Tạo shipment_items
            |--------------------------------------------------------------------------
            |
            | Schema thật chỉ có:
            | shipment_id, order_item_id, quantity
            |
            */

            foreach ($lockedOrder->items as $item) {
                $shipment->items()->create([
                    'order_item_id' => $item->id,
                    'quantity' => $item->quantity,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Ghi lịch sử Shipment
            |--------------------------------------------------------------------------
            */

            $shipment->statusHistories()->create([
                'from_status' => null,
                'to_status' => 'ready_to_ship',

                'location' =>
                    $lockedOrder->warehouse?->name,

                'description' =>
                    'Hệ thống đã tự tạo kiện vận chuyển theo thông tin khách hàng đã chọn.',

                'source' => 'admin',
                'created_by' => $adminId,

                'provider_data' => $data['provider_data'] ?? null,
                'occurred_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Đồng bộ shipping_status của Order
            |--------------------------------------------------------------------------
            */

            $oldShippingStatus = $lockedOrder->shipping_status;

            if ($oldShippingStatus !== 'ready_to_ship') {
                $lockedOrder->update([
                    'shipping_status' => 'ready_to_ship',
                ]);

                $lockedOrder->statusHistories()->create([
                    'from_status' => $oldShippingStatus,
                    'to_status' => 'ready_to_ship',
                    'status_type' => 'shipping',

                    'note' =>
                        'Đã tạo kiện hàng và sẵn sàng bàn giao cho đơn vị vận chuyển.',

                    'created_by' => $adminId,
                    'source' => 'admin',
                    'occurred_at' => now(),
                ]);
            }

            return $shipment->fresh([
                'order',
                'shippingMethod',
                'warehouse',
                'items.orderItem',
                'statusHistories.creator',
            ]);
        }, 3);
    }

    /**
     * Sinh mã kiện hàng nội bộ duy nhất.
     */
    private function generateShipmentCode(): string
    {
        do {
            $shipmentCode = 'SHP'
                . now()->format('YmdHis')
                . strtoupper(Str::random(6));
        } while (
            Shipment::query()
                ->where('shipment_code', $shipmentCode)
                ->exists()
        );

        return $shipmentCode;
    }
}
