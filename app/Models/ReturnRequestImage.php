<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequestImage extends Model
{
    protected $fillable = [
        'return_request_id',
        'return_request_item_id',
        'image_path',
        'caption',
        'uploaded_by_type',
        'uploaded_by',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function returnRequest(): BelongsTo
    {
        return $this->belongsTo(ReturnRequest::class);
    }

    public function returnRequestItem(): BelongsTo
    {
        return $this->belongsTo(ReturnRequestItem::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    public function isUploadedByCustomer(): bool
    {
        return $this->uploaded_by_type === 'customer';
    }

    public function isUploadedByAdmin(): bool
    {
        return $this->uploaded_by_type === 'admin';
    }
}
