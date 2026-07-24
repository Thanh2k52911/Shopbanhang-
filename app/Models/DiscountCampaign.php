<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscountCampaign extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'is_flash_sale',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_flash_sale' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function productDiscounts(): HasMany
    {
        return $this->hasMany(
            ProductDiscount::class,
            'campaign_id'
        );
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_discounts',
            'campaign_id',
            'product_id'
        )
            ->withPivot([
                'id',
                'discount_percent',
                'discount_amount',
                'limit_quantity',
                'sold_quantity',
            ])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeFlashSale(Builder $query): Builder
    {
        return $query->where('is_flash_sale', true);
    }

    public function isCurrentlyActive(): bool
    {
        return $this->status
            && $this->start_date?->lte(now())
            && $this->end_date?->gte(now());
    }

    public function hasEnded(): bool
    {
        return $this->end_date?->isPast() ?? false;
    }

    public function hasStarted(): bool
    {
        return $this->start_date?->isPast() ?? false;
    }
}
