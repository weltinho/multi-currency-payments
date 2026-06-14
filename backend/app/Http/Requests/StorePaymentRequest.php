<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Employee payment creation. Only accepts amount + description —
 * currency and exchange rate are resolved server-side from the user profile.
 */
class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isEmployee() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:1000'],
            'local_amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
