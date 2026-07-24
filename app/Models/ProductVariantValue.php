<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantValue extends Model
{
    protected $fillable = [
        'variant_id',
        'value_id',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariant::class,
            'variant_id'
        );
    }

    public function value(): BelongsTo
    {
        return $this->belongsTo(
            VariantValue::class,
            'value_id'
        );
    }
}
