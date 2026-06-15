<?php

namespace App\Support\Scramble;

/** Canonical OpenAPI example payloads for Scramble / Try It. */
final class OpenApiExamples
{
    public static function health(): array
    {
        return ['status' => 'ok'];
    }

    public static function messageAuthenticated(): array
    {
        return ['message' => 'Authenticated'];
    }

    public static function loginRequest(): array
    {
        return [
            'email' => 'finance@buzzvel.com',
            'password' => '123456',
        ];
    }

    public static function userFinance(): array
    {
        return [
            'id' => '01932a1b-2c3d-7000-8000-000000000001',
            'name' => 'Helena Marques',
            'email' => 'finance@buzzvel.com',
            'role' => 'finance',
            'country' => 'Portugal',
            'country_code' => 'PT',
            'currency' => 'EUR',
            'must_change_password' => false,
        ];
    }

    public static function userEmployee(): array
    {
        return [
            'id' => '01932a1b-2c3d-7000-8000-000000000010',
            'name' => 'Rafael Silva',
            'email' => 'rafael@buzzvel.com',
            'role' => 'employee',
            'country' => 'Brazil',
            'country_code' => 'BR',
            'currency' => 'BRL',
            'must_change_password' => false,
        ];
    }

    public static function userNewEmployee(): array
    {
        return [
            'id' => '01932a1b-2c3d-7000-8000-000000000099',
            'name' => 'Jordan Lee',
            'email' => 'jordan.lee@buzzvel.com',
            'role' => 'employee',
            'country' => 'United States',
            'country_code' => 'US',
            'currency' => 'USD',
            'must_change_password' => true,
        ];
    }

    public static function changePasswordRequest(): array
    {
        return [
            'current_password' => '123456',
            'password' => '654321',
            'password_confirmation' => '654321',
        ];
    }

    public static function storeEmployeeRequest(): array
    {
        return [
            'name' => 'Jordan Lee',
            'email' => 'jordan.lee@buzzvel.com',
            'country_code' => 'US',
        ];
    }

    public static function storePaymentRequest(): array
    {
        return [
            'description' => 'Equipment reimbursement — monitor and peripherals',
            'local_amount' => 4200,
            'currency' => 'BRL',
        ];
    }

    public static function paymentDecisionRequest(): array
    {
        return ['status' => 'approved'];
    }

    public static function paymentPending(): array
    {
        return [
            'id' => '01932a1b-2c3d-7000-8000-000000000501',
            'reference' => 'PAY-2026-1007',
            'user_id' => '01932a1b-2c3d-7000-8000-000000000010',
            'user_name' => 'Rafael Silva',
            'country' => 'Brazil',
            'currency' => 'BRL',
            'local_amount' => 4200,
            'exchange_rate' => 6.21,
            'eur_amount' => 676.33,
            'status' => 'pending',
            'created_at' => '2026-06-15T08:00:00+00:00',
            'updated_at' => '2026-06-15T08:00:00+00:00',
            'reviewed_at' => null,
            'rate_source' => 'exchangerate-api.com',
            'description' => 'Equipment reimbursement — monitor and peripherals',
        ];
    }

    public static function paymentApproved(): array
    {
        return [
            ...self::paymentPending(),
            'status' => 'approved',
            'reviewed_at' => '2026-06-15T12:00:00+00:00',
        ];
    }

    public static function paginatedPayments(): array
    {
        return [
            'data' => [self::paymentPending()],
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 8,
            'total' => 1,
            'from' => 1,
            'to' => 1,
        ];
    }

    public static function paymentSummary(): array
    {
        return [
            'total' => 12,
            'pending' => 4,
            'approved_eur' => 1842.59,
            'status_counts' => [
                'all' => 12,
                'pending' => 4,
                'approved' => 5,
                'rejected' => 1,
                'expired' => 2,
            ],
        ];
    }

    public static function testUsers(): array
    {
        return [
            'finance' => [[
                'name' => 'Helena Marques',
                'email' => 'finance@buzzvel.com',
                'country' => 'Portugal',
                'currency' => 'EUR',
            ]],
            'employees' => [[
                'name' => 'Rafael Silva',
                'email' => 'rafael@buzzvel.com',
                'country' => 'Brazil',
                'currency' => 'BRL',
            ]],
        ];
    }

    public static function employeeList(): array
    {
        return [
            'data' => [[
                'id' => '01932a1b-2c3d-7000-8000-000000000010',
                'name' => 'Rafael Silva',
                'email' => 'rafael@buzzvel.com',
                'country' => 'Brazil',
                'country_code' => 'BR',
                'currency' => 'BRL',
            ]],
        ];
    }

    public static function countryProfiles(): array
    {
        return [
            'data' => [[
                'code' => 'BR',
                'name' => 'Brazil',
                'currency' => 'BRL',
            ]],
        ];
    }

    public static function validationError(): array
    {
        return [
            'message' => 'The given data was invalid.',
            'errors' => [
                'email' => ['The provided credentials are incorrect.'],
            ],
        ];
    }
}
