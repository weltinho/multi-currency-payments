<?php

namespace App\Exceptions;

use RuntimeException;

/** Exchange rate API unavailable or currency not supported — mapped to HTTP 503. */
class ExchangeRateException extends RuntimeException
{
    public function __construct(public readonly string $translationKey = 'payment.rate_unavailable')
    {
        parent::__construct($translationKey);
    }
}
