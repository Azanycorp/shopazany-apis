<?php

namespace App\Http\Requests\Admin;

use App\Enum\BannerType;
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
            'period' => ['required', 'string', 'in:monthly,yearly'],
            'tier' => ['required', 'integer'],
            'type' => ['required', 'string', 'in:'.BannerType::B2C.','.BannerType::B2B.','.BannerType::AGRIECOM_B2C],
        ];
    }

    public function messages()
    {
        return [
            'period' => 'Field should be either monthly or yearly',
            'type.in' => 'The type must be one of: '.BannerType::B2C.','.BannerType::B2B.','.BannerType::AGRIECOM_B2C,
        ];
    }
}
