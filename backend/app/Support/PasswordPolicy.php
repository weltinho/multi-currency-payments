<?php

namespace App\Support;

/**
 * Single place for password rules when the user picks a new password (first login, etc.).
 *
 * Right now this is deliberately weak — exactly 6 digits — because it's a test project.
 * When we harden this for real use, change PATTERN / rules() here (and the matching
 * frontend file in lib/password-policy.ts).
 */
final class PasswordPolicy
{
    public const LENGTH = 6;

    public const PATTERN = '/^\d{6}$/';

    /**
     * Used by ChangePasswordRequest and anywhere else we validate a chosen password.
     * Includes `confirmed` so Laravel checks password_confirmation for us.
     *
     * @return list<string|\Illuminate\Contracts\Validation\ValidationRule>
     */
    public static function rules(): array
    {
        return [
            'required',
            'string',
            'regex:'.self::PATTERN,
            'confirmed',
        ];
    }

    public static function isValid(string $password): bool
    {
        return (bool) preg_match(self::PATTERN, $password);
    }
}
