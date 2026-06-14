<?php

namespace App\Enums;

enum AppLocale: string
{
    case English = 'en';
    case Portuguese = 'pt';
    case Spanish = 'es';
    case Italian = 'it';
    case French = 'fr';
    case German = 'de';
    case Japanese = 'ja';
    case Korean = 'ko';
    case Polish = 'pl';
    case Swedish = 'sv';
    case Arabic = 'ar';

    public static function tryFromHeader(?string $value): self
    {
        if ($value === null || $value === '') {
            return self::English;
        }

        $normalized = strtolower(substr(trim($value), 0, 2));

        return self::tryFrom($normalized) ?? self::English;
    }

    public static function forCountryCode(?string $countryCode): self
    {
        return match (strtoupper((string) $countryCode)) {
            'BR', 'PT' => self::Portuguese,
            'ES', 'MX' => self::Spanish,
            'IT' => self::Italian,
            'FR' => self::French,
            'DE' => self::German,
            'JP' => self::Japanese,
            'KR' => self::Korean,
            'PL' => self::Polish,
            'SE' => self::Swedish,
            'AE' => self::Arabic,
            default => self::English,
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
