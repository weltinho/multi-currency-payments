<?php

namespace App\Services\Employee;

use App\Contracts\Employee\EmployeeServiceContract;
use App\Contracts\Translation\TranslatorContract;
use App\Enums\UserRole;
use App\Exceptions\ForbiddenException;
use App\Models\User;
use App\Support\EmployeeCountryProfiles;
use App\Support\PersonName;
use Illuminate\Validation\ValidationException;

/**
 * Finance-only employee provisioning (our interpretation of "Registration").
 *
 * In a real company, workers do not self-register on the reimbursement portal.
 * Finance (or HR) creates accounts; employees only authenticate via login.
 * role is always forced to employee — never accepted from the client.
 */
class EmployeeService implements EmployeeServiceContract
{
    public function __construct(private TranslatorContract $translator) {}

    public function listEmployees(User $user): array
    {
        if (! $user->isFinance()) {
            throw new ForbiddenException('employee.forbidden');
        }

        return User::query()
            ->where('role', UserRole::Employee)
            ->orderBy('name')
            ->get()
            ->map(fn (User $employee) => $employee->toApiArray())
            ->all();
    }

    public function register(User $user, array $data): array
    {
        if (! $user->isFinance()) {
            throw new ForbiddenException('employee.forbidden');
        }

        $profile = EmployeeCountryProfiles::find($data['country_code']);

        if (! $profile) {
            throw ValidationException::withMessages([
                'country_code' => [$this->translator->get('employee.country_not_supported')],
            ]);
        }

        // Initial password is the employee's first name; they must change it on first login.
        // PasswordPolicy only applies to the *new* password, not this temporary one.
        $employee = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => PersonName::firstName($data['name']),
            'must_change_password' => true,
            'role' => UserRole::Employee,
            'country' => $profile['country'],
            'country_code' => $profile['country_code'],
            'currency' => $profile['currency'],
        ]);

        return $employee->toApiArray();
    }
}
