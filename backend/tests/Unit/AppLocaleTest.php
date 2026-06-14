<?php

namespace Tests\Unit;

use App\Enums\AppLocale;
use Tests\TestCase;

class AppLocaleTest extends TestCase
{
    public function test_maps_seeded_country_codes_to_locales(): void
    {
        $this->assertSame(AppLocale::German, AppLocale::forCountryCode('DE'));
        $this->assertSame(AppLocale::Japanese, AppLocale::forCountryCode('JP'));
        $this->assertSame(AppLocale::Korean, AppLocale::forCountryCode('KR'));
        $this->assertSame(AppLocale::Polish, AppLocale::forCountryCode('PL'));
        $this->assertSame(AppLocale::Swedish, AppLocale::forCountryCode('SE'));
        $this->assertSame(AppLocale::Arabic, AppLocale::forCountryCode('AE'));
        $this->assertSame(AppLocale::English, AppLocale::forCountryCode('US'));
    }
}
