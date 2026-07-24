<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecentlyViewedProduct extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'view_count',
        'last_viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'view_count' => 'integer',
            'last_viewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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

    public function scopeLatestViewed(Builder $query): Builder
    {
        return $query->orderByDesc('last_viewed_at');
    }
}
