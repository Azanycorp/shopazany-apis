<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'description' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'product_price' => ['required', 'integer'],
            'current_stock_quantity' => ['required', 'integer'],
            'minimum_order_quantity' => ['required', 'integer'],
            'front_image' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:10240'],
            'images' => ['required', 'array'],
            'images.*' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:10240'],
            'discount_type' => ['nullable', 'in:flat,percentage'],
            'discount_value' => ['nullable', 'numeric', 'min:0', $this->discountRule()],
            'variation' => ['required', 'array'],
            'variation.*' => ['required', 'string', $this->variationRule()],
            'variation_image' => ['nullable', 'array'],
            'variation_image.*' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'discount_type.in' => 'The discount type must be either flat or percentage.',
        ];
    }

    /**
     * Custom rule to validate discount value based on discount type.
     */
    protected function discountRule(): \Closure
    {
        return function ($attribute, $value, $fail): void {
            if ($this->discount_type === 'percentage' && $value > 100) {
                $fail('The discount value cannot be more than 100 when the discount type is percentage.');
            }
        };
    }

    /**
     * Custom rule to validate each variation JSON.
     */
    protected function variationRule(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $decoded = json_decode($value, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $fail('Each variation must be a valid JSON string.');
            }

            $requiredKeys = ['variation', 'sku', 'price', 'stock'];

            foreach ($requiredKeys as $key) {
                if (! isset($decoded[$key]) || $decoded[$key] === '' || $decoded[$key] === null) {
                    return $fail("The '{$key}' in variation cannot be empty.");
                }
            }

            if (! is_numeric($decoded['price']) || $decoded['price'] < 0) {
                return $fail("The 'price' in variation must be a non-negative number.");
            }

            if (! is_numeric($decoded['stock']) || $decoded['stock'] < 0) {
                return $fail("The 'stock' in variation must be a non-negative number.");
            }
        };
    }
}
