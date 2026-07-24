<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDiscount extends Model
{
    protected $fillable = [
        'campaign_id',
        'product_id',
        'discount_percent',
        'discount_amount',
        'limit_quantity',
        'sold_quantity',
    ];

    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'limit_quantity' => 'integer',
            'sold_quantity' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(
            DiscountCampaign::class,
            'campaign_id'
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getRemainingQuantityAttribute(): ?int
    {
        if ($this->limit_quantity === null) {
            return null;
        }

        return max(
            0,
            $this->limit_quantity - $this->sold_quantity
        );
    }

    public function getIsSoldOutAttribute(): bool
    {
        return $this->limit_quantity !== null
            && $this->remaining_quantity <= 0;
    }

    public function calculateDiscount(float $price): float
    {
        if ($this->discount_percent !== null) {
            return round(
                $price * ((float) $this->discount_percent / 100),
                2
            );
        }

        if ($this->discount_amount !== null) {
            return min(
                $price,
                (float) $this->discount_amount
            );
        }

        return 0;
    }

    public function calculateSalePrice(float $price): float
    {
        return max(
            0,
            $price - $this->calculateDiscount($price)
        );
    }
}
