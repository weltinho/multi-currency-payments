<?php

namespace App\Services\Auth;

use App\Contracts\Auth\AuthServiceContract;
use App\Contracts\Translation\TranslatorContract;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/** Auth business logic — login, session user payload, and first-login password change. */
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

    public function changePassword(User $user, string $currentPassword, string $newPassword): array
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [$this->translator->get('auth.validation.current_password_invalid')],
            ]);
        }

        // Clear the flag so password.changed middleware lets them through next time.
        $user->forceFill([
            'password' => $newPassword,
            'must_change_password' => false,
        ])->save();

        return $user->fresh()->toApiArray();
    }
}
