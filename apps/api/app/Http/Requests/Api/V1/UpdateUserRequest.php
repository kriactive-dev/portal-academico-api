<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,' . $this->route('user')],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'roleId' => ['sometimes', 'required', 'integer', 'exists:roles,id'],
            'isActive' => ['sometimes', 'required', 'boolean'],
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
        if ($this->has('roleId')) {
            $this->merge(['role_id' => $this->roleId]);
        }
        if ($this->has('isActive')) {
            $this->merge(['is_active' => $this->isActive]);
        }
    }
}
