<?php

namespace App\Services\Payment;

use App\Contracts\ExchangeRate\ExchangeRateServiceContract;
use App\Contracts\Payment\PaymentRepositoryContract;
use App\Contracts\Payment\PaymentServiceContract;
use App\Enums\PaymentStatus;
use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\Payment;
use App\Models\User;

/**
 * Payment business rules: role-based scoping, immutable rate at create,
 * finance-only approve/reject with 409 on non-pending.
 *
 * Pagination is done in-memory after repository filter — acceptable for demo
 * scale; could move to DB paginate() if volume grows.
 */
class PaymentService implements PaymentServiceContract
{
    public function __construct(
        private PaymentRepositoryContract $payments,
        private ExchangeRateServiceContract $exchangeRates,
    ) {}

    public function paginate(User $user, array $query): array
    {
        $perPage = max(1, (int) ($query['per_page'] ?? 8));
        $page = max(1, (int) ($query['page'] ?? 1));

        // Employees are always scoped to their own user_id — finance can filter all.
        $filters = [
            'status' => $query['status'] ?? null,
            'collaborator' => $query['collaborator'] ?? null,
            'user_id' => $user->isEmployee() ? (string) $user->id : ($query['user_id'] ?? null),
        ];

        $rows = $this->payments->filter($filters);
        $rows = $this->sortRows($rows, $query['sort'] ?? null, $query['dir'] ?? null);
        $total = count($rows);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;
        $data = array_slice($rows, $offset, $perPage);

        return [
            'data' => $data,
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $total === 0 ? 0 : $offset + 1,
            'to' => $total === 0 ? 0 : $offset + count($data),
        ];
    }

    public function summary(User $user, array $query): array
    {
        $filters = [
            'collaborator' => $query['collaborator'] ?? null,
            'user_id' => $user->isEmployee() ? (string) $user->id : null,
        ];

        $rows = $this->payments->filter($filters);

        $statusCounts = [
            'all' => count($rows),
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'expired' => 0,
        ];

        $approvedEur = 0.0;

        foreach ($rows as $payment) {
            $statusCounts[$payment['status']]++;
            if ($payment['status'] === 'approved') {
                $approvedEur += $payment['eur_amount'];
            }
        }

        return [
            'total' => $statusCounts['all'],
            'pending' => $statusCounts['pending'],
            'approved_eur' => round($approvedEur, 2),
            'status_counts' => $statusCounts,
        ];
    }

    public function create(User $user, array $data): array
    {
        if (! $user->isEmployee()) {
            throw new ForbiddenException('payment.forbidden');
        }

        // Rate captured once here — never updated afterwards (model guard enforces).
        $currency = $data['currency'] ?? $user->currency;
        $rateData = $this->exchangeRates->getRateForCurrency($currency);
        $localAmount = (float) $data['local_amount'];
        $eurAmount = round($localAmount / $rateData['rate'], 2);

        return $this->payments->create([
            'reference' => $this->generateReference(),
            'user_id' => $user->id,
            'description' => $data['description'],
            'currency' => $currency,
            'local_amount' => $localAmount,
            'exchange_rate' => $rateData['rate'],
            'eur_amount' => $eurAmount,
            'rate_source' => $rateData['source'],
            'rate_fetched_at' => $rateData['fetched_at'],
            'status' => PaymentStatus::Pending,
        ]);
    }

    public function show(User $user, string $id): array
    {
        $payment = $this->payments->find($id);

        if (! $payment) {
            throw new NotFoundException('payment.not_found');
        }

        if ($user->isEmployee() && (int) $payment['user_id'] !== $user->id) {
            throw new ForbiddenException('payment.forbidden');
        }

        return $payment;
    }

    public function decide(User $user, string $id, string $status): array
    {
        if (! $user->isFinance()) {
            throw new ForbiddenException('payment.forbidden');
        }

        $payment = $this->payments->find($id);

        if (! $payment) {
            throw new NotFoundException('payment.not_found');
        }

        if ($payment['status'] !== 'pending') {
            throw new ConflictException('payment.not_pending');
        }

        $updated = $this->payments->updateStatus($id, $status);

        if (! $updated) {
            throw new NotFoundException('payment.not_found');
        }

        return $updated;
    }

    public function expireStalePending(): int
    {
        // Batch expiry: one query for all stale pendings. Alternative would be a
        // Job::dispatch()->delay(48h) on create — we skipped that; see README.
        $hours = (int) config('payments.pending_expiration_hours', 48);
        $cutoff = now()->subHours($hours);

        return $this->payments->expirePendingOlderThan($cutoff);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function sortRows(array $rows, ?string $sortBy, ?string $direction): array
    {
        $allowed = [
            'created_at',
            'currency',
            'local_amount',
            'eur_amount',
            'status',
            'user_name',
            'exchange_rate',
            'country',
        ];

        $sortBy = in_array($sortBy, $allowed, true) ? $sortBy : 'created_at';
        $direction = strtolower((string) $direction) === 'asc' ? 'asc' : 'desc';

        usort($rows, function (array $a, array $b) use ($sortBy, $direction): int {
            $left = $a[$sortBy] ?? null;
            $right = $b[$sortBy] ?? null;

            $cmp = match ($sortBy) {
                'local_amount', 'eur_amount', 'exchange_rate' => ($left <=> $right),
                'created_at' => strcmp((string) $left, (string) $right),
                default => strcasecmp((string) $left, (string) $right),
            };

            return $direction === 'asc' ? $cmp : -$cmp;
        });

        return $rows;
    }

    private function generateReference(): string
    {
        $year = now()->year;
        $sequence = (int) Payment::query()->max('id') + 1;

        return sprintf('PAY-%d-%04d', $year, $sequence);
    }
}
