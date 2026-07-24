<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    /**
     * Thời gian cache cấu hình.
     */
    private const CACHE_TTL_SECONDS = 3600;

    /**
     * Cache key chứa toàn bộ settings.
     */
    private const CACHE_KEY_ALL = 'settings.all';

    /**
     * Lấy toàn bộ cấu hình dưới dạng:
     *
     * [
     *     'site_name' => 'Cosmetic Shop',
     *     'maintenance_mode' => false,
     *     ...
     * ]
     */
    public function all(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY_ALL,
            self::CACHE_TTL_SECONDS,
            function (): Collection {
                return DB::table('settings')
                    ->orderBy('group')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get([
                        'key',
                        'value',
                        'type',
                    ])
                    ->mapWithKeys(
                        function (object $setting): array {
                            return [
                                $setting->key => $this->castValue(
                                    $setting->value,
                                    $setting->type
                                ),
                            ];
                        }
                    );
            }
        );
    }

    /**
     * Lấy một cấu hình theo key.
     */
    public function get(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->all()->get(
            $key,
            $default
        );
    }

    /**
     * Kiểm tra setting có tồn tại hay không.
     */
    public function has(string $key): bool
    {
        return $this->all()->has($key);
    }

    /**
     * Lấy cấu hình theo group.
     *
     * Kết quả:
     *
     * [
     *     'site_name' => 'Cosmetic Shop',
     *     'site_logo' => 'settings/images/logo.png',
     * ]
     */
    public function group(string $group): Collection
    {
        return Cache::remember(
            'settings.group.' . $group,
            self::CACHE_TTL_SECONDS,
            function () use ($group): Collection {
                return DB::table('settings')
                    ->where('group', $group)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get([
                        'key',
                        'value',
                        'type',
                    ])
                    ->mapWithKeys(
                        function (object $setting): array {
                            return [
                                $setting->key => $this->castValue(
                                    $setting->value,
                                    $setting->type
                                ),
                            ];
                        }
                    );
            }
        );
    }

    /**
     * Lấy URL public của ảnh/file trong settings.
     *
     * Ví dụ:
     * setting_url('site_logo')
     */
    public function url(
        string $key,
        ?string $default = null
    ): ?string {
        $value = $this->get($key);

        if (
            $value === null
            || $value === ''
        ) {
            return $default;
        }

        if (
            is_string($value)
            && (
                str_starts_with($value, 'http://')
                || str_starts_with($value, 'https://')
                || str_starts_with($value, '//')
            )
        ) {
            return $value;
        }

        return Storage::disk('public')
            ->url(
                ltrim((string) $value, '/')
            );
    }

    /**
     * Lấy tên website.
     */
    public function siteName(): string
    {
        return (string) $this->get(
            'site_name',
            config('app.name', 'Cosmetic Shop')
        );
    }

    /**
     * Lấy logo website.
     */
    public function siteLogo(
        ?string $default = null
    ): ?string {
        return $this->url(
            'site_logo',
            $default
        );
    }

    /**
     * Lấy favicon website.
     */
    public function siteFavicon(
        ?string $default = null
    ): ?string {
        return $this->url(
            'site_favicon',
            $default
        );
    }

    /**
     * Kiểm tra chế độ bảo trì phía client.
     */
    public function maintenanceMode(): bool
    {
        return (bool) $this->get(
            'maintenance_mode',
            false
        );
    }

    /**
     * Xóa toàn bộ cache settings.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ALL);

        DB::table('settings')
            ->select('group')
            ->distinct()
            ->pluck('group')
            ->each(
                function (string $group): void {
                    Cache::forget(
                        'settings.group.' . $group
                    );
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Xóa thêm các cache key cũ từng được sử dụng trong dự án
        |--------------------------------------------------------------------------
        */

        Cache::forget('settings');
        Cache::forget('settings.public');
        Cache::forget('public_settings');
        Cache::forget('site_settings');
    }

    /**
     * Ép kiểu value dựa theo type trong database.
     */
    private function castValue(
        mixed $value,
        ?string $type
    ): mixed {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var(
                $value,
                FILTER_VALIDATE_BOOLEAN
            ),

            'number' => $this->castNumber(
                $value
            ),

            'json' => $this->decodeJson(
                $value
            ),

            default => $value,
        };
    }

    /**
     * Ép kiểu số nhưng vẫn giữ int nếu không có phần thập phân.
     */
    private function castNumber(
        mixed $value
    ): int|float {
        $number = (float) $value;

        return floor($number) === $number
            ? (int) $number
            : $number;
    }

    /**
     * Decode JSON an toàn.
     */
    private function decodeJson(
        mixed $value
    ): mixed {
        if (! is_string($value)) {
            return $value;
        }

        $decoded = json_decode(
            $value,
            true
        );

        return json_last_error() === JSON_ERROR_NONE
            ? $decoded
            : $value;
    }
}
