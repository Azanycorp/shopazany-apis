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
            'centre_id' => ['nullable', 'integer', 'exists:collation_centers,id'],
            'user_shipping_address_id' => ['nullable', 'integer', 'required_without:shipping_address', 'exists:user_shipping_addresses,id'],
            'shipping_address' => ['nullable', 'array', 'required_without:user_shipping_address_id'],
            'shipping_address.first_name' => ['required_with:shipping_address', 'string'],
            'shipping_address.last_name' => ['nullable', 'string'],
            'shipping_address.email' => ['required_with:shipping_address', 'email'],
            'amount' => ['required', 'integer'],
            'currency' => ['required', 'string', 'in:NGN,USD'],
            'payment_redirect_url' => ['required', 'string', 'url'],
        ];
    }
}
