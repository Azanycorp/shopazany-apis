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
            'origin_hub' => ['required', 'string', 'max:255'],
            'items_count' => ['required', 'numeric', 'min:0'],
            'destination_hub' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'min:0'],
            'priority' => ['required', 'in:low,medium,high'],
            'shipment_ids' => ['required', 'array'],
            'shipment_ids.*' => ['required', 'integer', 'exists:shippments,id'],
        ];
    }
}
