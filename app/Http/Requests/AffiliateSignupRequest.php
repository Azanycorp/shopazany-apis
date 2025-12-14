<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AffiliateSignupRequest extends FormRequest
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
        $rules = [
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'email' => ['required', 'email', 'email:rfc,dns'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'referral_code' => ['somtimes', 'string'],
        ];

        if (App::environment('production')) {
            $rules['email'][] = 'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com|hotmail\.com)$/';
        }

        return $rules;
    }

    /**
     * Get custom error messages for specific fields.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.regex' => 'Please use a valid email address with one of the following domains: gmail.com, yahoo.com, outlook.com, hotmail.com.',
        ];
    }
}
