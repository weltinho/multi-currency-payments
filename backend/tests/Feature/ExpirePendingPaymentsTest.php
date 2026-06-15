<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** End-to-end check for pending → expired scheduled command (window from config). */
class ExpirePendingPaymentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_expires_only_stale_pending_payments(): void
    {
        $hours = (int) config('payments.pending_expiration_hours', 48);

        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'expire.test@buzzvel.com',
            'password' => '123456',
            'role' => UserRole::Employee,
            'country' => 'Brazil',
            'country_code' => 'BR',
            'currency' => 'BRL',
        ]);

        $stale = Payment::query()->create($this->paymentAttributes($employee->id, PaymentStatus::Pending));
        $stale->forceFill(['created_at' => now()->subHours($hours + 1), 'updated_at' => now()->subHours($hours + 1)])->saveQuietly();

        $fresh = Payment::query()->create($this->paymentAttributes($employee->id, PaymentStatus::Pending));
        $freshAge = $hours > 2 ? now()->subHours(2) : now()->subMinutes(max(1, (int) ($hours * 30)));
        $fresh->forceFill(['created_at' => $freshAge, 'updated_at' => $freshAge])->saveQuietly();

        $approved = Payment::query()->create($this->paymentAttributes($employee->id, PaymentStatus::Approved));
        $approved->forceFill(['created_at' => now()->subHours($hours + 24), 'updated_at' => now()->subHours($hours + 24)])->saveQuietly();

        $this->artisan('payments:expire-pending')
            ->expectsOutputToContain('Expired 1 pending payment')
            ->assertSuccessful();

        $this->assertSame(PaymentStatus::Expired, $stale->fresh()->status);
        $this->assertSame(PaymentStatus::Pending, $fresh->fresh()->status);
        $this->assertSame(PaymentStatus::Approved, $approved->fresh()->status);
        $this->assertNull($stale->fresh()->reviewed_at);
    }

    public function test_command_reports_when_nothing_to_expire(): void
    {
        $hours = (int) config('payments.pending_expiration_hours', 48);

        $this->artisan('payments:expire-pending')
            ->expectsOutputToContain("No pending payments older than {$hours} hours")
            ->assertSuccessful();
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentAttributes(int $userId, PaymentStatus $status): array
    {
        $now = now();

        return [
            'reference' => 'PAY-TEST-'.uniqid(),
            'user_id' => $userId,
            'description' => 'Test payment',
            'currency' => 'BRL',
            'local_amount' => 100,
            'exchange_rate' => 6.0,
            'eur_amount' => 16.67,
            'rate_source' => 'test',
            'rate_fetched_at' => $now,
            'status' => $status,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
