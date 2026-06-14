<?php

namespace App\Http\Requests;

use App\Contracts\Translation\TranslatorContract;
use App\Support\EmployeeCountryProfiles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Finance-only employee registration. role is never accepted from the client.
 * Validation messages are localized via TranslatorContract.
 */
class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isFinance() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
            'country_code' => ['required', 'string', 'size:2', Rule::in(EmployeeCountryProfiles::codes())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $translator = app(TranslatorContract::class);

        return [
            'name.required' => $translator->get('employee.validation.name_required'),
            'email.required' => $translator->get('employee.validation.email_required'),
            'email.email' => $translator->get('employee.validation.email_invalid'),
            'email.unique' => $translator->get('employee.validation.email_taken'),
            'password.required' => $translator->get('employee.validation.password_required'),
            'password.min' => $translator->get('employee.validation.password_min'),
            'country_code.required' => $translator->get('employee.validation.country_required'),
            'country_code.in' => $translator->get('employee.country_not_supported'),
        ];
    }
}
