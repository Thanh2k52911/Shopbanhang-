<?php

use App\Services\SettingService;

if (! function_exists('setting')) {
    /**
     * Lấy giá trị cấu hình theo key.
     *
     * Ví dụ:
     *
     * setting('site_name', 'Cosmetic Shop');
     */
    function setting(
        string $key,
        mixed $default = null
    ): mixed {
        return app(SettingService::class)
            ->get($key, $default);
    }
}

if (! function_exists('setting_group')) {
    /**
     * Lấy toàn bộ cấu hình theo group.
     *
     * Ví dụ:
     *
     * setting_group('social');
     */
    function setting_group(
        string $group
    ): \Illuminate\Support\Collection {
        return app(SettingService::class)
            ->group($group);
    }
}

if (! function_exists('setting_url')) {
    /**
     * Lấy URL public của setting dạng ảnh hoặc file.
     *
     * Ví dụ:
     *
     * setting_url('site_logo');
     */
    function setting_url(
        string $key,
        ?string $default = null
    ): ?string {
        return app(SettingService::class)
            ->url($key, $default);
    }
}

if (! function_exists('site_name')) {
    /**
     * Lấy tên website.
     */
    function site_name(): string
    {
        return app(SettingService::class)
            ->siteName();
    }
}

if (! function_exists('site_logo')) {
    /**
     * Lấy URL logo website.
     */
    function site_logo(
        ?string $default = null
    ): ?string {
        return app(SettingService::class)
            ->siteLogo($default);
    }
}

if (! function_exists('site_favicon')) {
    /**
     * Lấy URL favicon website.
     */
    function site_favicon(
        ?string $default = null
    ): ?string {
        return app(SettingService::class)
            ->siteFavicon($default);
    }
}

if (! function_exists('maintenance_mode_enabled')) {
    /**
     * Kiểm tra chế độ bảo trì phía khách hàng.
     */
    function maintenance_mode_enabled(): bool
    {
        return app(SettingService::class)
            ->maintenanceMode();
    }
}

if (! function_exists('setting_boolean')) {
    /**
     * Lấy một setting dạng boolean.
     */
    function setting_boolean(
        string $key,
        bool $default = false
    ): bool {
        return (bool) setting($key, $default);
    }
}

if (! function_exists('setting_number')) {
    /**
     * Lấy một setting dạng số.
     */
    function setting_number(
        string $key,
        int|float $default = 0
    ): int|float {
        $value = setting($key, $default);

        if (! is_numeric($value)) {
            return $default;
        }

        $number = (float) $value;

        return floor($number) === $number
            ? (int) $number
            : $number;
    }
}

if (! function_exists('setting_text')) {
    /**
     * Lấy setting dạng chuỗi.
     */
    function setting_text(
        string $key,
        string $default = ''
    ): string {
        $value = setting($key, $default);

        if (
            $value === null
            || is_array($value)
            || is_object($value)
        ) {
            return $default;
        }

        return (string) $value;
    }
}
