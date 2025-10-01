<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('id') ?? $this->route('user');
        
        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'password' => 'sometimes|string|min:6',
            'email_verified' => 'sometimes|boolean',
            'roles' => 'sometimes|array',
            'roles.*' => 'string|exists:roles,name',
            
            // Dados do perfil (opcionais)
            'profile' => 'sometimes|array',
            'profile.phone' => 'sometimes|nullable|string|max:20',
            'profile.address' => 'sometimes|nullable|string|max:255',
            'profile.city' => 'sometimes|nullable|string|max:100',
            'profile.country' => 'sometimes|nullable|string|max:100',
            'profile.postal_code' => 'sometimes|nullable|string|max:20',
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
            'email.email' => 'O email deve ter um formato válido.',
            'email.unique' => 'Este email já está sendo usado.',
            'email.max' => 'O email não pode ter mais que 255 caracteres.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'email_verified.boolean' => 'O campo email verificado deve ser verdadeiro ou falso.',
            'roles.array' => 'As roles devem ser uma lista.',
            'roles.*.exists' => 'Uma ou mais roles não existem.',
            'profile.array' => 'Os dados do perfil devem ser um objeto.',
            'profile.phone.max' => 'O telefone não pode ter mais que 20 caracteres.',
            'profile.address.max' => 'O endereço não pode ter mais que 255 caracteres.',
            'profile.city.max' => 'A cidade não pode ter mais que 100 caracteres.',
            'profile.country.max' => 'O país não pode ter mais que 100 caracteres.',
            'profile.postal_code.max' => 'O código postal não pode ter mais que 20 caracteres.',
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
