<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class B2BAuthorizeNetCardRequest extends FormRequest
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
            'rfq_id' => 'required|numeric|exists:rfqs,id',
            'shipping_address_id' => 'required|numeric|exists:buyer_shipping_addresses,id',
            'shipping_agent_id' => 'required|numeric|exists:shipping_agents,id',
            'amount' => 'required|numeric',
            'payment.creditCard.cardNumber' => 'required|digits:16',
            'payment.creditCard.expirationDate' => 'required|date_format:m/y',
            'payment.creditCard.cardCode' => 'required|digits:3',
            'billTo.firstName' => 'required|string',
            'billTo.lastName' => 'required|string',
            'billTo.address' => 'required|string',
            'billTo.city' => 'required|string',
            'billTo.state' => 'required|string',
            'billTo.country' => 'required|string',
            'customer.email' => 'required|email',
        ];
    }
}
