<?php

namespace App\Exceptions;

use RuntimeException;

class ConflictException extends RuntimeException
{
    public function __construct(public readonly string $translationKey)
    {
        parent::__construct($translationKey);
    }
}
