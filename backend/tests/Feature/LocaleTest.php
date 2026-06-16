<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_validation_is_returned_in_requested_language(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid@buzzvel.com',
            'password' => 'wrong-password',
        ], [
            'X-App-Language' => 'pt',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonFragment([
                'message' => 'As credenciais fornecidas estão incorretas.',
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
    }

    public function test_payment_forbidden_message_is_returned_in_requested_language(): void
    {
        $employee = User::create([
            'name' => 'Rafael Souza',
            'email' => 'rafael@buzzvel.com',
            'password' => '123456',
            'role' => UserRole::Employee,
            'country' => 'Brazil',
            'country_code' => 'BR',
            'currency' => 'BRL',
        ]);

        $response = $this->actingAs($employee)
            ->patchJson('/api/payments/p-1001', [
                'status' => 'approved',
            ], [
                'X-App-Language' => 'es',
            ]);

        $response->assertForbidden()
            ->assertJson([
                'message' => 'No tiene permiso para realizar esta acción.',
            ]);
    }

    public function test_login_validation_is_returned_in_german(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid@buzzvel.com',
            'password' => 'wrong-password',
        ], [
            'X-App-Language' => 'de',
        ]);

        $response->assertUnprocessable()
            ->assertJsonFragment([
                'email' => ['Die angegebenen Zugangsdaten sind falsch.'],
            ]);
    }
}
