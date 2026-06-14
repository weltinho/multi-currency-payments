<?php

namespace App\Exceptions;

use RuntimeException;

class NotFoundException extends RuntimeException
{
    public function __construct(public readonly string $translationKey)
    {
        parent::__construct($translationKey);
    }
}
