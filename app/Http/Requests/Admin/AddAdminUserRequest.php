<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AddAdminUserRequest extends FormRequest
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
            'first_name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:admins,email'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            // 'permissions' => ['required', 'array'],
            // 'permissions.*' => ['required', 'exists:permissions,id']
        ];
    }
}
