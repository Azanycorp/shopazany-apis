<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddRewardPointRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'icon' => ['required', 'image'],
            'points' => ['required', 'integer'],
            'verification_type' => ['required', 'string', 'in:manual,automatic'],
            'country_ids' => ['required', 'array'],
        ];
    }
}
