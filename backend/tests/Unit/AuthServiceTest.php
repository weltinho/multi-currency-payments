<?php

namespace Tests\Unit;

use App\Contracts\Translation\TranslatorContract;
use App\Services\Auth\AuthService;
use App\Services\Translation\Translator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    public function test_login_throws_translated_validation_exception_on_failure(): void
    {
        app()->setLocale('pt');

        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'wrong@buzzvel.com', 'password' => 'secret'])
            ->andReturn(false);

        $service = new AuthService(new Translator);

        try {
            $service->login(['email' => 'wrong@buzzvel.com', 'password' => 'secret']);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertSame(
                ['As credenciais fornecidas estão incorretas.'],
                $e->errors()['email']
            );
        }
    }

    public function test_login_succeeds_when_credentials_are_valid(): void
    {
        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'finance@buzzvel.com', 'password' => '123456'])
            ->andReturn(true);

        $service = new AuthService(app(TranslatorContract::class));

        $service->login(['email' => 'finance@buzzvel.com', 'password' => '123456']);

        $this->assertTrue(true);
    }
}
