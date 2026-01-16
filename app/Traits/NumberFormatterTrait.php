<?php

namespace App\Traits;

use NumberFormatter;

trait NumberFormatterTrait
{
    public static function formatCurrency(float|string $amount, string $currency = 'COP', string $locale = 'es_CO'): string
    {
        if (is_string($amount)) {
            $amount = str_replace(',', '.', $amount);
            $amount = preg_replace('/[^0-9.]/', '', $amount);
            if (substr_count($amount, '.') > 1) {
                $parts = explode('.', $amount);
                $last = array_pop($parts);
                $amount = implode('', $parts) . '.' . $last;
            }
        }

        $amount = (float) $amount;

        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($amount, $currency);
    }
}
