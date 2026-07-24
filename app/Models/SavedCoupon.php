<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedCoupon extends Model
{
    protected $fillable = [
        'user_id',
        'coupon_id',
        'saved_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'coupon_id' => 'integer',
            'saved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
