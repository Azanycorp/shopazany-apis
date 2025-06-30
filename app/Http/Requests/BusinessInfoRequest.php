<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessInfoRequest extends FormRequest
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
            'identity_type' => ['required', 'string'],
            'file' => ['required', 'mimes:png,jpg,jpeg,pdf,docx,xlsx,xls'],
        ];
    }
}
