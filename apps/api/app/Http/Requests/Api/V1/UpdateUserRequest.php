<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,' . $this->route('user')],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'role_id' => ['sometimes', 'required', 'integer', 'exists:roles,id'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The user name is required.',
            'email.required' => 'The email address is required.',
            'email.unique' => 'This email is already in use.',
            'password.required' => 'The password is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'role_id.required' => 'The role is required.',
            'role_id.exists' => 'The selected role does not exist.',
            'is_active.required' => 'The active status is required.',
            'is_active.boolean' => 'The active status must be true or false.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        if ($this->has('role_id') || $this->has('roleId')) {
            $data['role_id'] = $this->input('role_id') ?? $this->input('roleId');
        }
        if ($this->has('is_active') || $this->has('isActive')) {
            $data['is_active'] = $this->input('is_active') ?? $this->input('isActive');
        }
        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
