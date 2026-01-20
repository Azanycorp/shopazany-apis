<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class SignUpRequest extends FormRequest
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
            'email' => ['required', 'email', 'email:rfc,dns', 'unique:users,email'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'terms' => ['required', 'boolean'],
        ];

        if (App::environment('production')) {
            $rules['email'][] = function ($attribute, $value, $fail) {
                $blockedDomains = config('disposableemail.domains', []);

                $domain = strtolower(trim(Str::after($value, '@')));

                if (in_array($domain, $blockedDomains, true)) {
                    $fail('Disposable or test email addresses are not allowed.');
                }
            };
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
