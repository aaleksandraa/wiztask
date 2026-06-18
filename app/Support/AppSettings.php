<?php

namespace App\Support;

use App\Models\Setting;

class AppSettings
{
    public static function appName(): string
    {
        return Setting::get('app_name', config('app.name', 'WizTask'));
    }

    public static function defaultCurrency(): string
    {
        return Setting::get('default_currency', 'KM');
    }

    public static function defaultHourlyRate(): float
    {
        return (float) Setting::get('default_hourly_rate', 40);
    }

    /**
     * @return array<int, string>
     */
    public static function allowedFileTypes(): array
    {
        $raw = Setting::get('allowed_file_types', '*');

        return array_values(array_filter(array_map('trim', explode(',', (string) $raw))));
    }

    public static function allowsAllFileTypes(): bool
    {
        return in_array('*', self::allowedFileTypes(), true);
    }

    public static function isAllowedExtension(string $extension): bool
    {
        if (self::allowsAllFileTypes()) {
            return true;
        }

        return in_array(strtolower($extension), self::allowedFileTypes(), true);
    }
}
