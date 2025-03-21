<?php

namespace App\Http\Requests\B2B;

use Illuminate\Foundation\Http\FormRequest;

class AddProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:200', 'regex:/^[a-zA-Z0-9\s\-]+$/'],
            'category_id' => ['required', 'integer', 'exists:b2b_product_categories,id'],
            'sub_category_id' => ['nullable', 'integer', 'exists:b2b_product_categories,id'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'keywords' => ['required', 'array', 'min:1', 'max:10'],
            'keywords.*' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:500'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit' => ['required', 'integer', 'min:1'],
            'front_image' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'minimum_order_quantity' => ['required', 'integer', 'min:1'],
            'fob_price' => ['required', 'numeric', 'min:0'],
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ];
    }
}
