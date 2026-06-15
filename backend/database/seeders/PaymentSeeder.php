<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Demo payment requests for finance/employee dashboards.
 *
 * Every seeded employee gets one pending request aged 47h55m so the scheduled
 * expire command will flip it within ~5 minutes of a fresh docker compose up.
 */
class PaymentSeeder extends Seeder
{
    /** Rough EUR rates for seed data only — real creates use the live API. */
    private const APPROX_RATES = [
        'BRL' => 6.21,
        'USD' => 1.08,
        'GBP' => 0.84,
        'JPY' => 162.45,
        'EUR' => 1.0,
        'INR' => 90.5,
        'KRW' => 1450.0,
        'PLN' => 4.32,
        'CAD' => 1.47,
        'AUD' => 1.62,
        'MXN' => 18.5,
        'AED' => 3.97,
        'SEK' => 11.2,
        'ZAR' => 19.8,
        'SGD' => 1.45,
    ];

    public function run(): void
    {
        $employees = User::query()
            ->where('role', UserRole::Employee)
            ->orderBy('email')
            ->get();

        if ($employees->isEmpty()) {
            return;
        }

        // Just under 48h — scheduler will expire these shortly after boot.
        $almostExpiredAt = Carbon::now()->subHours(47)->subMinutes(55);
        $sequence = 1001;

        foreach ($employees as $employee) {
            $this->seedPayment($employee, [
                'reference' => sprintf('PAY-2026-%04d', $sequence++),
                'description' => 'Pending reimbursement — nearing 48h expiration (demo)',
                'local_amount' => $this->sampleAmount($employee->currency),
                'status' => PaymentStatus::Pending,
                'created_at' => $almostExpiredAt,
                'reviewed_at' => null,
            ]);
        }

        // A few extra rows with mixed statuses so the dashboard is not empty-only-expired.
        $showcase = [
            ['email' => 'rafael@buzzvel.com', 'description' => 'Equipment reimbursement — monitor and peripherals', 'local_amount' => 4200, 'status' => PaymentStatus::Pending, 'hours_ago' => 2],
            ['email' => 'emily@buzzvel.com', 'description' => 'Tech conference — registration', 'local_amount' => 1850, 'status' => PaymentStatus::Approved, 'hours_ago' => 30, 'reviewed_hours_ago' => 28],
            ['email' => 'oliver@buzzvel.com', 'description' => 'Design software — annual license', 'local_amount' => 920, 'status' => PaymentStatus::Pending, 'hours_ago' => 6],
            ['email' => 'yuki@buzzvel.com', 'description' => 'Corporate travel — accommodation', 'local_amount' => 245000, 'status' => PaymentStatus::Rejected, 'hours_ago' => 72, 'reviewed_hours_ago' => 70],
            ['email' => 'ana@buzzvel.com', 'description' => 'Coworking pass — quarterly', 'local_amount' => 450, 'status' => PaymentStatus::Approved, 'hours_ago' => 120, 'reviewed_hours_ago' => 118],
            ['email' => 'lukas@buzzvel.com', 'description' => 'Team lunch — client visit', 'local_amount' => 180, 'status' => PaymentStatus::Expired, 'hours_ago' => 96],
            ['email' => 'camille@buzzvel.com', 'description' => 'Training course — compliance', 'local_amount' => 890, 'status' => PaymentStatus::Pending, 'hours_ago' => 12],
            ['email' => 'priya@buzzvel.com', 'description' => 'Software subscription', 'local_amount' => 12000, 'status' => PaymentStatus::Approved, 'hours_ago' => 48, 'reviewed_hours_ago' => 40],
        ];

        foreach ($showcase as $row) {
            $employee = $employees->firstWhere('email', $row['email']);

            if (! $employee) {
                continue;
            }

            $createdAt = Carbon::now()->subHours($row['hours_ago']);
            $reviewedAt = isset($row['reviewed_hours_ago'])
                ? Carbon::now()->subHours($row['reviewed_hours_ago'])
                : null;

            $this->seedPayment($employee, [
                'reference' => sprintf('PAY-2026-%04d', $sequence++),
                'description' => $row['description'],
                'local_amount' => $row['local_amount'],
                'status' => $row['status'],
                'created_at' => $createdAt,
                'reviewed_at' => $reviewedAt,
            ]);
        }
    }

    /**
     * @param  array{
     *     reference: string,
     *     description: string,
     *     local_amount: float|int,
     *     status: PaymentStatus,
     *     created_at: Carbon,
     *     reviewed_at?: Carbon|null
     * }  $row
     */
    private function seedPayment(User $employee, array $row): void
    {
        $rate = self::APPROX_RATES[$employee->currency] ?? 1.0;
        $localAmount = (float) $row['local_amount'];
        $createdAt = $row['created_at'];

        Payment::updateOrCreate(
            ['reference' => $row['reference']],
            [
                'user_id' => $employee->id,
                'description' => $row['description'],
                'currency' => $employee->currency,
                'local_amount' => $localAmount,
                'exchange_rate' => $rate,
                'eur_amount' => round($localAmount / $rate, 2),
                'rate_source' => 'exchangerate-api.com',
                'rate_fetched_at' => $createdAt,
                'status' => $row['status'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'reviewed_at' => $row['reviewed_at'] ?? null,
            ],
        );
    }

    private function sampleAmount(string $currency): float
    {
        return match ($currency) {
            'JPY', 'KRW', 'INR' => 50000,
            'BRL', 'MXN', 'ZAR' => 1500,
            default => 500,
        };
    }
}
