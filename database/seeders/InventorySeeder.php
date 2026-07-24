<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\ProductSku;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $warehouse = Warehouse::first();

        foreach (ProductSku::all() as $sku) {

            Inventory::updateOrCreate(

                [
                    'warehouse_id' => $warehouse->id,
                    'sku_id' => $sku->id,
                ],

                [
                    'quantity' => rand(30,100),

                    'reserved_quantity' => rand(0,5),

                    'sold_quantity' => rand(10,50),

                    'minimum_stock' => 10,
                ]

            );
        }
    }
}
