<?php

namespace App\Http\Requests\University;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUniversityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Assume user is authenticated via middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $university = $this->route('university');
        
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('universities', 'name')->ignore($university->id ?? null)
            ],
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('universities', 'code')->ignore($university->id ?? null)
            ],
            'description' => 'nullable|string|max:5000',
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:50',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('universities', 'email')->ignore($university->id ?? null)
            ],
            'website' => 'nullable|url|max:255',
            'logo_url' => 'nullable|url|max:500',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome da universidade é obrigatório.',
            'name.unique' => 'Já existe uma universidade com este nome.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'code.unique' => 'Este código já está sendo usado por outra universidade.',
            'code.max' => 'O código não pode ter mais de 20 caracteres.',
            'description.max' => 'A descrição não pode ter mais de 5000 caracteres.',
            'address.max' => 'O endereço não pode ter mais de 1000 caracteres.',
            'phone.max' => 'O telefone não pode ter mais de 50 caracteres.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.unique' => 'Este email já está being usado por outra universidade.',
            'website.url' => 'O website deve ser uma URL válida.',
            'logo_url.url' => 'A URL do logo deve ser uma URL válida.',
            'is_active.boolean' => 'O status deve ser verdadeiro ou falso.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'code' => 'código',
            'description' => 'descrição',
            'address' => 'endereço',
            'phone' => 'telefone',
            'email' => 'email',
            'website' => 'website',
            'logo_url' => 'logo',
            'is_active' => 'status',
        ];
    }
}
