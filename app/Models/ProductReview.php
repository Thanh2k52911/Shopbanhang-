<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductReview extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'content',
        'verified_purchase',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'verified_purchase' => 'boolean',
            'status' => 'boolean',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(
            ReviewImage::class,
            'review_id'
        );
    }

    public function videos(): HasMany
    {
        return $this->hasMany(
            ReviewVideo::class,
            'review_id'
        );
    }

    public function likes(): HasMany
    {
        return $this->hasMany(
            ReviewLike::class,
            'review_id'
        );
    }

    public function replies(): HasMany
    {
        return $this->hasMany(
            ReviewReply::class,
            'review_id'
        )->oldest();
    }

    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'review_likes',
            'review_id',
            'user_id'
        )->withTimestamps();
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', false);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verified_purchase', true);
    }

    public function scopeWithRating(
        Builder $query,
        int $rating
    ): Builder {
        return $query->where('rating', $rating);
    }

    public function getLikesCountAttribute(): int
    {
        return $this->likes_count
            ?? $this->likes()->count();
    }

    public function isLikedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->likes()
            ->where('user_id', $user->id)
            ->exists();
    }
}
