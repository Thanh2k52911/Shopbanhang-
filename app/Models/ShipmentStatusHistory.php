<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentStatusHistory extends Model
{
    public const SOURCE_SYSTEM = 'system';
    public const SOURCE_ADMIN = 'admin';
    public const SOURCE_PROVIDER = 'provider';

    protected $fillable = [
        'shipment_id',
        'from_status',
        'to_status',
        'location',
        'description',
        'source',
        'created_by',
        'provider_data',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'provider_data' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ShipmentStatusHistory $history): void {
            $history->source ??= self::SOURCE_SYSTEM;
            $history->occurred_at ??= now();
        });
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeChronological(Builder $query): Builder
    {
        return $query
            ->orderBy('occurred_at')
            ->orderBy('id');
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');
    }
}
