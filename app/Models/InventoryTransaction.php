<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'warehouse_id',
        'sku_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reference_id' => 'integer',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(
            ProductSku::class,
            'sku_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function scopeImports(Builder $query): Builder
    {
        return $query->where('type', 'import');
    }

    public function scopeExports(Builder $query): Builder
    {
        return $query->where('type', 'export');
    }

    public function scopeReturns(Builder $query): Builder
    {
        return $query->where('type', 'return');
    }

    public function scopeAdjustments(Builder $query): Builder
    {
        return $query->where('type', 'adjust');
    }

    public function isStockIncrease(): bool
    {
        return in_array($this->type, [
            'import',
            'return',
        ], true);
    }

    public function isStockDecrease(): bool
    {
        return in_array($this->type, [
            'export',
            'cancel',
        ], true);
    }
}
