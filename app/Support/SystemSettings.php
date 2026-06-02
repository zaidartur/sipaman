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
    private const DEFAULT_PIRT_EXPIRY_WARNING_DAYS = [30, 14, 7];
    private const DEFAULT_PIRT_EXPIRY_NOTIFICATION_TIME = '08:00';
    private const DEFAULT_PIRT_EXPIRY_MESSAGE_TEMPLATE = 'Yth. {nama_pelaku_usaha}, masa berlaku PIRT produk {nama_produk} (No. SPPIRT {no_sppirt}) akan berakhir pada {masa_berlaku_pirt}. Mohon siapkan pembaruan sesuai ketentuan.';

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

    public static function pirtExpiryNotificationsEnabled(): bool
    {
        return self::get('pirt_expiry_notification_enabled', '0') === '1';
    }

    public static function pirtExpiryWarningDays(): array
    {
        $days = collect(preg_split('/[\s,]+/', (string) self::get('pirt_expiry_warning_days', ''), -1, PREG_SPLIT_NO_EMPTY) ?: [])
            ->map(fn (string $day) => ctype_digit($day) ? (int) $day : null)
            ->filter(fn (?int $day) => $day !== null && $day >= 1 && $day <= 365)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return $days === [] ? self::DEFAULT_PIRT_EXPIRY_WARNING_DAYS : $days;
    }

    public static function pirtExpiryNotificationTime(): string
    {
        $time = self::get('pirt_expiry_notification_time', self::DEFAULT_PIRT_EXPIRY_NOTIFICATION_TIME);

        return is_string($time) && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)
            ? $time
            : self::DEFAULT_PIRT_EXPIRY_NOTIFICATION_TIME;
    }

    public static function pirtExpiryMessageTemplate(): string
    {
        return self::get('pirt_expiry_message_template', self::DEFAULT_PIRT_EXPIRY_MESSAGE_TEMPLATE)
            ?: self::DEFAULT_PIRT_EXPIRY_MESSAGE_TEMPLATE;
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
