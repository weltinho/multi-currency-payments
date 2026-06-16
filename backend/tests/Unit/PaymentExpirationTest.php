<?php

namespace Tests\Unit;

use App\Contracts\Payment\PaymentRepositoryContract;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\EloquentPaymentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentExpirationTest extends TestCase
{
    use RefreshDatabase;

    public function test_repository_expires_pending_rows_older_than_cutoff(): void
    {
        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'repo.expire@buzzvel.com',
            'password' => '123456',
            'role' => UserRole::Employee,
            'country' => 'Portugal',
            'country_code' => 'PT',
            'currency' => 'EUR',
        ]);

        Payment::query()->create([
            'reference' => 'PAY-EXP-1',
            'user_id' => $employee->id,
            'description' => 'Stale',
            'currency' => 'EUR',
            'local_amount' => 100,
            'exchange_rate' => 1,
            'eur_amount' => 100,
            'rate_source' => 'test',
            'rate_fetched_at' => now(),
            'status' => PaymentStatus::Pending,
        ])->forceFill(['created_at' => now()->subHours(50), 'updated_at' => now()->subHours(50)])->saveQuietly();

        Payment::query()->create([
            'reference' => 'PAY-EXP-2',
            'user_id' => $employee->id,
            'description' => 'Fresh',
            'currency' => 'EUR',
            'local_amount' => 50,
            'exchange_rate' => 1,
            'eur_amount' => 50,
            'rate_source' => 'test',
            'rate_fetched_at' => now(),
            'status' => PaymentStatus::Pending,
        ])->forceFill(['created_at' => now()->subHour(), 'updated_at' => now()->subHour()])->saveQuietly();

        $repository = new EloquentPaymentRepository;
        $cutoff = now()->subHours(48);

        $this->assertSame(1, $repository->expirePendingOlderThan($cutoff, 48));
        $this->assertSame(PaymentStatus::Expired, Payment::query()->where('reference', 'PAY-EXP-1')->value('status'));
        $this->assertSame(PaymentStatus::Pending, Payment::query()->where('reference', 'PAY-EXP-2')->value('status'));
    }

    public function test_repository_does_not_expire_pending_rows_at_exactly_forty_eight_hours(): void
    {
        $employee = User::create([
            'name' => 'Boundary Employee',
            'email' => 'boundary.expire@buzzvel.com',
            'password' => '123456',
            'role' => UserRole::Employee,
            'country' => 'Portugal',
            'country_code' => 'PT',
            'currency' => 'EUR',
        ]);

        Payment::query()->create([
            'reference' => 'PAY-EXP-BOUNDARY',
            'user_id' => $employee->id,
            'description' => 'Exactly 48h',
            'currency' => 'EUR',
            'local_amount' => 100,
            'exchange_rate' => 1,
            'eur_amount' => 100,
            'rate_source' => 'test',
            'rate_fetched_at' => now(),
            'status' => PaymentStatus::Pending,
        ])->forceFill(['created_at' => now()->subHours(48), 'updated_at' => now()->subHours(48)])->saveQuietly();

        $repository = new EloquentPaymentRepository;

        $this->assertSame(0, $repository->expirePendingOlderThan(now()->subHours(48), 48));
        $this->assertSame(PaymentStatus::Pending, Payment::query()->where('reference', 'PAY-EXP-BOUNDARY')->value('status'));
    }
}
