<?php

namespace Tests\Unit;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_economic_fields_cannot_be_updated_after_creation(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@buzzvel.com',
            'password' => '123456',
            'role' => \App\Enums\UserRole::Employee,
            'country' => 'Brazil',
            'country_code' => 'BR',
            'currency' => 'BRL',
        ]);

        $payment = Payment::query()->create([
            'reference' => 'PAY-2026-0001',
            'user_id' => $user->id,
            'description' => 'Test payment',
            'currency' => 'BRL',
            'local_amount' => 100,
            'exchange_rate' => 6.21,
            'eur_amount' => 16.1,
            'rate_source' => 'exchangerate-api.com',
            'rate_fetched_at' => now(),
            'status' => PaymentStatus::Pending,
        ]);

        $this->expectException(\RuntimeException::class);

        $payment->update(['exchange_rate' => 7.0]);
    }
}
