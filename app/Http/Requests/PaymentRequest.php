<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
            'shipping_agent_id' => ['nullable', 'integer', 'exists:shipping_agents,id'],
            'email' => ['required', 'email', 'email:rfc,dns'],
            'amount' => ['required', 'integer'],
            'currency' => ['required', 'string', 'in:NGN,USD'],
            'payment_redirect_url' => ['required', 'url'],
        ];
    }
}
