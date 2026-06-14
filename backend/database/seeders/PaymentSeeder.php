<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Sample payment requests for the finance dashboard demo.
 * Linked to seeded users by email (not legacy stub string ids).
 */
class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $usersByEmail = User::query()
            ->whereIn('email', [
                'rafael@buzzvel.com',
                'emily@buzzvel.com',
                'oliver@buzzvel.com',
                'yuki@buzzvel.com',
            ])
            ->get()
            ->keyBy('email');

        $payments = [
            ['email' => 'rafael@buzzvel.com', 'reference' => 'PAY-2026-1001', 'description' => 'Equipment reimbursement — monitor and peripherals', 'local_amount' => 4200, 'exchange_rate' => 6.21, 'eur_amount' => 676.33, 'status' => PaymentStatus::Pending, 'created_at' => '2026-06-12 09:24:00', 'reviewed_at' => null],
            ['email' => 'emily@buzzvel.com', 'reference' => 'PAY-2026-1002', 'description' => 'Tech conference — registration', 'local_amount' => 1850, 'exchange_rate' => 1.08, 'eur_amount' => 1712.96, 'status' => PaymentStatus::Approved, 'created_at' => '2026-06-11 14:10:00', 'reviewed_at' => '2026-06-11 16:42:00'],
            ['email' => 'oliver@buzzvel.com', 'reference' => 'PAY-2026-1003', 'description' => 'Design software — annual license', 'local_amount' => 920, 'exchange_rate' => 0.84, 'eur_amount' => 1095.24, 'status' => PaymentStatus::Pending, 'created_at' => '2026-06-12 07:55:00', 'reviewed_at' => null],
            ['email' => 'yuki@buzzvel.com', 'reference' => 'PAY-2026-1004', 'description' => 'Corporate travel — accommodation', 'local_amount' => 245000, 'exchange_rate' => 162.45, 'eur_amount' => 1508.16, 'status' => PaymentStatus::Rejected, 'created_at' => '2026-06-09 03:30:00', 'reviewed_at' => '2026-06-09 11:05:00'],
            ['email' => 'rafael@buzzvel.com', 'reference' => 'PAY-2026-1005', 'description' => 'Office supplies — pending for more than 48h', 'local_amount' => 1300, 'exchange_rate' => 6.18, 'eur_amount' => 210.36, 'status' => PaymentStatus::Expired, 'created_at' => '2026-06-05 18:00:00', 'reviewed_at' => null],
            ['email' => 'emily@buzzvel.com', 'reference' => 'PAY-2026-1006', 'description' => 'Productivity tools subscription', 'local_amount' => 540, 'exchange_rate' => 1.07, 'eur_amount' => 504.67, 'status' => PaymentStatus::Approved, 'created_at' => '2026-06-08 12:20:00', 'reviewed_at' => '2026-06-08 13:00:00'],
            ['email' => 'oliver@buzzvel.com', 'reference' => 'PAY-2026-1007', 'description' => 'External consulting — product sprint', 'local_amount' => 2100, 'exchange_rate' => 0.85, 'eur_amount' => 2470.59, 'status' => PaymentStatus::Pending, 'created_at' => '2026-06-12 10:40:00', 'reviewed_at' => null],
            ['email' => 'rafael@buzzvel.com', 'reference' => 'PAY-2026-1008', 'description' => 'Coworking desk — monthly', 'local_amount' => 980, 'exchange_rate' => 6.2, 'eur_amount' => 158.06, 'status' => PaymentStatus::Approved, 'created_at' => '2026-06-07 11:15:00', 'reviewed_at' => '2026-06-07 15:30:00'],
            ['email' => 'yuki@buzzvel.com', 'reference' => 'PAY-2026-1009', 'description' => 'Online course — UX research', 'local_amount' => 88000, 'exchange_rate' => 161.9, 'eur_amount' => 543.55, 'status' => PaymentStatus::Approved, 'created_at' => '2026-06-06 08:45:00', 'reviewed_at' => '2026-06-06 10:20:00'],
            ['email' => 'emily@buzzvel.com', 'reference' => 'PAY-2026-1010', 'description' => 'Laptop replacement — engineering', 'local_amount' => 3200, 'exchange_rate' => 1.09, 'eur_amount' => 2935.78, 'status' => PaymentStatus::Pending, 'created_at' => '2026-06-12 13:05:00', 'reviewed_at' => null],
            ['email' => 'oliver@buzzvel.com', 'reference' => 'PAY-2026-1011', 'description' => 'Client dinner — missing receipt', 'local_amount' => 410, 'exchange_rate' => 0.84, 'eur_amount' => 488.1, 'status' => PaymentStatus::Rejected, 'created_at' => '2026-06-04 16:30:00', 'reviewed_at' => '2026-06-05 09:10:00'],
            ['email' => 'rafael@buzzvel.com', 'reference' => 'PAY-2026-1012', 'description' => 'Team offsite — venue deposit', 'local_amount' => 2750, 'exchange_rate' => 6.22, 'eur_amount' => 442.12, 'status' => PaymentStatus::Pending, 'created_at' => '2026-06-11 19:50:00', 'reviewed_at' => null],
        ];

        foreach ($payments as $row) {
            $user = $usersByEmail->get($row['email']);

            if (! $user) {
                continue;
            }

            Payment::updateOrCreate(
                ['reference' => $row['reference']],
                [
                    'user_id' => $user->id,
                    'description' => $row['description'],
                    'currency' => $user->currency,
                    'local_amount' => $row['local_amount'],
                    'exchange_rate' => $row['exchange_rate'],
                    'eur_amount' => $row['eur_amount'],
                    'rate_source' => 'exchangerate-api.com',
                    'rate_fetched_at' => $row['created_at'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['created_at'],
                    'reviewed_at' => $row['reviewed_at'],
                ],
            );
        }
    }
}
