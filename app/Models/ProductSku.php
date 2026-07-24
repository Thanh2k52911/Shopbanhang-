<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSku extends Model
{
    protected $fillable = [
        'product_id',
        'variant_id',
        'sku_code',
        'barcode',
        'price',
        'cost_price',
        'weight',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'weight' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariant::class,
            'variant_id'
        );
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(
            Inventory::class,
            'sku_id'
        );
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(
            InventoryTransaction::class,
            'sku_id'
        );
    }

    public function getTotalStockAttribute(): int
{
    return (int) $this->inventories->sum('quantity');
}

public function getAvailableStockAttribute(): int
{
    return (int) $this->inventories->sum(
        fn (Inventory $inventory) => $inventory->available_quantity
    );
}

public function getIsInStockAttribute(): bool
{
    return $this->available_stock > 0;
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(
            CartItem::class,
            'sku_id'
        );
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(
            OrderItem::class,
            'sku_id'
        );
    }
}
