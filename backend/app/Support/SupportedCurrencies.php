<?php

namespace App\Support;

/**
 * ISO currency codes supported for payment requests and exchange-rate lookup.
 * Kept in sync with frontend REFERENCE_RATES / CURRENCY_META.
 */
final class SupportedCurrencies
{
    public const CODES = [
        'EUR',
        'BRL',
        'USD',
        'GBP',
        'JPY',
        'INR',
        'KRW',
        'PLN',
        'CAD',
        'AUD',
        'MXN',
        'AED',
        'SEK',
        'ZAR',
        'SGD',
    ];

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return self::CODES;
    }
}
