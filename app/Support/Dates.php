<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class Dates
{
    public const DISPLAY = 'd.m.Y';

    public const DISPLAY_DATETIME = 'd.m.Y H:i';

    public const INPUT = 'd.m.Y';

    public const DB = 'Y-m-d';

    public static function format(null|string|CarbonInterface $date, bool $withTime = false): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        $carbon = $date instanceof CarbonInterface
            ? $date
            : Carbon::parse($date);

        return $carbon->format($withTime ? self::DISPLAY_DATETIME : self::DISPLAY);
    }

    public static function formatOr(null|string|CarbonInterface $date, string $fallback = '-', bool $withTime = false): string
    {
        return self::format($date, $withTime) ?? $fallback;
    }

    public static function today(): string
    {
        return now()->format(self::INPUT);
    }

    /** d.m.Y → Y-m-d za bazu */
    public static function toDatabase(null|string $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $value = trim((string) $value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        return Carbon::createFromFormat(self::INPUT, $value)->format(self::DB);
    }

    /** Y-m-d ili Carbon → d.m.Y za forme */
    public static function toInput(null|string|CarbonInterface $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::format($value);
    }

    /** @return array<int, string> */
    public static function rule(bool $required = false): array
    {
        return $required
            ? ['required', 'date_format:'.self::INPUT]
            : ['nullable', 'date_format:'.self::INPUT];
    }

    /** @param  array<int, string>  $fields */
    public static function fillForSave(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = self::toDatabase($data[$field]);
            }
        }

        return $data;
    }
}
