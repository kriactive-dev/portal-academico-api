<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studentNumber' => ['sometimes', 'required', 'string', 'max:255', 'unique:students,student_number,' . $this->route('student')],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:students,email,' . $this->route('student')],
            'phone' => ['sometimes', 'required', 'string', 'max:50'],
            'birthDate' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', 'in:active,inactive,graduated'],
            'enrollmentDate' => ['sometimes', 'required', 'date'],
            'guardianName' => ['sometimes', 'nullable', 'string', 'max:255'],
            'guardianPhone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'guardianRelationship' => ['sometimes', 'nullable', 'string', 'max:255'],
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
        if ($this->has('studentNumber')) {
            $this->merge(['student_number' => $this->studentNumber]);
        }
        if ($this->has('birthDate')) {
            $this->merge(['birth_date' => $this->birthDate]);
        }
        if ($this->has('enrollmentDate')) {
            $this->merge(['enrollment_date' => $this->enrollmentDate]);
        }
        if ($this->has('guardianName')) {
            $this->merge(['guardian_name' => $this->guardianName]);
        }
        if ($this->has('guardianPhone')) {
            $this->merge(['guardian_phone' => $this->guardianPhone]);
        }
        if ($this->has('guardianRelationship')) {
            $this->merge(['guardian_relationship' => $this->guardianRelationship]);
        }
    }
}
