<?php

namespace App\Console\Commands;

use App\Contracts\Payment\PaymentServiceContract;
use Illuminate\Console\Command;

/**
 * Marks stale pending payment requests as expired.
 *
 * We use a scheduled command (batch scan) instead of a delayed Job per payment —
 * see README § "Payment expiration" for the rationale. In short: the rule is
 * global ("anything pending > 48h"), idempotent, and needs no extra queue worker
 * in Docker beyond the scheduler container we already run.
 */
class ExpirePendingPayments extends Command
{
    protected $signature = 'payments:expire-pending';

    protected $description = 'Expire pending payment requests older than the configured window';

    public function handle(PaymentServiceContract $payments): int
    {
        $hours = (int) config('payments.pending_expiration_hours', 48);
        $expired = $payments->expireStalePending();

        if ($expired === 0) {
            $this->info("No pending payments older than {$hours} hours.");

            return self::SUCCESS;
        }

        $this->info("Expired {$expired} pending payment(s) older than {$hours} hours.");

        return self::SUCCESS;
    }
}
