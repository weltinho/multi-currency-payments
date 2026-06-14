<?php

namespace Tests\Unit;

use App\Services\ExchangeRate\ExchangeRateService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeRateServiceTest extends TestCase
{
    public function test_it_returns_eur_rate_of_one(): void
    {
        config([
            'services.exchange_rate.source' => 'exchangerate-api.com',
        ]);

        $service = new ExchangeRateService;
        $result = $service->getRateForCurrency('EUR');

        $this->assertSame(1.0, $result['rate']);
        $this->assertSame('exchangerate-api.com', $result['source']);
    }

    public function test_it_fetches_rate_from_v6_api(): void
    {
        config([
            'services.exchange_rate.url' => 'https://v6.exchangerate-api.com/v6',
            'services.exchange_rate.key' => 'test-key',
            'services.exchange_rate.source' => 'exchangerate-api.com',
        ]);

        Http::fake([
            'https://v6.exchangerate-api.com/v6/test-key/latest/EUR' => Http::response([
                'result' => 'success',
                'base_code' => 'EUR',
                'conversion_rates' => [
                    'BRL' => 6.21,
                ],
            ]),
        ]);

        $service = new ExchangeRateService;
        $result = $service->getRateForCurrency('BRL');

        $this->assertSame(6.21, $result['rate']);
        $this->assertSame('exchangerate-api.com', $result['source']);
    }

    public function test_it_computes_eur_amount_from_local_amount(): void
    {
        $localAmount = 4200.0;
        $rate = 6.21;

        $this->assertSame(676.33, round($localAmount / $rate, 2));
    }
}
