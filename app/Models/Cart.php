<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser(
        Builder $query,
        int $userId
    ): Builder {
        return $query->where('user_id', $userId);
    }

    public function scopeForSession(
        Builder $query,
        string $sessionId
    ): Builder {
        return $query->where('session_id', $sessionId);
    }

    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(
            fn (CartItem $item) => $item->subtotal
        );
    }

    public function getDiscountTotalAttribute(): float
    {
        return (float) $this->items->sum(
            fn (CartItem $item) =>
                (float) $item->discount_amount * $item->quantity
        );
    }

    public function getTotalAttribute(): float
    {
        return max(
            0,
            $this->subtotal - $this->discount_total
        );
    }
}
