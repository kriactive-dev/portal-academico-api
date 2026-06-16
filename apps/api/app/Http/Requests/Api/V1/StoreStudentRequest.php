<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
            'student_number' => ['required', 'string', 'max:255', 'unique:students,student_number'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:students,email'],
            'phone' => ['required', 'string', 'max:50'],
            'birth_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive,graduated'],
            'enrollment_date' => ['required', 'date'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:50'],
            'guardian_relationship' => ['nullable', 'string', 'max:255'],
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
        $this->merge([
            'student_number' => $this->input('student_number') ?? $this->input('studentNumber'),
            'birth_date' => $this->input('birth_date') ?? $this->input('birthDate'),
            'enrollment_date' => $this->input('enrollment_date') ?? $this->input('enrollmentDate'),
            'guardian_name' => $this->input('guardian_name') ?? $this->input('guardianName'),
            'guardian_phone' => $this->input('guardian_phone') ?? $this->input('guardianPhone'),
            'guardian_relationship' => $this->input('guardian_relationship') ?? $this->input('guardianRelationship'),
        ]);
    }
}
