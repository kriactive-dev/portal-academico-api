<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentFinancialRecordRequest extends FormRequest
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
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'user_id' => 'nullable|integer|exists:users,id',
            'student_id' => 'nullable|integer|exists:users,id',
            'student_code' => 'nullable|string|max:50',
            'payment_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'date.required' => 'A data é obrigatória.',
            'date.date' => 'A data deve ter um formato válido.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser numérico.',
            'amount.min' => 'O valor deve ser maior ou igual a zero.',
            'description.max' => 'A descrição não pode ter mais de 255 caracteres.',
            'status.max' => 'O status não pode ter mais de 50 caracteres.',
            'user_id.exists' => 'O usuário selecionado não existe.',
            'student_id.exists' => 'O estudante selecionado não existe.',
            'student_code.max' => 'O código do estudante não pode ter mais de 50 caracteres.',
            'payment_method.max' => 'O método de pagamento não pode ter mais de 100 caracteres.',
            'notes.max' => 'As notas não podem ter mais de 1000 caracteres.',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'date' => 'data',
            'amount' => 'valor',
            'description' => 'descrição',
            'status' => 'status',
            'user_id' => 'usuário',
            'student_id' => 'estudante',
            'student_code' => 'código do estudante',
            'payment_method' => 'método de pagamento',
            'notes' => 'notas',
        ];
    }
}
