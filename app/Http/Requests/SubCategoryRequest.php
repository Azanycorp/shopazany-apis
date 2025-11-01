<?php

namespace App\Http\Requests;

use App\Enum\BannerType;
use Illuminate\Foundation\Http\FormRequest;

class SubCategoryRequest extends FormRequest
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
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:'.BannerType::B2C.','.BannerType::B2B.','.BannerType::AGRIECOM_B2C],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'The type must be one of: '.BannerType::B2C.','.BannerType::B2B.','.BannerType::AGRIECOM_B2C,
        ];
    }
}
