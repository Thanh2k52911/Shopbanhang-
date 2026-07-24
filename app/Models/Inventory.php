<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $fillable = [
        'warehouse_id',
        'sku_id',
        'quantity',
        'reserved_quantity',
        'sold_quantity',
        'minimum_stock',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'sold_quantity' => 'integer',
            'minimum_stock' => 'integer',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(
            ProductSku::class,
            'sku_id'
        );
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(
            0,
            $this->quantity - $this->reserved_quantity
        );
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->available_quantity <= $this->minimum_stock;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->available_quantity <= 0;
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereRaw(
            '(quantity - reserved_quantity) <= minimum_stock'
        );
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->whereRaw(
            '(quantity - reserved_quantity) <= 0'
        );
    }

    public function scopeInWarehouse(
        Builder $query,
        int $warehouseId
    ): Builder {
        return $query->where('warehouse_id', $warehouseId);
    }
}
