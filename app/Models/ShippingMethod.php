<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingMethod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'provider',
        'description',
        'base_fee',
        'free_shipping_minimum',
        'estimated_days_min',
        'estimated_days_max',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'base_fee' => 'decimal:2',
            'free_shipping_minimum' => 'decimal:2',
            'estimated_days_min' => 'integer',
            'estimated_days_max' => 'integer',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', true)
            ->orderBy('sort_order');
    }

    public function calculateFee(float $orderAmount): float
    {
        if (
            $this->free_shipping_minimum !== null
            && $orderAmount >= (float) $this->free_shipping_minimum
        ) {
            return 0;
        }

        return (float) $this->base_fee;
    }

    public function getEstimatedDeliveryTextAttribute(): string
    {
        if (
            $this->estimated_days_min === null
            && $this->estimated_days_max === null
        ) {
            return 'Đang cập nhật';
        }

        if (
            $this->estimated_days_min === $this->estimated_days_max
            || $this->estimated_days_max === null
        ) {
            return $this->estimated_days_min . ' ngày';
        }

        return $this->estimated_days_min
            . ' - '
            . $this->estimated_days_max
            . ' ngày';
    }
}
