<?php

namespace App\Contracts\ExchangeRate;

interface ExchangeRateServiceContract
{
    /**
     * Fetch the EUR → local currency rate for the given ISO 4217 code.
     *
     * @return array{rate: float, source: string, fetched_at: \DateTimeInterface}
     */
    public function getRateForCurrency(string $currency): array;
}
