<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo users for the Buzzvel test environment.
 *
 * Finance accounts are seed-only (no self-registration). Employees can also be
 * created at runtime by finance via POST /api/employees. Password for all: 123456.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $financeTeam = [
            [
                'name' => 'Helena Marques',
                'email' => 'finance@buzzvel.com',
                'role' => 'finance',
                'country' => 'Portugal',
                'country_code' => 'PT',
                'currency' => 'EUR',
            ],
            [
                'name' => 'Marcus Keller',
                'email' => 'marcus.finance@buzzvel.com',
                'role' => 'finance',
                'country' => 'Germany',
                'country_code' => 'DE',
                'currency' => 'EUR',
            ],
            [
                'name' => 'Sofia Laurent',
                'email' => 'sofia.finance@buzzvel.com',
                'role' => 'finance',
                'country' => 'France',
                'country_code' => 'FR',
                'currency' => 'EUR',
            ],
        ];

        $employees = [
            ['name' => 'Rafael Souza', 'email' => 'rafael@buzzvel.com', 'country' => 'Brazil', 'country_code' => 'BR', 'currency' => 'BRL'],
            ['name' => 'Emily Carter', 'email' => 'emily@buzzvel.com', 'country' => 'United States', 'country_code' => 'US', 'currency' => 'USD'],
            ['name' => 'Oliver Bennett', 'email' => 'oliver@buzzvel.com', 'country' => 'United Kingdom', 'country_code' => 'GB', 'currency' => 'GBP'],
            ['name' => 'Yuki Tanaka', 'email' => 'yuki@buzzvel.com', 'country' => 'Japan', 'country_code' => 'JP', 'currency' => 'JPY'],
            ['name' => 'Ana Rodrigues', 'email' => 'ana@buzzvel.com', 'country' => 'Portugal', 'country_code' => 'PT', 'currency' => 'EUR'],
            ['name' => 'Lukas Weber', 'email' => 'lukas@buzzvel.com', 'country' => 'Germany', 'country_code' => 'DE', 'currency' => 'EUR'],
            ['name' => 'Camille Dubois', 'email' => 'camille@buzzvel.com', 'country' => 'France', 'country_code' => 'FR', 'currency' => 'EUR'],
            ['name' => 'James O\'Connor', 'email' => 'james@buzzvel.com', 'country' => 'Ireland', 'country_code' => 'IE', 'currency' => 'EUR'],
            ['name' => 'Priya Sharma', 'email' => 'priya@buzzvel.com', 'country' => 'India', 'country_code' => 'IN', 'currency' => 'INR'],
            ['name' => 'Min-jun Park', 'email' => 'minjun@buzzvel.com', 'country' => 'South Korea', 'country_code' => 'KR', 'currency' => 'KRW'],
            ['name' => 'Sofia García', 'email' => 'sofia.garcia@buzzvel.com', 'country' => 'Spain', 'country_code' => 'ES', 'currency' => 'EUR'],
            ['name' => 'Marco Rossi', 'email' => 'marco@buzzvel.com', 'country' => 'Italy', 'country_code' => 'IT', 'currency' => 'EUR'],
            ['name' => 'Elena Popov', 'email' => 'elena@buzzvel.com', 'country' => 'Poland', 'country_code' => 'PL', 'currency' => 'PLN'],
            ['name' => 'Liam Fraser', 'email' => 'liam@buzzvel.com', 'country' => 'Canada', 'country_code' => 'CA', 'currency' => 'CAD'],
            ['name' => 'Chloe Nguyen', 'email' => 'chloe@buzzvel.com', 'country' => 'Australia', 'country_code' => 'AU', 'currency' => 'AUD'],
            ['name' => 'Diego Morales', 'email' => 'diego@buzzvel.com', 'country' => 'Mexico', 'country_code' => 'MX', 'currency' => 'MXN'],
            ['name' => 'Fatima Al-Hassan', 'email' => 'fatima@buzzvel.com', 'country' => 'United Arab Emirates', 'country_code' => 'AE', 'currency' => 'AED'],
            ['name' => 'Erik Lindström', 'email' => 'erik@buzzvel.com', 'country' => 'Sweden', 'country_code' => 'SE', 'currency' => 'SEK'],
            ['name' => 'Zara Okonkwo', 'email' => 'zara@buzzvel.com', 'country' => 'South Africa', 'country_code' => 'ZA', 'currency' => 'ZAR'],
            ['name' => 'Wei Chen', 'email' => 'wei@buzzvel.com', 'country' => 'Singapore', 'country_code' => 'SG', 'currency' => 'SGD'],
        ];

        foreach ($financeTeam as $user) {
            $this->seedUser($user);
        }

        foreach ($employees as $user) {
            $this->seedUser(array_merge($user, ['role' => 'employee']));
        }
    }

    private function seedUser(array $attributes): void
    {
        User::updateOrCreate(
            ['email' => $attributes['email']],
            array_merge($attributes, ['password' => '123456']),
        );
    }
}
