<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddress extends Model
{
    protected $fillable = [
        'order_id',
        'type',
        'receiver_name',
        'phone',
        'email',
        'province',
        'district',
        'ward',
        'address',
        'full_address',
        'note',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getFormattedAddressAttribute(): string
    {
        if (!empty($this->full_address)) {
            return $this->full_address;
        }

        return implode(', ', array_filter([
            $this->address,
            $this->ward,
            $this->district,
            $this->province,
        ]));
    }
}
