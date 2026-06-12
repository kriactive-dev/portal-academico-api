<?php

namespace App\Http\Requests\Api\RolePermission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class AssignRoleRequest extends FormRequest
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
        return [
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required_without:role_ids|exists:roles,id',
            'role_ids' => 'required_without:role_id|array',
            'role_ids.*' => 'exists:roles,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'O ID do usuário é obrigatório.',
            'user_id.exists' => 'O usuário não existe.',
            'role_id.required_without' => 'O ID da role é obrigatório quando role_ids não é fornecido.',
            'role_id.exists' => 'A role não existe.',
            'role_ids.required_without' => 'Os IDs das roles são obrigatórios quando role_id não é fornecido.',
            'role_ids.array' => 'Os IDs das roles devem ser um array.',
            'role_ids.*.exists' => 'Uma ou mais roles não existem.',
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
