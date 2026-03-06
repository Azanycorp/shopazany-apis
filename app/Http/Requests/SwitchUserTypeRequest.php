<?php

namespace App\Http\Requests;

use App\Enum\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SwitchUserTypeRequest extends FormRequest
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
            'type' => [
                'required',
                'string',
                Rule::in([UserType::B2B_SELLER, UserType::B2B_BUYER, UserType::B2B_AGRIECOM_SELLER, UserType::B2B_AGRIECOM_BUYER]),
            ],
        ];
    }
}
