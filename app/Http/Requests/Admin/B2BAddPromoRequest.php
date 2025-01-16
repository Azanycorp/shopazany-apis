<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class B2BAddPromoRequest extends FormRequest
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
            'coupon_type' => ['required', 'in:product,total_orders,welcome_coupon'],
            'coupon_code' => ['required', 'string', 'unique:b2b_promos,coupon_code'],
            'discount' => ['required', 'integer'],
            'discount_type' => ['required', 'in:amount,percent']
        ];
    }
}
