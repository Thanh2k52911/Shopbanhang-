<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'maximum_discount',
        'minimum_order_amount',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'first_order_only',
        'is_public',
        'status',
        'start_at',
        'end_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'maximum_discount' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',

            'usage_limit' => 'integer',
            'usage_limit_per_user' => 'integer',
            'used_count' => 'integer',

            'first_order_only' => 'boolean',
            'is_public' => 'boolean',
            'status' => 'boolean',

            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    public function savedByUsers(): HasMany
{
    return $this->hasMany(
        SavedCoupon::class,
        'coupon_id'
    );
}
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'coupon_products',
            'coupon_id',
            'product_id'
        );
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'coupon_categories',
            'coupon_id',
            'category_id'
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'coupon_users',
            'coupon_id',
            'user_id'
        );
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
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

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
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

        if (
            $this->usage_limit !== null
            && $this->used_count >= $this->usage_limit
        ) {
            return false;
        }

        return true;
    }

    public function hasReachedUsageLimit(): bool
    {
        return $this->usage_limit !== null
            && $this->used_count >= $this->usage_limit;
    }

    public function canBeUsedBy(?User $user): bool
    {
        if (!$this->isCurrentlyActive()) {
            return false;
        }

        if ($user === null) {
            return !$this->first_order_only
                && $this->users()->doesntExist();
        }

        if (
            $this->first_order_only
            && $user->orders()
                ->where('order_status', 'completed')
                ->exists()
        ) {
            return false;
        }

        if ($this->users()->exists()) {
            $allowed = $this->users()
                ->where('users.id', $user->id)
                ->exists();

            if (!$allowed) {
                return false;
            }
        }

        $userUsageCount = $this->usages()
            ->where('user_id', $user->id)
            ->count();

        return $userUsageCount < $this->usage_limit_per_user;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($subtotal < (float) $this->minimum_order_amount) {
            return 0;
        }

        if ($this->discount_type === 'fixed') {
            return min(
                $subtotal,
                (float) $this->discount_value
            );
        }

        if ($this->discount_type === 'percentage') {
            $discount = $subtotal
                * ((float) $this->discount_value / 100);

            if ($this->maximum_discount !== null) {
                $discount = min(
                    $discount,
                    (float) $this->maximum_discount
                );
            }

            return min($subtotal, round($discount, 2));
        }

        return 0;
    }

    public function isFreeShipping(): bool
    {
        return $this->discount_type === 'free_shipping';
    }
}
