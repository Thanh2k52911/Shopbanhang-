<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'payment_id',
        'type',
        'transaction_id',
        'amount',
        'status',
        'response_code',
        'message',
        'request_data',
        'response_data',
        'ip_address',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'request_data' => 'array',
            'response_data' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function isSuccessful(): bool
    {
        return in_array($this->status, [
            'success',
            'completed',
            'paid',
        ], true);
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
