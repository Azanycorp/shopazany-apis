<?php

namespace App\Http\Requests;

use App\Rules\NoDisposableEmail;
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
        return [
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'email' => ['required', 'email', 'email:rfc,dns', new NoDisposableEmail, 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'referral_code' => ['somtimes', 'string'],
        ];
    }
}
