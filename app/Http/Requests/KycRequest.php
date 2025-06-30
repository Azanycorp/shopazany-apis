<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KycRequest extends FormRequest
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
            'fullname' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'nationality' => ['required', 'string', 'max:100'],
            'country_of_residence' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string'],
            'document_number' => ['required', 'string'],
            'document_type' => ['required', 'string'],
            'image' => ['required', 'image', 'mimes:png,jpg,jpeg'],
        ];
    }
}
