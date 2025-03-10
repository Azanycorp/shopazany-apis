<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionPaymentRequest extends FormRequest
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
            'email' => ['required', 'email'],
            'subscription_plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
            'amount' => ['required', 'numeric'],
            'type' => ['required', 'string', 'in:paystack,authorize'],
            'card_number' => ['required_if:type,authorize'],
            'expiration_date' => ['required_if:type,authorize'],
            'card_code' => ['required_if:type,authorize'],
        ];
    }
}
