<?php

namespace App\Http\Requests;

use App\Enum\BannerType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg'],
            'type' => [
                'required',
                'string',
                'in:'.BannerType::B2C.','.BannerType::B2B.','.BannerType::AGRIECOM_B2C.','.BannerType::AGRIECOM_B2B,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'The type must be one of: '.BannerType::B2C.','.BannerType::B2B.','.BannerType::AGRIECOM_B2C.','.BannerType::AGRIECOM_B2B,
        ];
    }
}
