<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnRequest extends Model
{
    public const TYPE_RETURN = 'return';
    public const TYPE_EXCHANGE = 'exchange';
    public const TYPE_REFUND = 'refund';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_WAITING_FOR_RETURN = 'waiting_for_return';
    public const STATUS_RETURNING = 'returning';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_INSPECTING = 'inspecting';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'return_code',
        'order_id',
        'user_id',
        'request_type',
        'status',
        'reason',
        'description',
        'requested_amount',
        'approved_amount',
        'return_shipping_fee',
        'shipping_fee_payer',
        'customer_note',
        'admin_note',
        'rejection_reason',
        'processed_by',
        'approved_at',
        'rejected_at',
        'received_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'return_shipping_fee' => 'decimal:2',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'received_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnRequestItem::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ReturnRequestImage::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ReturnStatusHistory::class)
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function scopeWithStatus(Builder $query, string|array $statuses): Builder
    {
        $statuses = is_array($statuses) ? $statuses : [$statuses];

        return $query->whereIn('status', $statuses);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeWaitingForReturn(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_WAITING_FOR_RETURN);
    }

    public function scopeReturning(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RETURNING);
    }

    public function scopeReceived(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RECEIVED);
    }

    public function scopeInspecting(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_INSPECTING);
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            self::STATUS_COMPLETED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
        ]);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_WAITING_FOR_RETURN,
        ], true);
    }

    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeRejected(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
        ], true);
    }

    public function getTotalItemQuantityAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
