<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShippingAgentRequest extends FormRequest
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
            'name' => 'required',
            'type' => 'required|in:local,international',
            'logo' => 'nullable|image',
            'country_id' => 'required',
            'account_email' => 'required|email',
            'account_password' => 'required',
            'api_live_key' => 'required',
            'api_test_key' => 'required',
            'status' => 'required|in:active,test',
        ];
    }
}
