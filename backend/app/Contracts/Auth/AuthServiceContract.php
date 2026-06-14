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
}
