<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AddB2BBannerRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'start_date' => ['required', 'date', 'date_format:Y-m-d H:i:s', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'date_format:Y-m-d H:i:s', 'after:start_date'],
            'products' => ['required', 'array'],
            'products.*' => ['exists:b2b_products,id'],
        ];
    }
}
