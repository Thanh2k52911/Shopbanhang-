<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    protected $fillable = [
        'loyalty_account_id',
        'order_id',
        'type',
        'points',
        'balance_before',
        'balance_after',
        'monetary_value',
        'status',
        'reference_type',
        'reference_id',
        'description',
        'available_at',
        'expires_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'balance_before' => 'integer',
            'balance_after' => 'integer',
            'monetary_value' => 'decimal:2',
            'reference_id' => 'integer',
            'available_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(
            LoyaltyAccount::class,
            'loyalty_account_id'
        );
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeEarned(Builder $query): Builder
    {
        return $query->where('type', 'earn');
    }

    public function scopeRedeemed(Builder $query): Builder
    {
        return $query->where('type', 'redeem');
    }

    public function isCredit(): bool
    {
        return $this->points > 0;
    }

    public function isDebit(): bool
    {
        return $this->points < 0;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast();
    }
}
