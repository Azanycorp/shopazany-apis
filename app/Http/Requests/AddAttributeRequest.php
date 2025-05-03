<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddAttributeRequest extends FormRequest
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
            'attributes' => ['required', 'array'],
            'attributes.*.name' => ['required', 'string', 'max:100', 'unique:product_attributes,name'],
            'attributes.*.values' => ['required', 'array'],
            'attributes.*.use_for_variation' => ['required', 'boolean'],
        ];
    }
}
