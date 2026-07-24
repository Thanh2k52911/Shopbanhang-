<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'route_name',
        'url',
        'request_method',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAction(Builder $query, ?string $action): Builder
    {
        return $action
            ? $query->where('action', $action)
            : $query;
    }

    public function scopeAuditableType(
        Builder $query,
        ?string $auditableType
    ): Builder {
        return $auditableType
            ? $query->where('auditable_type', $auditableType)
            : $query;
    }

    public function scopeForUser(Builder $query, ?int $userId): Builder
    {
        return $userId
            ? $query->where('user_id', $userId)
            : $query;
    }

    public function scopeLatestLogs(Builder $query): Builder
    {
        return $query
            ->latest('created_at')
            ->latest('id');
    }

    public function getAuditableNameAttribute(): string
    {
        if (! $this->auditable_type) {
            return 'Hệ thống';
        }

        return class_basename($this->auditable_type);
    }
}
