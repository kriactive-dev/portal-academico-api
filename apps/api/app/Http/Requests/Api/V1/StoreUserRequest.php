<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'roleId' => ['required', 'integer', 'exists:roles,id'],
            'isActive' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The user name is required.',
            'email.required' => 'The email address is required.',
            'email.unique' => 'This email is already in use.',
            'password.required' => 'The password is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'roleId.required' => 'The role is required.',
            'roleId.exists' => 'The selected role does not exist.',
            'isActive.required' => 'The active status is required.',
            'isActive.boolean' => 'The active status must be true or false.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'role_id' => $this->roleId,
            'is_active' => $this->isActive,
        ]);
    }
}
