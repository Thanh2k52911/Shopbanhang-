<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductQuestionAnswer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'question_id',
        'user_id',
        'answer',
        'is_official',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_official' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(
            ProductQuestion::class,
            'question_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeOfficial(Builder $query): Builder
    {
        return $query
            ->where('is_official', true)
            ->where('status', true);
    }
}
