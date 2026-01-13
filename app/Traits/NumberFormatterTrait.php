<?php
namespace App\Traits;

use NumberFormatter;

trait NumberFormatterTrait
{
    public function formatCurrency(float $amount, string $currency = 'COP', string $locale = 'es_CO'): string
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, $currency);
    }
}