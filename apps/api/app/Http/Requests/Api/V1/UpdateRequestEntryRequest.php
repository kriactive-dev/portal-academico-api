<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequestEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studentId' => ['sometimes', 'required', 'integer', 'exists:students,id'],
            'type' => ['sometimes', 'required', 'in:certificate,internship_approval'],
            'status' => ['sometimes', 'required', 'in:pending,approved,denied'],
            'details' => ['sometimes', 'required', 'array'],
            'details.courseId' => ['sometimes', 'required', 'integer', 'exists:courses,id'],
            'details.purpose' => ['sometimes', 'required', 'string', 'max:500'],
            'details.urgent' => ['sometimes', 'required', 'boolean'],
            'details.company' => ['sometimes', 'required', 'string', 'max:255'],
            'details.internshipRole' => ['sometimes', 'required', 'string', 'max:255'],
            'details.internshipStartDate' => ['sometimes', 'required', 'date'],
            'details.internshipEndDate' => ['sometimes', 'required', 'date', 'after_or_equal:details.internshipStartDate'],
        ];
    }

    public function messages(): array
    {
        return [
            'studentId.required' => 'The student is required.',
            'studentId.exists' => 'The selected student does not exist.',
            'type.required' => 'The request type is required.',
            'type.in' => 'The type must be certificate or internship_approval.',
            'status.in' => 'The status must be pending, approved, or denied.',
            'details.required' => 'The details are required.',
            'details.courseId.required' => 'The course is required for certificate requests.',
            'details.courseId.exists' => 'The selected course does not exist.',
            'details.urgent.boolean' => 'The urgent flag must be true or false.',
            'details.internshipStartDate.date' => 'The internship start date must be a valid date.',
            'details.internshipEndDate.date' => 'The internship end date must be a valid date.',
            'details.internshipEndDate.after_or_equal' => 'The internship end date must be on or after the start date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('studentId')) {
            $this->merge(['student_id' => $this->studentId]);
        }

        $details = $this->details;
        if (is_array($details)) {
            if (isset($details['courseId'])) {
                $details['cursoId'] = $details['courseId'];
            }
            if (isset($details['purpose'])) {
                $details['finalidade'] = $details['purpose'];
            }
            if (isset($details['urgent'])) {
                $details['urgente'] = $details['urgent'];
            }
            if (isset($details['company'])) {
                $details['empresa'] = $details['company'];
            }
            if (isset($details['internshipRole'])) {
                $details['cargoEstagio'] = $details['internshipRole'];
            }
            if (isset($details['internshipStartDate'])) {
                $details['dataInicioEstagio'] = $details['internshipStartDate'];
            }
            if (isset($details['internshipEndDate'])) {
                $details['dataFimEstagio'] = $details['internshipEndDate'];
            }
            $this->merge(['details' => $details]);
        }
    }
}
