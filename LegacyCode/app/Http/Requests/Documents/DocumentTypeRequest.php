<?php

namespace App\Http\Requests\Documents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentTypeRequest extends FormRequest
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
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
        ];

        // Para update, adicionar regra de unique excluindo o próprio registro
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $documentTypeId = $this->route('document_type') ?? $this->route('id');
            $rules['name'][] = Rule::unique('document_types', 'name')->ignore($documentTypeId)->whereNull('deleted_at');
        } else {
            // Para create
            $rules['name'][] = Rule::unique('document_types', 'name')->whereNull('deleted_at');
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do tipo de documento é obrigatório.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'name.min' => 'O nome deve ter pelo menos 2 caracteres.',
            'name.unique' => 'Já existe um tipo de documento com este nome.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome do tipo de documento',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpar e formatar o nome
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }
    }
}