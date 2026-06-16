<?php

namespace App\Support;

class Money
{
    public static function format(float|int|string|null $amount, ?string $currency = null): string
    {
        $currency = $currency ?: AppSettings::defaultCurrency();
        $value = number_format((float) $amount, 2, ',', '.');

        return $value.' '.$currency;
    }

    public static function minutesToHuman(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return sprintf('%dh %02dmin', $h, $m);
    }
}
