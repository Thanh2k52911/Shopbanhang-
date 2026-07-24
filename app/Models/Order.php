<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_code',
        'user_id',
        'coupon_id',
        'warehouse_id',
        'shipping_method_id',

        'order_status',
        'payment_status',
        'shipping_status',
        'payment_method',

        'subtotal',
        'product_discount',
        'coupon_discount',
        'shipping_fee',
        'tax_amount',
        'point_discount',
        'total_amount',
        'total_quantity',

        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_note',
        'admin_note',

        'cancel_reason',
        'cancelled_by',
        'confirmed_by',

        'ip_address',
        'user_agent',

        'confirmed_at',
        'processing_at',
        'packed_at',
        'shipping_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'product_discount' => 'decimal:2',
            'coupon_discount' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'point_discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'total_quantity' => 'integer',

            'confirmed_at' => 'datetime',
            'processing_at' => 'datetime',
            'packed_at' => 'datetime',
            'shipping_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
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

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'confirmed_by'
        );
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cancelled_by'
        );
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)
            ->where('type', 'shipping');
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)
            ->where('type', 'billing');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)
            ->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(
            ProductReview::class,
            'order_id'
        );
    }

    public function couponUsages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function contactMessages(): HasMany
{
    return $this->hasMany(ContactMessage::class);
}
    public function scopePending(Builder $query): Builder
    {
        return $query->where('order_status', 'pending');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('order_status', 'completed');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('order_status', 'cancelled');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid');
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->order_status, [
            'pending',
            'confirmed',
            'processing',
        ], true);
    }

    public function canBeReviewed(): bool
    {
        return $this->order_status === 'completed';
    }

    public function successfulPayment(): HasOne
{
    return $this->hasOne(Payment::class)
        ->where('status', 'paid')
        ->latestOfMany();
}

    public function latestShipment(): HasOne
{
    return $this->hasOne(Shipment::class)
        ->latestOfMany();
}
}
