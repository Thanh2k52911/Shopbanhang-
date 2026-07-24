<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $warehouseId = DB::table('warehouses')
            ->where('status', 1)
            ->orderBy('id')
            ->value('id');

        $createdBy = DB::table('users')
            ->orderBy('id')
            ->value('id');

        if (!$warehouseId) {
            $this->command?->warn(
                'Không có warehouse để tạo inventory transactions.'
            );

            return;
        }

        $transactions = [
            [
                'sku_code' => 'CERAVE-FOAM-236ML',
                'type' => 'import',
                'quantity' => 100,
                'reference_type' => 'initial_stock',
                'reference_code' => null,
                'note' => 'Nhập kho ban đầu.',
            ],
            [
                'sku_code' => 'CERAVE-FOAM-473ML',
                'type' => 'import',
                'quantity' => 80,
                'reference_type' => 'initial_stock',
                'reference_code' => null,
                'note' => 'Nhập kho ban đầu.',
            ],
            [
                'sku_code' => 'SENKA-WHIP-100G',
                'type' => 'import',
                'quantity' => 150,
                'reference_type' => 'initial_stock',
                'reference_code' => null,
                'note' => 'Nhập kho ban đầu.',
            ],
            [
                'sku_code' => 'SENKA-WHIP-120G',
                'type' => 'import',
                'quantity' => 120,
                'reference_type' => 'initial_stock',
                'reference_code' => null,
                'note' => 'Nhập kho ban đầu.',
            ],
            [
                'sku_code' => 'BIODERMA-H2O-250ML',
                'type' => 'export',
                'quantity' => -1,
                'reference_type' => 'order',
                'reference_code' => 'ORD-DEMO-0004',
                'note' => 'Xuất kho cho đơn hàng đã hoàn thành.',
            ],
            [
                'sku_code' => 'SENKA-WHIP-120G',
                'type' => 'export',
                'quantity' => -1,
                'reference_type' => 'order',
                'reference_code' => 'ORD-DEMO-0004',
                'note' => 'Xuất kho cho đơn hàng đã hoàn thành.',
            ],
            [
                'sku_code' => 'LRP-ANTHELIOS-50ML',
                'type' => 'cancel',
                'quantity' => 1,
                'reference_type' => 'order',
                'reference_code' => 'ORD-DEMO-0005',
                'note' => 'Hoàn lại tồn kho do khách hủy đơn.',
            ],
            [
                'sku_code' => 'ANESSA-UV-60ML',
                'type' => 'adjust',
                'quantity' => -2,
                'reference_type' => 'stock_check',
                'reference_code' => null,
                'note' => 'Điều chỉnh tồn kho sau kiểm kê.',
            ],
        ];

        foreach ($transactions as $index => $transaction) {
            $skuId = DB::table('product_skus')
                ->where('sku_code', $transaction['sku_code'])
                ->value('id');

            if (!$skuId) {
                $this->command?->warn(
                    "Không tìm thấy SKU: {$transaction['sku_code']}"
                );

                continue;
            }

            $referenceId = null;

            if (
                $transaction['reference_type'] === 'order'
                && $transaction['reference_code']
            ) {
                $referenceId = DB::table('orders')
                    ->where(
                        'order_code',
                        $transaction['reference_code']
                    )
                    ->value('id');
            }

            $exists = DB::table('inventory_transactions')
                ->where('warehouse_id', $warehouseId)
                ->where('sku_id', $skuId)
                ->where('type', $transaction['type'])
                ->where('reference_type', $transaction['reference_type'])
                ->where('reference_id', $referenceId)
                ->where('note', $transaction['note'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('inventory_transactions')->insert([
                'warehouse_id' => $warehouseId,
                'sku_id' => $skuId,
                'type' => $transaction['type'],
                'quantity' => $transaction['quantity'],
                'reference_type' => $transaction['reference_type'],
                'reference_id' => $referenceId,
                'note' => $transaction['note'],
                'created_by' => $createdBy,
                'created_at' => now()->subDays(8 - $index),
                'updated_at' => now(),
            ]);
        }
    }
}
