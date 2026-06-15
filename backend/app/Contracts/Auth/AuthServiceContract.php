<?php

namespace App\Contracts\Auth;

use App\Models\User;

interface AuthServiceContract
{
    /**
     * @param  array{email: string, password: string}  $credentials
     */
    public function login(array $credentials): void;

    /**
     * @return array<string, mixed>
     */
    public function currentUser(User $user): array;

    /**
     * First-login password update. Clears must_change_password on success.
     *
     * @return array<string, mixed>
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): array;
}
