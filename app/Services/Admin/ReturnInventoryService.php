<?php

namespace App\Services\Admin;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use App\Models\ReturnRequestItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnInventoryService
{
    /**
     * Chốt số lượng hàng đã trả và nhập lại kho các dòng đủ điều kiện.
     *
     * Phương thức này có tính idempotent: gọi lại nhiều lần sẽ không cộng kho
     * lặp nhờ inventory_transactions tham chiếu theo ReturnRequestItem.
     */
    public function processCompletedReturn(
        ReturnRequest $returnRequest,
        ?int $adminId = null
    ): ReturnRequest {
        return DB::transaction(function () use ($returnRequest, $adminId): ReturnRequest {
            $lockedRequest = ReturnRequest::query()
                ->with([
                    'order:id,warehouse_id',
                    'items.orderItem:id,order_id,sku_id,quantity,returned_quantity,refunded_quantity',
                ])
                ->lockForUpdate()
                ->findOrFail($returnRequest->id);

            if ($lockedRequest->status !== ReturnRequest::STATUS_COMPLETED) {
                throw ValidationException::withMessages([
                    'status' => 'Chỉ được xử lý tồn kho khi yêu cầu trả hàng đã hoàn tất.',
                ]);
            }

            $warehouseId = (int) ($lockedRequest->order?->warehouse_id ?? 0);

            foreach ($lockedRequest->items as $returnItem) {
                $this->syncReturnedQuantity($returnItem);

                if (! $returnItem->canRestock()) {
                    continue;
                }

                if ($warehouseId <= 0) {
                    throw ValidationException::withMessages([
                        'warehouse_id' => 'Đơn hàng chưa có kho để nhập lại sản phẩm trả về.',
                    ]);
                }

                $this->restockItem(
                    returnItem: $returnItem,
                    warehouseId: $warehouseId,
                    adminId: $adminId
                );
            }

            return $lockedRequest->fresh([
                'order',
                'items.orderItem',
            ]);
        }, 3);
    }

    private function syncReturnedQuantity(ReturnRequestItem $returnItem): void
    {
        $orderItem = OrderItem::query()
            ->lockForUpdate()
            ->find($returnItem->order_item_id);

        if (! $orderItem) {
            return;
        }

        $completedReturnedQuantity = (int) ReturnRequestItem::query()
            ->where('order_item_id', $orderItem->id)
            ->whereHas('returnRequest', function ($query): void {
                $query->where('status', ReturnRequest::STATUS_COMPLETED);
            })
            ->sum('quantity');

        $orderItem->update([
            'returned_quantity' => min(
                (int) $orderItem->quantity,
                max(0, $completedReturnedQuantity)
            ),
        ]);
    }

    private function restockItem(
        ReturnRequestItem $returnItem,
        int $warehouseId,
        ?int $adminId
    ): void {
        $orderItem = $returnItem->orderItem;
        $skuId = (int) ($orderItem?->sku_id ?? 0);
        $quantity = (int) $returnItem->quantity;

        if ($skuId <= 0 || $quantity <= 0) {
            return;
        }

        $alreadyProcessed = InventoryTransaction::query()
            ->where('type', 'return')
            ->where('reference_type', ReturnRequestItem::class)
            ->where('reference_id', $returnItem->id)
            ->lockForUpdate()
            ->exists();

        if ($alreadyProcessed) {
            return;
        }

        $inventory = Inventory::query()
            ->where('warehouse_id', $warehouseId)
            ->where('sku_id', $skuId)
            ->lockForUpdate()
            ->first();

        if (! $inventory) {
            $inventory = Inventory::query()->create([
                'warehouse_id' => $warehouseId,
                'sku_id' => $skuId,
                'quantity' => 0,
                'reserved_quantity' => 0,
                'sold_quantity' => 0,
                'minimum_stock' => 10,
            ]);
        }

        $inventory->update([
            'quantity' => (int) $inventory->quantity + $quantity,
            'sold_quantity' => max(
                0,
                (int) $inventory->sold_quantity - $quantity
            ),
        ]);

        InventoryTransaction::query()->create([
            'warehouse_id' => $warehouseId,
            'sku_id' => $skuId,
            'type' => 'return',
            'quantity' => $quantity,
            'reference_type' => ReturnRequestItem::class,
            'reference_id' => $returnItem->id,
            'note' => sprintf(
                'Nhập lại kho từ yêu cầu trả hàng %s, dòng #%d.',
                $returnItem->returnRequest?->return_code ?? ('#'.$returnItem->return_request_id),
                $returnItem->id
            ),
            'created_by' => $adminId,
        ]);
    }
}
