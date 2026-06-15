<?php

namespace App\Http\Requests;

use App\Support\SupportedCurrencies;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Employee payment creation. Amount + description required; currency optional
 * (defaults to the employee profile currency when omitted).
 *
 * @ignoreSchema
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
            'currency' => ['sometimes', 'string', 'size:3', Rule::in(SupportedCurrencies::codes())],
        ];
    }
}
