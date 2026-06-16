<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeRatePreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.exchange_rate.url' => 'https://v6.exchangerate-api.com/v6',
            'services.exchange_rate.key' => 'test-key',
            'services.exchange_rate.source' => 'exchangerate-api.com',
            'services.exchange_rate.cache_ttl_seconds' => 30,
        ]);

        Http::fake([
            'https://v6.exchangerate-api.com/v6/test-key/latest/EUR' => Http::response([
                'result' => 'success',
                'base_code' => 'EUR',
                'conversion_rates' => [
                    'BRL' => 6.21,
                ],
            ]),
        ]);
    }

    public function test_employee_can_fetch_live_rate_preview(): void
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

        $this->actingAs($employee)
            ->getJson('/api/exchange-rates/BRL')
            ->assertOk()
            ->assertJsonPath('currency', 'BRL')
            ->assertJsonPath('exchange_rate', 6.21)
            ->assertJsonPath('rate_source', 'exchangerate-api.com')
            ->assertJsonStructure(['rate_fetched_at']);
    }

    public function test_eur_preview_returns_rate_of_one(): void
    {
        $employee = User::create([
            'name' => 'Ana Rodrigues',
            'email' => 'ana@buzzvel.com',
            'password' => '123456',
            'role' => UserRole::Employee,
            'country' => 'Portugal',
            'country_code' => 'PT',
            'currency' => 'EUR',
        ]);

        $this->actingAs($employee)
            ->getJson('/api/exchange-rates/EUR')
            ->assertOk()
            ->assertJsonPath('exchange_rate', 1);
    }

    public function test_invalid_currency_returns_422(): void
    {
        $employee = User::create([
            'name' => 'Ana Rodrigues',
            'email' => 'ana@buzzvel.com',
            'password' => '123456',
            'role' => UserRole::Employee,
            'country' => 'Portugal',
            'country_code' => 'PT',
            'currency' => 'EUR',
        ]);

        $this->actingAs($employee)
            ->getJson('/api/exchange-rates/XYZ')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['currency']);
    }

    public function test_guest_cannot_fetch_rate_preview(): void
    {
        $this->getJson('/api/exchange-rates/BRL')->assertUnauthorized();
    }
}
