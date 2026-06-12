<?php

namespace App\Http\Requests\Api\RolePermission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $roleId = $this->route('id') ?? $this->route('role');
        
        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($roleId)
            ],
            'guard_name' => 'sometimes|string|in:web,api',
            'permission_ids' => 'sometimes|nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais que 255 caracteres.',
            'name.unique' => 'Já existe uma role com este nome.',
            'guard_name.string' => 'O guard name deve ser um texto.',
            'guard_name.in' => 'O guard name deve ser web ou api.',
            'permission_ids.array' => 'As permissões devem ser um array.',
            'permission_ids.*.exists' => 'Uma ou mais permissões não existem.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Dados de validação inválidos.',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
