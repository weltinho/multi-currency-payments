<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.exchange_rate.url' => 'https://v6.exchangerate-api.com/v6',
            'services.exchange_rate.key' => 'test-key',
            'services.exchange_rate.source' => 'exchangerate-api.com',
        ]);

        Http::fake([
            'https://v6.exchangerate-api.com/v6/test-key/latest/EUR' => Http::response([
                'result' => 'success',
                'base_code' => 'EUR',
                'conversion_rates' => [
                    'BRL' => 6.21,
                    'USD' => 1.08,
                ],
            ]),
        ]);
    }

    public function test_employee_can_create_payment_request(): void
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

        $response = $this->actingAs($employee)->postJson('/api/payments', [
            'description' => 'Equipment reimbursement',
            'local_amount' => 4200,
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('currency', 'BRL')
            ->assertJsonPath('local_amount', 4200)
            ->assertJsonPath('exchange_rate', 6.21)
            ->assertJsonPath('eur_amount', 676.33)
            ->assertJsonPath('user_id', $employee->id)
            ->assertJsonPath('user_name', 'Rafael Souza')
            ->assertJsonStructure(['rate_fetched_at']);

        $this->assertDatabaseHas('payment_requests', [
            'user_id' => $employee->id,
            'description' => 'Equipment reimbursement',
            'currency' => 'BRL',
            'status' => 'pending',
        ]);
    }

    public function test_employee_can_create_payment_in_another_currency(): void
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

        $response = $this->actingAs($employee)->postJson('/api/payments', [
            'description' => 'USD reimbursement',
            'local_amount' => 108,
            'currency' => 'USD',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('currency', 'USD')
            ->assertJsonPath('local_amount', 108)
            ->assertJsonPath('exchange_rate', 1.08)
            ->assertJsonPath('eur_amount', 100);

        $this->assertDatabaseHas('payment_requests', [
            'user_id' => $employee->id,
            'description' => 'USD reimbursement',
            'currency' => 'USD',
            'status' => 'pending',
        ]);
    }

    public function test_employee_cannot_create_payment_with_unsupported_currency(): void
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

        $response = $this->actingAs($employee)->postJson('/api/payments', [
            'description' => 'Invalid currency',
            'local_amount' => 100,
            'currency' => 'XYZ',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['currency']);
    }

    public function test_finance_cannot_create_payment_request(): void
    {
        $finance = User::create([
            'name' => 'Helena Marques',
            'email' => 'finance@buzzvel.com',
            'password' => '123456',
            'role' => UserRole::Finance,
            'country' => 'Portugal',
            'country_code' => 'PT',
            'currency' => 'EUR',
        ]);

        $response = $this->actingAs($finance)->postJson('/api/payments', [
            'description' => 'Should fail',
            'local_amount' => 100,
        ]);

        $response->assertForbidden();
    }

    public function test_employee_can_view_own_payment(): void
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

        $payment = Payment::query()->create([
            'reference' => 'PAY-2026-0001',
            'user_id' => $employee->id,
            'description' => 'Test payment',
            'currency' => 'BRL',
            'local_amount' => 100,
            'exchange_rate' => 6.21,
            'eur_amount' => 16.1,
            'rate_source' => 'exchangerate-api.com',
            'rate_fetched_at' => now(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($employee)->getJson("/api/payments/{$payment->id}");

        $response->assertOk()
            ->assertJsonPath('id', $payment->id)
            ->assertJsonPath('description', 'Test payment');
    }

    public function test_employee_cannot_view_other_users_payment(): void
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

        $other = User::create([
            'name' => 'Emily Carter',
            'email' => 'emily@buzzvel.com',
            'password' => '123456',
            'role' => UserRole::Employee,
            'country' => 'United States',
            'country_code' => 'US',
            'currency' => 'USD',
        ]);

        $payment = Payment::query()->create([
            'reference' => 'PAY-2026-0002',
            'user_id' => $other->id,
            'description' => 'Other user payment',
            'currency' => 'USD',
            'local_amount' => 100,
            'exchange_rate' => 1.08,
            'eur_amount' => 92.59,
            'rate_source' => 'exchangerate-api.com',
            'rate_fetched_at' => now(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($employee)->getJson("/api/payments/{$payment->id}");

        $response->assertForbidden();
    }

    public function test_finance_can_approve_pending_payment(): void
    {
        $finance = User::create([
            'name' => 'Helena Marques',
            'email' => 'finance@buzzvel.com',
            'password' => '123456',
            'role' => UserRole::Finance,
            'country' => 'Portugal',
            'country_code' => 'PT',
            'currency' => 'EUR',
        ]);

        $employee = User::create([
            'name' => 'Rafael Souza',
            'email' => 'rafael@buzzvel.com',
            'password' => '123456',
            'role' => UserRole::Employee,
            'country' => 'Brazil',
            'country_code' => 'BR',
            'currency' => 'BRL',
        ]);

        $payment = Payment::query()->create([
            'reference' => 'PAY-2026-0003',
            'user_id' => $employee->id,
            'description' => 'Pending payment',
            'currency' => 'BRL',
            'local_amount' => 100,
            'exchange_rate' => 6.21,
            'eur_amount' => 16.1,
            'rate_source' => 'exchangerate-api.com',
            'rate_fetched_at' => now(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($finance)->patchJson("/api/payments/{$payment->id}", [
            'status' => 'approved',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'approved');

        $this->assertDatabaseHas('payment_requests', [
            'id' => $payment->id,
            'status' => 'approved',
        ]);
    }
}
