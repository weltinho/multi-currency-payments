<?php

namespace App\Services\TestUser;

use App\Contracts\TestUser\TestUserServiceContract;
use App\Enums\UserRole;
use App\Models\User;

class TestUserService implements TestUserServiceContract
{
    public function listGroupedByRole(): array
    {
        $mapUser = fn (User $user) => [
            'name' => $user->name,
            'email' => $user->email,
            'country' => $user->country,
            'currency' => $user->currency,
        ];

        return [
            'finance' => User::query()
                ->where('role', UserRole::Finance)
                ->orderBy('name')
                ->get()
                ->map($mapUser)
                ->values()
                ->all(),
            'employees' => User::query()
                ->where('role', UserRole::Employee)
                ->orderBy('name')
                ->get()
                ->map($mapUser)
                ->values()
                ->all(),
        ];
    }
}
