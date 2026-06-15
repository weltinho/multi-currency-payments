<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Authenticated user. role drives authorization across payments and employee management.
 * country/currency on the profile define the employee's reimbursement currency.
 *
 * must_change_password is set when finance creates the account; the SPA shows a
 * forced password screen until they call PUT /api/password.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'must_change_password',
        'role',
        'country',
        'country_code',
        'currency',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'role' => UserRole::class,
        ];
    }

    public function isFinance(): bool
    {
        return $this->role === UserRole::Finance;
    }

    public function isEmployee(): bool
    {
        return $this->role === UserRole::Employee;
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** Shape returned to the frontend. */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role->value,
            'country' => $this->country,
            'country_code' => $this->country_code,
            'currency' => $this->currency,
            'must_change_password' => $this->must_change_password,
        ];
    }
}
