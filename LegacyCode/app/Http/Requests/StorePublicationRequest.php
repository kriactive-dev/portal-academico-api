<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicationRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:65535',
            'university_name' => 'nullable|string',
            'year' => 'nullable|string',
            'university_id' => 'nullable|integer',
            'course_id' => 'nullable|integer',

            'expires_at' => 'nullable|date|after:today',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif|max:10240', // 10MB
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.string' => 'O título deve ser um texto válido.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            
            'body.required' => 'O conteúdo é obrigatório.',
            'body.string' => 'O conteúdo deve ser um texto válido.',
            'body.max' => 'O conteúdo não pode ter mais de 65535 caracteres.',
            
            'expires_at.date' => 'A data de expiração deve ser uma data válida.',
            'expires_at.after' => 'A data de expiração deve ser posterior a hoje.',
            
            'file.file' => 'Deve ser um arquivo válido.',
            'file.mimes' => 'O arquivo deve ser do tipo: pdf, doc, docx, xls, xlsx, ppt, pptx, txt, jpg, jpeg, png, gif.',
            'file.max' => 'O arquivo não pode ser maior que 10MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'título',
            'body' => 'conteúdo',
            'expires_at' => 'data de expiração',
            'file' => 'arquivo',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpar dados antes da validação
        if ($this->has('title')) {
            $this->merge([
                'title' => trim($this->title),
            ]);
        }

        if ($this->has('body')) {
            $this->merge([
                'body' => trim($this->body),
            ]);
        }
    }
}
