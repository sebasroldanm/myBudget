<?php
namespace App\Traits;

use NumberFormatter;

trait NumberFormatterTrait
{
    public function formatCurrency(float $amount, string $currency = 'COP', string $locale = 'es_CO'): string
    {
        $amount = (float) preg_replace('/\D+/', '', $amount);
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, $currency);
    }
}