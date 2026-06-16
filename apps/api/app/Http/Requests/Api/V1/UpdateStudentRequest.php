<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'student_number' => ['sometimes', 'required', 'string', 'max:255', 'unique:students,student_number,' . $this->route('student')],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:students,email,' . $this->route('student')],
            'phone' => ['sometimes', 'required', 'string', 'max:50'],
            'birth_date' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', 'in:active,inactive,graduated'],
            'enrollment_date' => ['sometimes', 'required', 'date'],
            'guardian_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'guardian_phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'guardian_relationship' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_number.required' => 'The student number is required.',
            'student_number.unique' => 'This student number is already in use.',
            'name.required' => 'The student name is required.',
            'email.required' => 'The email address is required.',
            'email.unique' => 'This email is already in use.',
            'phone.required' => 'The phone number is required.',
            'birth_date.required' => 'The birth date is required.',
            'birth_date.date' => 'The birth date must be a valid date.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be one of: active, inactive, or graduated.',
            'enrollment_date.required' => 'The enrollment date is required.',
            'enrollment_date.date' => 'The enrollment date must be a valid date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        $pairs = [
            'student_number' => 'studentNumber',
            'birth_date' => 'birthDate',
            'enrollment_date' => 'enrollmentDate',
            'guardian_name' => 'guardianName',
            'guardian_phone' => 'guardianPhone',
            'guardian_relationship' => 'guardianRelationship',
        ];
        foreach ($pairs as $snake => $camel) {
            if ($this->has($snake) || $this->has($camel)) {
                $data[$snake] = $this->input($snake) ?? $this->input($camel);
            }
        }
        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
