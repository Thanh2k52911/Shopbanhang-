<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoyaltyTier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'minimum_spending',
        'minimum_points',
        'point_multiplier',
        'discount_percent',
        'reward_enabled',
        'reward_name',
        'reward_description',
        'reward_discount_type',
        'reward_discount_value',
        'reward_maximum_discount',
        'reward_minimum_order_amount',
        'reward_valid_days',
        'color',
        'icon',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'minimum_spending' => 'decimal:2',
            'minimum_points' => 'integer',
            'point_multiplier' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'reward_enabled' => 'boolean',
            'reward_discount_value' => 'decimal:2',
            'reward_maximum_discount' => 'decimal:2',
            'reward_minimum_order_amount' => 'decimal:2',
            'reward_valid_days' => 'integer',
            'sort_order' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(
            LoyaltyTierReward::class,
            'loyalty_tier_id'
        );
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(
            LoyaltyAccount::class,
            'tier_id'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', true)
            ->orderBy('sort_order');
    }

    public function qualifies(
        float $spending,
        int $points
    ): bool {
        return $spending >= (float) $this->minimum_spending
            && $points >= $this->minimum_points;
    }
}
