<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

/**
 * Laravel policy for Payment — available for Gate/authorize() if extended.
 * Primary checks also live in PaymentService for explicit, testable flows.
 */
class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        return $user->isFinance() || $payment->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isEmployee();
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->isFinance();
    }
}
