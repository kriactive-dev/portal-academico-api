<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequestEntryRequest extends FormRequest
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
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'type' => ['required', 'in:certificate,internship_approval'],
            'status' => ['required', 'in:pending,approved,denied'],
            'submission_date' => ['required', 'date'],
            'details' => ['required', 'array'],
            'details.course_id' => ['required_if:type,certificate', 'integer', 'exists:courses,id'],
            'details.purpose' => ['required_if:type,certificate', 'string', 'max:500'],
            'details.urgent' => ['required_if:type,certificate', 'boolean'],
            'details.company' => ['required_if:type,internship_approval', 'string', 'max:255'],
            'details.internship_role' => ['required_if:type,internship_approval', 'string', 'max:255'],
            'details.internship_start_date' => ['required_if:type,internship_approval', 'date'],
            'details.internship_end_date' => ['required_if:type,internship_approval', 'date', 'after_or_equal:details.internship_start_date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'The student is required.',
            'student_id.exists' => 'The selected student does not exist.',
            'type.required' => 'The request type is required.',
            'type.in' => 'The type must be certificate or internship_approval.',
            'details.required' => 'The details are required.',
            'details.course_id.required_if' => 'The course is required for certificate requests.',
            'details.course_id.exists' => 'The selected course does not exist.',
            'details.purpose.required_if' => 'The purpose is required for certificate requests.',
            'details.urgent.required_if' => 'The urgent flag is required for certificate requests.',
            'details.urgent.boolean' => 'The urgent flag must be true or false.',
            'details.company.required_if' => 'The company name is required for internship approval requests.',
            'details.internship_role.required_if' => 'The internship role is required for internship approval requests.',
            'details.internship_start_date.required_if' => 'The internship start date is required for internship approval requests.',
            'details.internship_start_date.date' => 'The internship start date must be a valid date.',
            'details.internship_end_date.required_if' => 'The internship end date is required for internship approval requests.',
            'details.internship_end_date.date' => 'The internship end date must be a valid date.',
            'details.internship_end_date.after_or_equal' => 'The internship end date must be on or after the start date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'student_id' => $this->input('student_id') ?? $this->input('studentId'),
            'submission_date' => now(),
        ]);

        $details = $this->input('details');
        if (is_array($details)) {
            $mapped = [];
            foreach ($details as $key => $value) {
                $snake = str($key)->snake()->toString();
                $mapped[$snake] = $value;
            }
            $this->merge(['details' => $mapped]);
        }
    }
}
