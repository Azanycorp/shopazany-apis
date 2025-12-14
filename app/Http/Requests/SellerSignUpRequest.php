<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rules\Password;

class SellerSignUpRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'email:rfc,dns', 'unique:users,email'],
            'address' => ['required', 'string'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
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
