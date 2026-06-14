<?php

namespace App\Repositories;

use App\Contracts\Payment\PaymentRepositoryContract;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

/**
 * Eloquent-backed payment persistence (replaces the early in-memory stub
 * used before the database layer existed).
 */
class EloquentPaymentRepository implements PaymentRepositoryContract
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function filter(array $filters): array
    {
        $query = Payment::query()->with('user');

        $this->applyFilters($query, $filters);

        return $query
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Payment $payment) => $payment->toApiArray())
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        $payment = Payment::query()->with('user')->find($id);

        return $payment?->toApiArray();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        $payment = Payment::query()->create($data);

        return $payment->load('user')->toApiArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function updateStatus(string $id, string $status): ?array
    {
        $payment = Payment::query()->with('user')->find($id);

        if (! $payment) {
            return null;
        }

        $payment->update([
            'status' => PaymentStatus::from($status),
            'reviewed_at' => now(),
        ]);

        return $payment->fresh(['user'])->toApiArray();
    }

    /**
     * @param  Builder<Payment>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $userId = $filters['user_id'] ?? null;

        if ($userId !== null && $userId !== '') {
            $query->where('user_id', (int) $userId);
        }

        $status = $filters['status'] ?? null;

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $collaborator = strtolower(trim($filters['collaborator'] ?? ''));

        if ($collaborator !== '') {
            $query->whereHas('user', function (Builder $userQuery) use ($collaborator) {
                $userQuery->whereRaw('LOWER(name) LIKE ?', ['%'.$collaborator.'%']);
            });
        }
    }
}
