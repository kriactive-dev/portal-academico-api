<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'courseId' => ['required', 'integer', 'exists:courses,id'],
            'shift' => ['required', 'in:morning,afternoon,evening'],
            'status' => ['required', 'in:planned,in_progress,completed'],
            'startDate' => ['required', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'trainerIds' => ['required', 'array', 'min:1'],
            'trainerIds.*' => ['required', 'integer', 'exists:trainers,id'],
            'studentIds' => ['required', 'array'],
            'studentIds.*' => ['required', 'integer', 'exists:students,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The class name is required.',
            'courseId.required' => 'The course is required.',
            'courseId.exists' => 'The selected course does not exist.',
            'shift.required' => 'The shift is required.',
            'shift.in' => 'The shift must be morning, afternoon, or evening.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be planned, in_progress, or completed.',
            'startDate.required' => 'The start date is required.',
            'startDate.date' => 'The start date must be a valid date.',
            'endDate.date' => 'The end date must be a valid date.',
            'endDate.after_or_equal' => 'The end date must be on or after the start date.',
            'trainerIds.required' => 'At least one trainer must be assigned.',
            'trainerIds.*.exists' => 'One or more selected trainers do not exist.',
            'studentIds.*.exists' => 'One or more selected students do not exist.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'course_id' => $this->courseId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);
    }
}
