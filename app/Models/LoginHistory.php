<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'session_id',
        'ip_address',
        'user_agent',
        'device',
        'browser',
        'platform',
        'country',
        'city',
        'is_success',
        'failure_reason',
        'logged_in_at',
        'logged_out_at',
    ];

    protected function casts(): array
    {
        return [
            'is_success' => 'boolean',
            'logged_in_at' => 'datetime',
            'logged_out_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('is_success', true);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('is_success', false);
    }

    public function scopeLatestLogin(Builder $query): Builder
    {
        return $query->orderByDesc('logged_in_at')->orderByDesc('id');
    }

    public function isOnline(): bool
    {
        return $this->is_success
            && $this->logged_out_at === null;
    }
}
