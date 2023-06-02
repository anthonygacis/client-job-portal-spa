<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'role_type' => ['required', 'in:existing,new'],
            'existing_roles' => ['required_if:role_type,existing', 'array'],
            'new_role' => ['required_if:role_type,new'],
            'permissions' => ['required_if:role_type,new', 'array']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function messages()
    {
        return [
            'new_role.required_if' => 'Role name is required',
            'permissions.required_if' => 'Please select some permissions',
            'existing_roles.required_if' => 'Please select some roles',
        ];
    }
}
