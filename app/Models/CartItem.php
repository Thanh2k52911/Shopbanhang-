<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'sku_id',
        'quantity',
        'unit_price',
        'discount_amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(
            ProductSku::class,
            'sku_id'
        );
    }

    public function getFinalUnitPriceAttribute(): float
    {
        return max(
            0,
            (float) $this->unit_price -
            (float) $this->discount_amount
        );
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->unit_price * $this->quantity;
    }

    public function getTotalAttribute(): float
    {
        return $this->final_unit_price * $this->quantity;
    }
}
