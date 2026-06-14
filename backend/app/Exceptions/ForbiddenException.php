<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Domain exception carrying a messages.php translation key.
 * Controllers catch these and return localized JSON (403/404/409/503).
 */
class ForbiddenException extends RuntimeException
{
    public function __construct(public readonly string $translationKey)
    {
        parent::__construct($translationKey);
    }
}
