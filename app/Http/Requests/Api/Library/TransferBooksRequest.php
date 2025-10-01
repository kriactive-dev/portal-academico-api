<?php

namespace App\Http\Requests\Api\Library;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class TransferBooksRequest extends FormRequest
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
            'from_library_id' => 'required|exists:libraries,id',
            'to_library_id' => 'required|exists:libraries,id|different:from_library_id',
            'book_ids' => 'required|array|min:1',
            'book_ids.*' => 'required|exists:books,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'from_library_id.required' => 'A biblioteca de origem é obrigatória.',
            'from_library_id.exists' => 'A biblioteca de origem não existe.',
            'to_library_id.required' => 'A biblioteca de destino é obrigatória.',
            'to_library_id.exists' => 'A biblioteca de destino não existe.',
            'to_library_id.different' => 'A biblioteca de destino deve ser diferente da biblioteca de origem.',
            'book_ids.required' => 'A lista de livros é obrigatória.',
            'book_ids.array' => 'A lista de livros deve ser um array.',
            'book_ids.min' => 'Deve ser selecionado pelo menos um livro.',
            'book_ids.*.exists' => 'Um ou mais livros não existem.',
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
