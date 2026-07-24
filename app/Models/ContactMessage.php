<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $fillable = [
        'contact_code',
        'user_id',
        'name',
        'email',
        'phone',
        'type',
        'subject',
        'message',
        'order_id',
        'status',
        'priority',
        'assigned_to',
        'admin_note',
        'replied_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', 'processing');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            'new',
            'processing',
            'replied',
        ]);
    }

    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->whereIn('priority', [
            'high',
            'urgent',
        ]);
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isAssigned(): bool
    {
        return $this->assigned_to !== null;
    }
}
