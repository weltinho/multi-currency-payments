<?php

namespace App\Support;

/**
 * Name helpers for provisioned employee accounts.
 */
final class PersonName
{
    /**
     * First token of the trimmed full name — used as the temporary initial password.
     */
    public static function firstName(string $fullName): string
    {
        $trimmed = trim($fullName);

        if ($trimmed === '') {
            return '';
        }

        $parts = preg_split('/\s+/u', $trimmed);

        return $parts[0] ?? $trimmed;
    }
}
