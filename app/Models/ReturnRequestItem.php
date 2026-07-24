<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnRequestItem extends Model
{
    protected $fillable = [
        'return_request_id',
        'order_item_id',
        'quantity',
        'reason',
        'description',
        'product_condition',
        'requested_refund_amount',
        'approved_refund_amount',
        'inspection_result',
        'inspection_note',
        'inventory_action',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'requested_refund_amount' => 'decimal:2',
            'approved_refund_amount' => 'decimal:2',
        ];
    }

    public function returnRequest(): BelongsTo
    {
        return $this->belongsTo(ReturnRequest::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ReturnRequestImage::class);
    }

    public function isApproved(): bool
    {
        return (float) $this->approved_refund_amount > 0;
    }

    public function canRestock(): bool
    {
        return $this->inventory_action === 'restock';
    }
}
