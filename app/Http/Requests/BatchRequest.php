<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchRequest extends FormRequest
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
            'collation_id' => ['required', 'integer', 'exists:collation_centers,id'],
            'shipment_ids' => ['required', 'array'],
            'shipment_ids.*' => ['required', 'integer', 'exists:shippments,id'],
        ];
    }
}
