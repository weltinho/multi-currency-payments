<?php

namespace App\Http\Requests;

use App\Contracts\Translation\TranslatorContract;
use App\Support\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates PUT /api/password — used after finance provisions an account and the
 * employee logs in for the first time. Current password can still be their first
 * name; the new password must satisfy PasswordPolicy.
 */
class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            // Rules live in PasswordPolicy so we can swap the demo policy later.
            'password' => PasswordPolicy::rules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $translator = app(TranslatorContract::class);

        return [
            'current_password.required' => $translator->get('auth.validation.current_password_required'),
            'password.required' => $translator->get('auth.validation.password_required'),
            'password.regex' => $translator->get('auth.validation.password_format'),
            'password.confirmed' => $translator->get('auth.validation.password_confirmed'),
        ];
    }
}
