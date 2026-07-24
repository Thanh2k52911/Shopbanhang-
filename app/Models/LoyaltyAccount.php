<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyAccount extends Model
{
    protected $fillable = [
        'user_id',
        'tier_id',
        'highest_tier_id',
        'available_points',
        'pending_points',
        'lifetime_earned_points',
        'lifetime_redeemed_points',
        'lifetime_spending',
        'tier_started_at',
        'tier_expires_at',
        'last_completed_order_at',
        'inactive_downgraded_at',
    ];

    protected function casts(): array
    {
        return [
            'available_points' => 'integer',
            'pending_points' => 'integer',
            'lifetime_earned_points' => 'integer',
            'lifetime_redeemed_points' => 'integer',
            'lifetime_spending' => 'decimal:2',
            'tier_started_at' => 'datetime',
            'tier_expires_at' => 'datetime',
            'last_completed_order_at' => 'datetime',
            'inactive_downgraded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(
            LoyaltyTier::class,
            'tier_id'
        );
    }

    public function highestTier(): BelongsTo
    {
        return $this->belongsTo(
            LoyaltyTier::class,
            'highest_tier_id'
        );
    }

    public function tierRewards(): HasMany
    {
        return $this->hasMany(
            LoyaltyTierReward::class,
            'loyalty_account_id'
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(
            LoyaltyTransaction::class,
            'loyalty_account_id'
        )->latest();
    }

    public function completedTransactions(): HasMany
    {
        return $this->hasMany(
            LoyaltyTransaction::class,
            'loyalty_account_id'
        )->where('status', 'completed');
    }

    public function hasEnoughPoints(int $points): bool
    {
        return $points > 0
            && $this->available_points >= $points;
    }

    public function getTotalPointsAttribute(): int
    {
        return $this->available_points
            + $this->pending_points;
    }

    public function getIsTierExpiredAttribute(): bool
    {
        return $this->tier_expires_at !== null
            && $this->tier_expires_at->isPast();
    }
}
