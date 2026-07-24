<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'title',
        'subtitle',
        'desktop_image',
        'mobile_image',
        'link_url',
        'button_text',
        'position',
        'target',
        'sort_order',
        'status',
        'start_at',
        'end_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'status' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', true)
            ->where(function (Builder $query) {
                $query
                    ->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query
                    ->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            });
    }

    public function scopeAtPosition(
        Builder $query,
        string $position
    ): Builder {
        return $query
            ->where('position', $position)
            ->orderBy('sort_order');
    }

    public function isCurrentlyActive(): bool
    {
        if (!$this->status) {
            return false;
        }

        if ($this->start_at && $this->start_at->isFuture()) {
            return false;
        }

        if ($this->end_at && $this->end_at->isPast()) {
            return false;
        }

        return true;
    }

    public function getDisplayImageAttribute(): string
    {
        return $this->mobile_image ?: $this->desktop_image;
    }
}
