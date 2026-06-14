<?php

namespace App\Services\ExchangeRate;

use App\Contracts\ExchangeRate\ExchangeRateServiceContract;
use App\Exceptions\ExchangeRateException;
use Illuminate\Support\Facades\Http;

/**
 * Fetches EUR → local rate at payment creation only (ExchangeRate-API v6).
 * Base currency is always EUR per Buzzvel brief. Requires EXCHANGE_RATE_API_KEY.
 */
class ExchangeRateService implements ExchangeRateServiceContract
{
    public function getRateForCurrency(string $currency): array
    {
        if ($currency === 'EUR') {
            return [
                'rate' => 1.0,
                'source' => config('services.exchange_rate.source'),
                'fetched_at' => now(),
            ];
        }

        $baseUrl = rtrim((string) config('services.exchange_rate.url'), '/');
        $apiKey = config('services.exchange_rate.key');

        if (! $apiKey) {
            throw new ExchangeRateException;
        }

        $response = Http::timeout(10)->get("{$baseUrl}/{$apiKey}/latest/EUR");

        if (! $response->successful()) {
            throw new ExchangeRateException;
        }

        $payload = $response->json();

        if (($payload['result'] ?? null) !== 'success') {
            throw new ExchangeRateException;
        }

        $rate = $payload['conversion_rates'][$currency] ?? null;

        if (! is_numeric($rate) || (float) $rate <= 0) {
            throw new ExchangeRateException('payment.unsupported_currency');
        }

        return [
            'rate' => (float) $rate,
            'source' => config('services.exchange_rate.source'),
            'fetched_at' => now(),
        ];
    }
}
