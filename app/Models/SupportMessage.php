<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'is_read_by_customer',
        'is_read_by_shop',
    ];

    protected $casts = [
        'is_read_by_customer' => 'boolean',
        'is_read_by_shop' => 'boolean',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(
            SupportConversation::class,
            'conversation_id'
        );
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'sender_id'
        );
    }

    public function getIsShopMessageAttribute(): bool
    {
        return (bool) $this->sender?->roles
            ?->contains(
                fn ($role): bool => in_array(
                    $role->name,
                    ['admin', 'super_admin'],
                    true
                )
            );
    }
}
