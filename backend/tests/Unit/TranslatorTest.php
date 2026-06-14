<?php

namespace Tests\Unit;

use App\Contracts\Translation\TranslatorContract;
use App\Enums\AppLocale;
use App\Services\Translation\Translator;
use Tests\TestCase;

class TranslatorTest extends TestCase
{
    public function test_returns_portuguese_message_when_locale_is_pt(): void
    {
        app()->setLocale(AppLocale::Portuguese->value);

        $translator = app(TranslatorContract::class);

        $this->assertSame(
            'As credenciais fornecidas estão incorretas.',
            $translator->get('auth.invalid_credentials')
        );
        $this->assertSame(AppLocale::Portuguese, $translator->locale());
    }

    public function test_falls_back_to_english_for_unknown_locale(): void
    {
        app()->setLocale('zz');

        $translator = new Translator;

        $this->assertSame(
            'The provided credentials are incorrect.',
            $translator->get('auth.invalid_credentials')
        );
    }
}
