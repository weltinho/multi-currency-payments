<?php

namespace App\Services\Translation;

use App\Contracts\Translation\TranslatorContract;
use App\Enums\AppLocale;

class Translator implements TranslatorContract
{
    public function locale(): AppLocale
    {
        return AppLocale::tryFromHeader(app()->getLocale());
    }

    public function get(string $key, array $replace = []): string
    {
        return trans("messages.{$key}", $replace);
    }
}
