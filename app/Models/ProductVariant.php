<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'compare_price',
        'weight',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'weight' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function values(): BelongsToMany
    {
        return $this->belongsToMany(
            VariantValue::class,
            'product_variant_values',
            'variant_id',
            'value_id'
        )->withTimestamps();
    }

    public function skus(): HasMany
    {
        return $this->hasMany(
            ProductSku::class,
            'variant_id'
        );
    }
    public function orderItems(): HasMany
    {
    return $this->hasMany(
        OrderItem::class,
        'variant_id'
    );
    }

}
