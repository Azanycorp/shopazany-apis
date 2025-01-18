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
            'name' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'integer'],
            'keywords' => ['required'],
            'description' => ['required', 'string'],
            'quantity' => ['required', 'string'],
            'unit' => ['required', 'string'],
            'front_image' => ['required', 'image', 'mimes:png,jpg,jpeg'],
            'minimum_order_quantity' => ['required', 'integer'],
            'fob_price' => ['required'],
            'images' => ['required', 'array'],
            'images.*' => ['image', 'mimes:png,jpg,jpeg']
        ];
    }
}
