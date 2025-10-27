<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
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
        $courseId = $this->route('id');
        
        return [
            'name' => 'required|string|max:255',
            'course_code' => 'nullable|string|max:50|unique:courses,course_code,' . $courseId,
            'description' => 'nullable|string',
            'duration' => 'nullable|string|max:100',
            'responsible' => 'nullable|string|max:255',
            'university_id' => 'nullable|exists:universities,id',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do curso é obrigatório.',
            'name.string' => 'O nome do curso deve ser uma string.',
            'name.max' => 'O nome do curso não pode ter mais de 255 caracteres.',
            'course_code.string' => 'O código do curso deve ser uma string.',
            'course_code.max' => 'O código do curso não pode ter mais de 50 caracteres.',
            'course_code.unique' => 'Este código de curso já está em uso.',
            'description.string' => 'A descrição deve ser uma string.',
            'duration.string' => 'A duração deve ser uma string.',
            'duration.max' => 'A duração não pode ter mais de 100 caracteres.',
            'responsible.string' => 'O responsável deve ser uma string.',
            'responsible.max' => 'O responsável não pode ter mais de 255 caracteres.',
            'university_id.exists' => 'A universidade selecionada não existe.',
        ];
    }
}
