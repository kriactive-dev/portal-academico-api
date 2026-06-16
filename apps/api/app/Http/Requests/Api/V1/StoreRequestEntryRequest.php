<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequestEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studentId' => ['required', 'integer', 'exists:students,id'],
            'type' => ['required', 'in:certificate,internship_approval'],
            'details' => ['required', 'array'],
            'details.courseId' => ['required_if:type,certificate', 'integer', 'exists:courses,id'],
            'details.purpose' => ['required_if:type,certificate', 'string', 'max:500'],
            'details.urgent' => ['required_if:type,certificate', 'boolean'],
            'details.company' => ['required_if:type,internship_approval', 'string', 'max:255'],
            'details.internshipRole' => ['required_if:type,internship_approval', 'string', 'max:255'],
            'details.internshipStartDate' => ['required_if:type,internship_approval', 'date'],
            'details.internshipEndDate' => ['required_if:type,internship_approval', 'date', 'after_or_equal:details.internshipStartDate'],
        ];
    }

    public function messages(): array
    {
        return [
            'studentId.required' => 'The student is required.',
            'studentId.exists' => 'The selected student does not exist.',
            'type.required' => 'The request type is required.',
            'type.in' => 'The type must be certificate or internship_approval.',
            'details.required' => 'The details are required.',
            'details.courseId.required_if' => 'The course is required for certificate requests.',
            'details.courseId.exists' => 'The selected course does not exist.',
            'details.purpose.required_if' => 'The purpose is required for certificate requests.',
            'details.urgent.required_if' => 'The urgent flag is required for certificate requests.',
            'details.urgent.boolean' => 'The urgent flag must be true or false.',
            'details.company.required_if' => 'The company name is required for internship approval requests.',
            'details.internshipRole.required_if' => 'The internship role is required for internship approval requests.',
            'details.internshipStartDate.required_if' => 'The internship start date is required for internship approval requests.',
            'details.internshipStartDate.date' => 'The internship start date must be a valid date.',
            'details.internshipEndDate.required_if' => 'The internship end date is required for internship approval requests.',
            'details.internshipEndDate.date' => 'The internship end date must be a valid date.',
            'details.internshipEndDate.after_or_equal' => 'The internship end date must be on or after the start date.',
        ];
    }

    protected function prepareForValidation(): void
    {
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
        }

        $this->merge([
            'student_id' => $this->studentId,
            'details' => $details ?? $this->details,
            'submission_date' => now(),
        ]);
    }
}
