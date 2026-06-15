<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Covers the first-login password flow and password.changed middleware behaviour. */
class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_provisioned_employee_must_change_password_before_creating_payments(): void
    {
        $employee = User::create([
            'name' => 'New Employee',
            'email' => 'new.employee@buzzvel.com',
            'password' => 'New',
            'must_change_password' => true,
            'role' => UserRole::Employee,
            'country' => 'Brazil',
            'country_code' => 'BR',
            'currency' => 'BRL',
        ]);

        $response = $this->actingAs($employee)->postJson('/api/payments', [
            'description' => 'Test',
            'local_amount' => 100,
        ]);

        $response->assertForbidden()
            ->assertJsonPath('message', 'You must change your password before continuing.');
    }

    public function test_employee_can_change_password_and_access_payments(): void
    {
        $employee = User::create([
            'name' => 'New Employee',
            'email' => 'new.employee@buzzvel.com',
            'password' => 'New',
            'must_change_password' => true,
            'role' => UserRole::Employee,
            'country' => 'Brazil',
            'country_code' => 'BR',
            'currency' => 'BRL',
        ]);

        $response = $this->actingAs($employee)->putJson('/api/password', [
            'current_password' => 'New',
            'password' => '654321',
            'password_confirmation' => '654321',
        ]);

        $response->assertOk()
            ->assertJsonPath('must_change_password', false);

        $this->assertDatabaseHas('users', [
            'email' => 'new.employee@buzzvel.com',
            'must_change_password' => false,
        ]);
    }

    public function test_change_password_rejects_invalid_current_password(): void
    {
        $employee = User::create([
            'name' => 'New Employee',
            'email' => 'new.employee@buzzvel.com',
            'password' => 'New',
            'must_change_password' => true,
            'role' => UserRole::Employee,
            'country' => 'Brazil',
            'country_code' => 'BR',
            'currency' => 'BRL',
        ]);

        $response = $this->actingAs($employee)->putJson('/api/password', [
            'current_password' => 'wrong',
            'password' => '654321',
            'password_confirmation' => '654321',
        ], [
            'X-App-Language' => 'en',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_change_password_rejects_invalid_format(): void
    {
        $employee = User::create([
            'name' => 'New Employee',
            'email' => 'new.employee@buzzvel.com',
            'password' => 'New',
            'must_change_password' => true,
            'role' => UserRole::Employee,
            'country' => 'Brazil',
            'country_code' => 'BR',
            'currency' => 'BRL',
        ]);

        $response = $this->actingAs($employee)->putJson('/api/password', [
            'current_password' => 'New',
            'password' => 'not-six-digits',
            'password_confirmation' => 'not-six-digits',
        ], [
            'X-App-Language' => 'en',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
