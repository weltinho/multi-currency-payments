<?php

namespace App\Services\Auth;

use App\Contracts\Auth\AuthServiceContract;
use App\Contracts\Translation\TranslatorContract;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/** Auth business logic — keeps credential validation translatable and testable. */
class AuthService implements AuthServiceContract
{
    public function __construct(private TranslatorContract $translator) {}

    public function login(array $credentials): void
    {
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => [$this->translator->get('auth.invalid_credentials')],
            ]);
        }
    }

    public function currentUser(User $user): array
    {
        return $user->toApiArray();
    }
}
