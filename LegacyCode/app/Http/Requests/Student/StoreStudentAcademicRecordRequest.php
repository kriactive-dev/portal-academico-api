<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentAcademicRecordRequest extends FormRequest
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
            'subject_code' => 'nullable|string|max:50',
            'subject_name' => 'nullable|string|max:255',
            'academic_year' => 'nullable|string|max:20',
            'semester' => 'nullable|string|max:20',
            'credits' => 'nullable|string|max:10',
            'grade' => 'nullable|string|max:20',
            'teacher_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'date' => 'nullable|date',
            'user_id' => 'nullable|integer|exists:users,id',
            'student_id' => 'nullable|integer|exists:users,id',
            'student_code' => 'nullable|string|max:50',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'subject_code.max' => 'O código da disciplina não pode ter mais de 50 caracteres.',
            'subject_name.max' => 'O nome da disciplina não pode ter mais de 255 caracteres.',
            'academic_year.max' => 'O ano acadêmico não pode ter mais de 20 caracteres.',
            'semester.max' => 'O semestre não pode ter mais de 20 caracteres.',
            'credits.max' => 'Os créditos não podem ter mais de 10 caracteres.',
            'grade.max' => 'A nota não pode ter mais de 20 caracteres.',
            'teacher_name.max' => 'O nome do professor não pode ter mais de 255 caracteres.',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres.',
            'date.date' => 'A data deve ter um formato válido.',
            'user_id.exists' => 'O usuário selecionado não existe.',
            'student_id.exists' => 'O estudante selecionado não existe.',
            'student_code.max' => 'O código do estudante não pode ter mais de 50 caracteres.',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'subject_code' => 'código da disciplina',
            'subject_name' => 'nome da disciplina',
            'academic_year' => 'ano acadêmico',
            'semester' => 'semestre',
            'credits' => 'créditos',
            'grade' => 'nota',
            'teacher_name' => 'nome do professor',
            'description' => 'descrição',
            'date' => 'data',
            'user_id' => 'usuário',
            'student_id' => 'estudante',
            'student_code' => 'código do estudante',
        ];
    }
}
