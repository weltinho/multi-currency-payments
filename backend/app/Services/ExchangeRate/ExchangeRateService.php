<?php

namespace App\Services\ExchangeRate;

use App\Contracts\ExchangeRate\ExchangeRateServiceContract;
use App\Exceptions\ExchangeRateException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Fetches EUR → local rate at payment creation only (ExchangeRate-API v6).
 * Base currency is always EUR per Buzzvel brief. Requires EXCHANGE_RATE_API_KEY.
 *
 * Live rates are cached in Redis for a short TTL so concurrent payment requests
 * reuse one upstream call — caching for not overflowing rate limit of exchange rate API.
 */
class ExchangeRateService implements ExchangeRateServiceContract
{
    private const CACHE_KEY = 'exchange_rates:eur:latest';

    public function getRateForCurrency(string $currency): array
    {
        if ($currency === 'EUR') {
            return [
                'rate' => 1.0,
                'source' => config('services.exchange_rate.source'),
                'fetched_at' => now(),
            ];
        }

        $snapshot = $this->latestRatesSnapshot();
        $rate = $snapshot['conversion_rates'][$currency] ?? null;

        if (! is_numeric($rate) || (float) $rate <= 0) {
            throw new ExchangeRateException('payment.unsupported_currency');
        }

        return [
            'rate' => (float) $rate,
            'source' => config('services.exchange_rate.source'),
            'fetched_at' => $snapshot['fetched_at'],
        ];
    }

    /**
     * @return array{conversion_rates: array<string, float|int>, fetched_at: Carbon}
     */
    private function latestRatesSnapshot(): array
    {
        $ttl = (int) config('services.exchange_rate.cache_ttl_seconds', 30);

        /** @var array{conversion_rates: array<string, float|int>, fetched_at: Carbon} $snapshot */
        $snapshot = Cache::remember(self::CACHE_KEY, $ttl, function (): array {
            return $this->fetchRatesFromApi();
        });

        return $snapshot;
    }

    /**
     * @return array{conversion_rates: array<string, float|int>, fetched_at: Carbon}
     */
    private function fetchRatesFromApi(): array
    {
        $baseUrl = rtrim((string) config('services.exchange_rate.url'), '/');
        $apiKey = config('services.exchange_rate.key');

        if (! $apiKey) {
            throw new ExchangeRateException(
                config('app.debug')
                    ? 'payment.rate_unavailable_missing_key'
                    : 'payment.rate_unavailable'
            );
        }

        $response = Http::timeout(10)->get("{$baseUrl}/{$apiKey}/latest/EUR");

        if (! $response->successful()) {
            throw new ExchangeRateException;
        }

        $payload = $response->json();

        if (($payload['result'] ?? null) !== 'success') {
            throw new ExchangeRateException;
        }

        $rates = $payload['conversion_rates'] ?? null;

        if (! is_array($rates) || $rates === []) {
            throw new ExchangeRateException;
        }

        return [
            'conversion_rates' => $rates,
            'fetched_at' => now(),
        ];
    }
}
