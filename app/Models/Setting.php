<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'label',
        'description',
        'is_public',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (Setting $setting) {
            Cache::forget('setting:' . $setting->key);
            Cache::forget('settings:public');
            Cache::forget('settings:group:' . $setting->group);
        });

        static::deleted(function (Setting $setting) {
            Cache::forget('setting:' . $setting->key);
            Cache::forget('settings:public');
            Cache::forget('settings:group:' . $setting->group);
        });
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeInGroup(
        Builder $query,
        string $group
    ): Builder {
        return $query
            ->where('group', $group)
            ->orderBy('sort_order');
    }

    public function getTypedValueAttribute(): mixed
    {
        return match ($this->type) {
            'number' => is_numeric($this->value)
                ? (float) $this->value
                : 0,

            'boolean' => filter_var(
                $this->value,
                FILTER_VALIDATE_BOOLEAN
            ),

            'json' => json_decode(
                $this->value ?? '[]',
                true
            ),

            default => $this->value,
        };
    }

    public static function getValue(
        string $key,
        mixed $default = null
    ): mixed {
        return Cache::rememberForever(
            'setting:' . $key,
            function () use ($key, $default) {
                $setting = static::query()
                    ->where('key', $key)
                    ->first();

                return $setting?->typed_value ?? $default;
            }
        );
    }

    public static function setValue(
        string $key,
        mixed $value,
        string $type = 'string',
        string $group = 'general'
    ): self {
        if (is_array($value)) {
            $value = json_encode($value);
            $type = 'json';
        }

        $setting = static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
            ]
        );

        Cache::forget('setting:' . $key);

        return $setting;
    }
}
