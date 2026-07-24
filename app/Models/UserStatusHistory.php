<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStatusHistory extends Model
{
    protected $fillable = [
        'user_id',
        'old_status',
        'new_status',
        'reason',
        'created_by',
    ];

    /**
     * Tài khoản bị thay đổi trạng thái.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    /**
     * Quản trị viên thực hiện thay đổi.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}
