<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'sku_id',

        'product_name',
        'product_slug',
        'variant_name',
        'sku_code',
        'barcode',
        'image_path',

        'original_price',
        'unit_price',
        'discount_amount',
        'quantity',
        'total_price',

        'is_reviewed',
        'returned_quantity',
        'refunded_quantity',
    ];

    protected function casts(): array
    {
        return [
            'original_price' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_price' => 'decimal:2',

            'quantity' => 'integer',
            'is_reviewed' => 'boolean',
            'returned_quantity' => 'integer',
            'refunded_quantity' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
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

    public function sku(): BelongsTo
    {
        return $this->belongsTo(
            ProductSku::class,
            'sku_id'
        );
    }

    public function shipmentItems(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function returnRequestItems(): HasMany
    {
        return $this->hasMany(ReturnRequestItem::class);
    }

    public function getRemainingReturnableQuantityAttribute(): int
    {
        return max(
            0,
            $this->quantity - $this->returned_quantity
        );
    }

    public function getRemainingRefundableQuantityAttribute(): int
    {
        return max(
            0,
            $this->quantity - $this->refunded_quantity
        );
    }

    public function canBeReturned(): bool
    {
        return $this->remaining_returnable_quantity > 0;
    }
}
