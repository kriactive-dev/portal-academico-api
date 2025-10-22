<?php

namespace App\Http\Requests\Api\Library;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreateBookRequest extends FormRequest
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
            'author' => 'nullable|string|max:255',
            'editor' => 'nullable|string|max:255',
            'cdu' => 'nullable|string|max:100',
            'topic' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:100',
            'launch_date' => 'nullable|string|max:100',
            'launch_place' => 'nullable|string|max:255',
            'library_id' => 'nullable|exists:libraries,id',
            'description' => 'nullable|string',
            'publisher' => 'nullable|string',
            'published_year' => 'nullable|string',
            'isbn' => 'nullable|string',
            // Arquivos
            'book_file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:50000', // 50MB
            'book_cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5000', // 5MB
            'book_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5000', // 5MB
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.string' => 'O título deve ser um texto.',
            'title.max' => 'O título não pode ter mais que 255 caracteres.',
            'author.string' => 'O autor deve ser um texto.',
            'author.max' => 'O autor não pode ter mais que 255 caracteres.',
            'editor.string' => 'A editora deve ser um texto.',
            'editor.max' => 'A editora não pode ter mais que 255 caracteres.',
            'cdu.max' => 'O CDU não pode ter mais que 100 caracteres.',
            'topic.max' => 'O tópico não pode ter mais que 255 caracteres.',
            'edition.max' => 'A edição não pode ter mais que 100 caracteres.',
            'launch_date.max' => 'A data de lançamento não pode ter mais que 100 caracteres.',
            'launch_place.max' => 'O local de lançamento não pode ter mais que 255 caracteres.',
            'library_id.exists' => 'A biblioteca selecionada não existe.',
            'book_file.file' => 'O arquivo do livro deve ser um arquivo válido.',
            'book_file.mimes' => 'O arquivo do livro deve ser PDF, DOC, DOCX ou TXT.',
            'book_file.max' => 'O arquivo do livro não pode ser maior que 50MB.',
            'cover_file.image' => 'A capa deve ser uma imagem.',
            'cover_file.mimes' => 'A capa deve ser JPEG, PNG, JPG ou GIF.',
            'cover_file.max' => 'A capa não pode ser maior que 5MB.',
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
