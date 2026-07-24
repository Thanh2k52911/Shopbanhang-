<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTierReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'loyalty_account_id',
        'loyalty_tier_id',
        'coupon_id',
        'reward_type',
        'rewarded_at',
    ];

    protected function casts(): array
    {
        return [
            'rewarded_at' => 'datetime',
        ];
    }

    public function loyaltyAccount(): BelongsTo
    {
        return $this->belongsTo(
            LoyaltyAccount::class,
            'loyalty_account_id'
        );
    }

    public function loyaltyTier(): BelongsTo
    {
        return $this->belongsTo(
            LoyaltyTier::class,
            'loyalty_tier_id'
        );
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(
            Coupon::class,
            'coupon_id'
        );
    }
}
