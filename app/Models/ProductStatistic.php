<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStatistic extends Model
{
    protected $fillable = [
        'product_id',
        'views',
        'favorites',
        'orders',
        'sold_quantity',
        'revenue',
    ];

    protected function casts(): array
    {
        return [
            'views' => 'integer',
            'favorites' => 'integer',
            'orders' => 'integer',
            'sold_quantity' => 'integer',
            'revenue' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
