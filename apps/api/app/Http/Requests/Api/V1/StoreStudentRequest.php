<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studentNumber' => ['required', 'string', 'max:255', 'unique:students,student_number'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:students,email'],
            'phone' => ['required', 'string', 'max:50'],
            'birthDate' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive,graduated'],
            'enrollmentDate' => ['required', 'date'],
            'guardianName' => ['nullable', 'string', 'max:255'],
            'guardianPhone' => ['nullable', 'string', 'max:50'],
            'guardianRelationship' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'studentNumber.required' => 'The student number is required.',
            'studentNumber.unique' => 'This student number is already in use.',
            'name.required' => 'The student name is required.',
            'email.required' => 'The email address is required.',
            'email.unique' => 'This email is already in use.',
            'phone.required' => 'The phone number is required.',
            'birthDate.required' => 'The birth date is required.',
            'birthDate.date' => 'The birth date must be a valid date.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be one of: active, inactive, or graduated.',
            'enrollmentDate.required' => 'The enrollment date is required.',
            'enrollmentDate.date' => 'The enrollment date must be a valid date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'student_number' => $this->studentNumber,
            'birth_date' => $this->birthDate,
            'enrollment_date' => $this->enrollmentDate,
            'guardian_name' => $this->guardianName,
            'guardian_phone' => $this->guardianPhone,
            'guardian_relationship' => $this->guardianRelationship,
        ]);
    }
}
