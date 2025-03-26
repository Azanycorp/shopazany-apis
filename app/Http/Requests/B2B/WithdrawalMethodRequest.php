<?php

namespace App\Http\Requests\B2B;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'account_name' => ['required', 'string', 'max:250'],
            'account_number' => ['required', 'string', 'max:11'],
            'bank_name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:bank_transfer'],
            'platform' => ['required', 'in:paystack,authorize'],
        ];
    }
}
