<?php

namespace App\Support;

/**
 * Whitelisted country → currency mappings for employee registration.
 * Keeps country/currency consistent and prevents invalid ISO combinations.
 * Matches the countries represented in UserSeeder.
 */
class EmployeeCountryProfiles
{
    /** @var array<string, array{country: string, country_code: string, currency: string}> */
    private const PROFILES = [
        'BR' => ['country' => 'Brazil', 'country_code' => 'BR', 'currency' => 'BRL'],
        'US' => ['country' => 'United States', 'country_code' => 'US', 'currency' => 'USD'],
        'GB' => ['country' => 'United Kingdom', 'country_code' => 'GB', 'currency' => 'GBP'],
        'JP' => ['country' => 'Japan', 'country_code' => 'JP', 'currency' => 'JPY'],
        'PT' => ['country' => 'Portugal', 'country_code' => 'PT', 'currency' => 'EUR'],
        'DE' => ['country' => 'Germany', 'country_code' => 'DE', 'currency' => 'EUR'],
        'FR' => ['country' => 'France', 'country_code' => 'FR', 'currency' => 'EUR'],
        'IE' => ['country' => 'Ireland', 'country_code' => 'IE', 'currency' => 'EUR'],
        'IN' => ['country' => 'India', 'country_code' => 'IN', 'currency' => 'INR'],
        'KR' => ['country' => 'South Korea', 'country_code' => 'KR', 'currency' => 'KRW'],
        'ES' => ['country' => 'Spain', 'country_code' => 'ES', 'currency' => 'EUR'],
        'IT' => ['country' => 'Italy', 'country_code' => 'IT', 'currency' => 'EUR'],
        'PL' => ['country' => 'Poland', 'country_code' => 'PL', 'currency' => 'PLN'],
        'CA' => ['country' => 'Canada', 'country_code' => 'CA', 'currency' => 'CAD'],
        'AU' => ['country' => 'Australia', 'country_code' => 'AU', 'currency' => 'AUD'],
        'MX' => ['country' => 'Mexico', 'country_code' => 'MX', 'currency' => 'MXN'],
        'AE' => ['country' => 'United Arab Emirates', 'country_code' => 'AE', 'currency' => 'AED'],
        'SE' => ['country' => 'Sweden', 'country_code' => 'SE', 'currency' => 'SEK'],
        'ZA' => ['country' => 'South Africa', 'country_code' => 'ZA', 'currency' => 'ZAR'],
        'SG' => ['country' => 'Singapore', 'country_code' => 'SG', 'currency' => 'SGD'],
    ];

    /**
     * @return array{country: string, country_code: string, currency: string}|null
     */
    public static function find(string $countryCode): ?array
    {
        return self::PROFILES[strtoupper($countryCode)] ?? null;
    }

    /** @return list<string> */
    public static function codes(): array
    {
        return array_keys(self::PROFILES);
    }

    /**
     * @return list<array{country: string, country_code: string, currency: string}>
     */
    public static function all(): array
    {
        return array_values(self::PROFILES);
    }
}
