<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Exception;

class ExchangeService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Convert an amount from one currency to another.
     *
     * @param string $from The currency to convert from.
     * @param string $to The currency to convert to.
     * @param float $amount The amount to convert.
     * @param string $type The type of conversion to perform.
     * @return float|string The converted amount.
     * @throws Exception If no exchange rate is available for the given currencies.
     */
    public function convert($from, $to, $amount, $type = null): float|string
    {
        if ($from === $to) {
            if ($type === 'withSymbol') {
                return (string) '$' . number_format($amount, 2) . ' ' . $to;
            }
            return (float) $amount;
        }

        $rate = ExchangeRate::where('from_currency', $from)
            ->where('to_currency', $to)
            ->latest('retrieved_at')
            ->value('rate');

        if (!$rate) {
            throw new Exception("No existe tasa de conversión disponible de $from a $to");
        }

        $rate = (float) $rate;
        $amount = $amount ? (float) str_replace(',', '', $amount) : 0;

        $convertedAmount = bcmul($amount, $rate, 2);

        if ($type === 'withSymbol') {
            return (string) '$' . number_format($convertedAmount, 2) . ' ' . $to;
        }

        return (float) $convertedAmount;
    }

}
