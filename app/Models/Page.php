<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'thumbnail',
        'page_type',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'template',
        'show_in_header',
        'show_in_footer',
        'status',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'show_in_header' => 'boolean',
            'show_in_footer' => 'boolean',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeInHeader(Builder $query): Builder
    {
        return $query
            ->where('show_in_header', true)
            ->orderBy('sort_order');
    }

    public function scopeInFooter(Builder $query): Builder
    {
        return $query
            ->where('show_in_footer', true)
            ->orderBy('sort_order');
    }

    public function scopeOfType(
        Builder $query,
        string $type
    ): Builder {
        return $query->where('page_type', $type);
    }



    public function getSeoTitleAttribute(): string
    {
        return $this->meta_title ?: $this->title;
    }
}
