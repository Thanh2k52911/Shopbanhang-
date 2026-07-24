<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'brand_id',
        'supplier_id',
        'name',
        'slug',
        'short_description',
        'description',
        'ingredient',
        'usage',
        'skin_type',
        'origin',
        'status',
        'is_featured',
        'view_count',
        'created_by',
        'updated_by',
    ];
    public function favorites(): HasMany
{
    return $this->hasMany(
        ProductFavorite::class,
        'product_id'
    );
}
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'is_featured' => 'boolean',
            'view_count' => 'integer',
        ];
    }


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->orderBy('sort_order');
    }

    public function thumbnail(): HasOne
    {
        return $this->hasOne(ProductImage::class)
            ->where('is_thumbnail', true);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(ProductVideo::class)
            ->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function skus(): HasMany
    {
        return $this->hasMany(ProductSku::class);
    }

    public function statistic(): HasOne
    {
        return $this->hasOne(ProductStatistic::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)
            ->where('status', true);
    }

    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'product_favorites',
            'product_id',
            'user_id'
        )->withTimestamps();
    }

    public function discountCampaigns(): BelongsToMany
    {
        return $this->belongsToMany(
            DiscountCampaign::class,
            'product_discounts',
            'product_id',
            'campaign_id'
        )
            ->withPivot([
                'discount_percent',
                'discount_amount',
                'limit_quantity',
                'sold_quantity',
            ])
            ->withTimestamps();
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(
            Coupon::class,
            'coupon_products',
            'product_id',
            'coupon_id'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function orderItems(): HasMany
    {
    return $this->hasMany(OrderItem::class);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function productDiscounts(): HasMany
    {
    return $this->hasMany(ProductDiscount::class);
    }

    public function getAverageRatingAttribute(): float
    {
    return round(
        (float) $this->approvedReviews()->avg('rating'),
        1
    );
    }

    public function getReviewsCountAttribute(): int
    {
    return $this->approvedReviews()->count();
    }

    public function recentlyViewedRecords(): HasMany
{
    return $this->hasMany(
        RecentlyViewedProduct::class
    );
}

public function questions(): HasMany
{
    return $this->hasMany(ProductQuestion::class);
}

public function publishedQuestions(): HasMany
{
    return $this->hasMany(ProductQuestion::class)
        ->whereIn('status', ['published', 'answered'])
        ->where('is_public', true);
}
}
