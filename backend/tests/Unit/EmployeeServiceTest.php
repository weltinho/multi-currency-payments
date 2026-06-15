<?php

namespace Tests\Unit;

use App\Contracts\Translation\TranslatorContract;
use App\Enums\UserRole;
use App\Exceptions\ForbiddenException;
use App\Models\User;
use App\Services\Employee\EmployeeService;
use App\Services\Translation\Translator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/** Unit tests for finance-only employee provisioning and country profile lookup. */
class EmployeeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_can_register_employee_with_country_profile(): void
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

        $service = new EmployeeService(new Translator);

        $result = $service->register($finance, [
            'name' => 'Ana Costa',
            'email' => 'ana.costa@buzzvel.com',
            'country_code' => 'PT',
        ]);

        $this->assertSame('employee', $result['role']);
        $this->assertSame('Portugal', $result['country']);
        $this->assertSame('EUR', $result['currency']);
        $this->assertTrue($result['must_change_password']);
    }

    public function test_employee_cannot_register_users(): void
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

        $service = new EmployeeService(app(TranslatorContract::class));

        $this->expectException(ForbiddenException::class);
        $service->register($employee, [
            'name' => 'Blocked',
            'email' => 'blocked@buzzvel.com',
            'country_code' => 'BR',
        ]);
    }

    public function test_register_rejects_unknown_country_code(): void
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

        $service = new EmployeeService(new Translator);

        $this->expectException(ValidationException::class);
        $service->register($finance, [
            'name' => 'Invalid',
            'email' => 'invalid@buzzvel.com',
            'country_code' => 'ZZ',
        ]);
    }
}
