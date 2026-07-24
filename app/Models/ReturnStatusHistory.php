<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnStatusHistory extends Model
{
    public const SOURCE_CUSTOMER = 'customer';
    public const SOURCE_ADMIN = 'admin';
    public const SOURCE_SYSTEM = 'system';
    public const SOURCE_SHIPPING_PROVIDER = 'shipping_provider';

    protected $fillable = [
        'return_request_id',
        'from_status',
        'to_status',
        'note',
        'source',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (ReturnStatusHistory $history): void {
            if (blank($history->source)) {
                $history->source = self::SOURCE_SYSTEM;
            }
        });
    }

    public function returnRequest(): BelongsTo
    {
        return $this->belongsTo(ReturnRequest::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeChronological(Builder $query): Builder
    {
        return $query->orderBy('created_at')->orderBy('id');
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }
}
