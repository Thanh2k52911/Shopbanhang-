<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'status_type',
        'note',
        'created_by',
        'source',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'created_by' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OrderStatusHistory $history): void {
            if (blank($history->status_type)) {
                $history->status_type = 'order';
            }

            if (blank($history->source)) {
                $history->source = 'system';
            }

            if (blank($history->occurred_at)) {
                $history->occurred_at = now();
            }
        });
    }

    public function scopeChronological(Builder $query): Builder
    {
        return $query
            ->orderBy('occurred_at')
            ->orderBy('id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
