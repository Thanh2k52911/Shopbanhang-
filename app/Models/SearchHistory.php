<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'keyword',
        'filters',
        'result_count',
        'clicked_product_id',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'result_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clickedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'clicked_product_id');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForKeyword(Builder $query, string $keyword): Builder
    {
        return $query->where('keyword', 'like', '%' . $keyword . '%');
    }

    public function scopeWithResults(Builder $query): Builder
    {
        return $query->where('result_count', '>', 0);
    }

    public function scopeWithoutResults(Builder $query): Builder
    {
        return $query->where('result_count', 0);
    }

    public function scopeLatestSearches(Builder $query): Builder
    {
        return $query->latest('created_at')->latest('id');
    }
}
