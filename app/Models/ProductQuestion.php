<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductQuestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'user_id',
        'guest_name',
        'guest_email',
        'question',
        'status',
        'is_public',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(
            ProductQuestionAnswer::class,
            'question_id'
        )->oldest();
    }

    public function officialAnswers(): HasMany
    {
        return $this->hasMany(
            ProductQuestionAnswer::class,
            'question_id'
        )
            ->where('is_official', true)
            ->where('status', true)
            ->oldest();
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereIn('status', ['published', 'answered'])
            ->where('is_public', true);
    }

    public function scopeAnswered(Builder $query): Builder
    {
        return $query->where('status', 'answered');
    }

    public function getAuthorNameAttribute(): string
    {
        return $this->user?->name
            ?? $this->guest_name
            ?? 'Khách hàng';
    }

    public function isAnswered(): bool
    {
        return $this->status === 'answered'
            || $this->answers()->exists();
    }
}
