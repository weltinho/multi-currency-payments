<?php

namespace App\Contracts\Translation;

use App\Enums\AppLocale;

interface TranslatorContract
{
    public function locale(): AppLocale;

    /**
     * @param  array<string, string|int|float>  $replace
     */
    public function get(string $key, array $replace = []): string;
}
