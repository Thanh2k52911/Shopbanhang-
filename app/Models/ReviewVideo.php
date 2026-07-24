<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewVideo extends Model
{
    protected $fillable = [
        'review_id',
        'video_path',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(
            ProductReview::class,
            'review_id'
        );
    }
}
