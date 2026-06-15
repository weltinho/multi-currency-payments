<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Finance-provisioned registration — our take on the brief's "Registration" requirement. */
class EmployeeRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_can_register_employee(): void
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

        $response = $this->actingAs($finance)->postJson('/api/employees', [
            'name' => 'New Employee',
            'email' => 'new.employee@buzzvel.com',
            'country_code' => 'BR',
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'New Employee')
            ->assertJsonPath('email', 'new.employee@buzzvel.com')
            ->assertJsonPath('role', 'employee')
            ->assertJsonPath('must_change_password', true)
            ->assertJsonPath('country', 'Brazil')
            ->assertJsonPath('country_code', 'BR')
            ->assertJsonPath('currency', 'BRL');

        $this->assertDatabaseHas('users', [
            'email' => 'new.employee@buzzvel.com',
            'role' => 'employee',
            'country_code' => 'BR',
            'currency' => 'BRL',
            'must_change_password' => true,
        ]);
    }

    public function test_provisioned_employee_gets_first_name_as_initial_password(): void
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

        $this->actingAs($finance)->postJson('/api/employees', [
            'name' => 'Maria Silva',
            'email' => 'maria.silva@buzzvel.com',
            'country_code' => 'PT',
        ])->assertCreated();

        $employee = User::query()->where('email', 'maria.silva@buzzvel.com')->firstOrFail();

        $this->assertTrue($employee->must_change_password);
        $this->assertTrue(Hash::check('Maria', $employee->password));
    }

    public function test_employee_cannot_register_other_employees(): void
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

        $response = $this->actingAs($employee)->postJson('/api/employees', [
            'name' => 'Blocked',
            'email' => 'blocked@buzzvel.com',
            'country_code' => 'US',
        ]);

        $response->assertForbidden();
    }

    public function test_finance_cannot_register_duplicate_email(): void
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

        User::create([
            'name' => 'Rafael Souza',
            'email' => 'rafael@buzzvel.com',
            'password' => '123456',
            'role' => UserRole::Employee,
            'country' => 'Brazil',
            'country_code' => 'BR',
            'currency' => 'BRL',
        ]);

        $response = $this->actingAs($finance)->postJson('/api/employees', [
            'name' => 'Duplicate',
            'email' => 'rafael@buzzvel.com',
            'country_code' => 'BR',
        ], [
            'X-App-Language' => 'en',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_finance_can_list_employees(): void
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

        User::create([
            'name' => 'Rafael Souza',
            'email' => 'rafael@buzzvel.com',
            'password' => '123456',
            'role' => UserRole::Employee,
            'country' => 'Brazil',
            'country_code' => 'BR',
            'currency' => 'BRL',
        ]);

        $response = $this->actingAs($finance)->getJson('/api/employees');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'rafael@buzzvel.com');
    }

    public function test_employee_registration_validation_is_localized(): void
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

        $response = $this->actingAs($finance)->postJson('/api/employees', [], [
            'X-App-Language' => 'pt',
        ]);

        $response->assertUnprocessable()
            ->assertJsonFragment([
                'name' => ['O nome do funcionário é obrigatório.'],
            ]);
    }
}
