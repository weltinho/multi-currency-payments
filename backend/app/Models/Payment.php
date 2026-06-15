<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Persisted payment request. Table name is payment_requests (Buzzvel domain term).
 *
 * Economic fields (rate, amounts, currency) are immutable after insert — enforced
 * in booted() so even accidental mass-assignment cannot change audit data.
 *
 * user_name/country are NOT stored here; joined from users in toApiArray() so
 * there is a single source of truth for employee profile data.
 */
class Payment extends Model
{
    protected $table = 'payment_requests';

    protected $fillable = [
        'reference',
        'user_id',
        'description',
        'currency',
        'local_amount',
        'exchange_rate',
        'eur_amount',
        'rate_source',
        'rate_fetched_at',
        'status',
        'reviewed_at',
    ];

    protected static function booted(): void
    {
        static::updating(function (Payment $payment) {
            foreach (['currency', 'local_amount', 'exchange_rate', 'eur_amount', 'rate_source', 'rate_fetched_at'] as $field) {
                if ($payment->isDirty($field)) {
                    throw new \RuntimeException("Payment economic field [{$field}] is immutable after creation.");
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'local_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:8',
            'eur_amount' => 'decimal:2',
            'rate_fetched_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'status' => PaymentStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Shape returned to the frontend. */
    public function toApiArray(): array
    {
        $this->loadMissing('user');

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'user_id' => $this->user_id,
            'user_name' => $this->user->name,
            'country' => $this->user->country,
            'currency' => $this->currency,
            'local_amount' => (float) $this->local_amount,
            'exchange_rate' => (float) $this->exchange_rate,
            'eur_amount' => (float) $this->eur_amount,
            'status' => $this->status->value,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'rate_source' => $this->rate_source,
            'rate_fetched_at' => $this->rate_fetched_at->toIso8601String(),
            'description' => $this->description,
        ];
    }
}
