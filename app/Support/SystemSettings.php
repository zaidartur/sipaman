<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SystemSettings
{
    private const CACHE_KEY = 'system_settings.public';
    private const DEFAULT_PAGINATION = 12;
    private const MIN_PAGINATION = 3;
    private const MAX_PAGINATION = 100;

    public static function all(): array
    {
        try {
            if (! Schema::hasTable('system_settings')) {
                return [];
            }
        } catch (\Throwable) {
            return [];
        }

        try {
            return Cache::rememberForever(self::CACHE_KEY, function () {
                return self::fresh();
            });
        } catch (\Throwable) {
            return self::fresh();
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $settings = self::all();

        return ($settings[$key] ?? null) ?: $default;
    }

    public static function pagination(mixed $value = null): int
    {
        return self::normalizePagination($value ?? self::get('default_pagination'));
    }

    public static function forget(): void
    {
        try {
            Cache::forget(self::CACHE_KEY);
        } catch (\Throwable) {
        }
    }

    private static function fresh(): array
    {
        try {
            return SystemSetting::query()
                ->pluck('value', 'key')
                ->map(fn ($value) => is_string($value) ? trim($value) : $value)
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private static function normalizePagination(mixed $value): int
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            $number = (int) $value;

            if ($number >= self::MIN_PAGINATION && $number <= self::MAX_PAGINATION) {
                return $number;
            }
        }

        return self::DEFAULT_PAGINATION;
    }
}
