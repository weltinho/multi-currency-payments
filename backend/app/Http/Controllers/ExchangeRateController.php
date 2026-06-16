<?php

namespace App\Http\Controllers;

use App\Contracts\ExchangeRate\ExchangeRateServiceContract;
use App\Contracts\Translation\TranslatorContract;
use App\Exceptions\ExchangeRateException;
use App\OpenApi\ExchangeRateResponse;
use App\Support\SupportedCurrencies;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

/**
 * Live EUR → local rate preview for the employee submission form.
 * Reuses the same Redis cache as payment creation.
 */
class ExchangeRateController extends Controller
{
    public function __construct(
        private ExchangeRateServiceContract $exchangeRates,
        private TranslatorContract $translator,
    ) {}

    #[Group('Employee', description: 'Live rate preview for the submission form estimate.', weight: 40)]
    #[Response(200, type: ExchangeRateResponse::class)]
    #[Response(422, examples: [['message' => 'The given data was invalid.', 'errors' => ['currency' => ['The selected currency is invalid.']]]])]
    #[Response(503, examples: [['message' => 'Exchange rate is temporarily unavailable. Please try again later.']])]
    public function show(string $currency): JsonResponse
    {
        $currency = strtoupper($currency);

        if (! in_array($currency, SupportedCurrencies::codes(), true)) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['currency' => ['The selected currency is invalid.']],
            ], 422);
        }

        try {
            $rateData = $this->exchangeRates->getRateForCurrency($currency);

            return response()->json([
                'currency' => $currency,
                'exchange_rate' => $rateData['rate'],
                'rate_source' => $rateData['source'],
                'rate_fetched_at' => $rateData['fetched_at']->format('c'),
            ]);
        } catch (ExchangeRateException $e) {
            return response()->json(['message' => $this->translator->get($e->translationKey)], 503);
        }
    }
}
