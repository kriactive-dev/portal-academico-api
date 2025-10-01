<?php

namespace App\Http\Requests\Api\Library;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLibraryRequest extends FormRequest
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
        $libraryId = $this->route('id') ?? $this->route('library');
        
        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('libraries')->ignore($libraryId)
            ],
            'address' => 'sometimes|nullable|string|max:500',
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
            'name.unique' => 'Já existe uma biblioteca com este nome.',
            'address.string' => 'O endereço deve ser um texto.',
            'address.max' => 'O endereço não pode ter mais que 500 caracteres.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Dados de validação inválidos.',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
