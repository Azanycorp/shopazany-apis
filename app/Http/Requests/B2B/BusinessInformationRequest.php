<?php

namespace App\Http\Requests\B2B;

use Illuminate\Foundation\Http\FormRequest;

class BusinessInformationRequest extends FormRequest
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
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'business_location' => ['required', 'string'],
            'business_type' => ['required', 'string'],
            'identification_type' => ['required', 'string'],
            'identification_type_document' => ['required'],
            'agree' => ['required', 'boolean'],
        ];
    }
}
