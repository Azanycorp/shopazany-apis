<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionPlanRequest extends FormRequest
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
            'cost' => ['required'],
            'country_id' => ['required'],
            'period' => ['required', 'string', 'in:monthly,yearly']
        ];
    }

    public function messages()
    {
        return [
            'period' => 'Field should be either monthly or yearly'
        ];
    }
}
