<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'comments' => 'nullable|string',
            'file_type' => 'nullable|string|max:50',
            'document_status_id' => 'nullable|integer|exists:document_statuses,id',
            'due_date' => 'nullable|date|after_or_equal:today',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif,zip,rar',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório',
            'title.max' => 'O título não pode ter mais de 255 caracteres',
            'description.max' => 'A descrição não pode ter mais de 1000 caracteres',
            'document_status_id.exists' => 'Status de documento inválido',
            'due_date.after_or_equal' => 'A data de vencimento deve ser igual ou posterior a hoje',
            'files.*.max' => 'Cada arquivo não pode ter mais de 10MB',
            'files.*.mimes' => 'Tipos de arquivo permitidos: pdf, doc, docx, xls, xlsx, ppt, pptx, txt, jpg, jpeg, png, gif, zip, rar',
        ];
    }
}
