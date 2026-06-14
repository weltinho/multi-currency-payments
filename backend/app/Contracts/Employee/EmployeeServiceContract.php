<?php

namespace App\Contracts\Employee;

use App\Models\User;

interface EmployeeServiceContract
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listEmployees(User $user): array;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function register(User $user, array $data): array;
}
