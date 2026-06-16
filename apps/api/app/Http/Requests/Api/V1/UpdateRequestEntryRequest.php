<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequestEntryRequest extends FormRequest
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
            'student_id' => ['sometimes', 'required', 'integer', 'exists:students,id'],
            'type' => ['sometimes', 'required', 'in:certificate,internship_approval'],
            'status' => ['sometimes', 'required', 'in:pending,approved,denied'],
            'details' => ['sometimes', 'required', 'array'],
            'details.course_id' => ['sometimes', 'required', 'integer', 'exists:courses,id'],
            'details.purpose' => ['sometimes', 'required', 'string', 'max:500'],
            'details.urgent' => ['sometimes', 'required', 'boolean'],
            'details.company' => ['sometimes', 'required', 'string', 'max:255'],
            'details.internship_role' => ['sometimes', 'required', 'string', 'max:255'],
            'details.internship_start_date' => ['sometimes', 'required', 'date'],
            'details.internship_end_date' => ['sometimes', 'required', 'date', 'after_or_equal:details.internship_start_date'],
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
            'status.in' => 'The status must be pending, approved, or denied.',
            'details.required' => 'The details are required.',
            'details.course_id.required' => 'The course is required for certificate requests.',
            'details.course_id.exists' => 'The selected course does not exist.',
            'details.urgent.boolean' => 'The urgent flag must be true or false.',
            'details.internship_start_date.date' => 'The internship start date must be a valid date.',
            'details.internship_end_date.date' => 'The internship end date must be a valid date.',
            'details.internship_end_date.after_or_equal' => 'The internship end date must be on or after the start date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        if ($this->has('student_id') || $this->has('studentId')) {
            $data['student_id'] = $this->input('student_id') ?? $this->input('studentId');
        }
        if (!empty($data)) {
            $this->merge($data);
        }

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
