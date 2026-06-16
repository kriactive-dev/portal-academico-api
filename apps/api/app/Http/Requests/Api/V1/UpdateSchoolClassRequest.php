<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolClassRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'course_id' => ['sometimes', 'required', 'integer', 'exists:courses,id'],
            'shift' => ['sometimes', 'required', 'in:morning,afternoon,evening'],
            'status' => ['sometimes', 'required', 'in:planned,in_progress,completed'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'trainer_ids' => ['sometimes', 'required', 'array', 'min:1'],
            'trainer_ids.*' => ['required', 'integer', 'exists:trainers,id'],
            'student_ids' => ['sometimes', 'required', 'array'],
            'student_ids.*' => ['required', 'integer', 'exists:students,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The class name is required.',
            'course_id.required' => 'The course is required.',
            'course_id.exists' => 'The selected course does not exist.',
            'shift.required' => 'The shift is required.',
            'shift.in' => 'The shift must be morning, afternoon, or evening.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be planned, in_progress, or completed.',
            'start_date.date' => 'The start date must be a valid date.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
            'trainer_ids.*.exists' => 'One or more selected trainers do not exist.',
            'student_ids.*.exists' => 'One or more selected students do not exist.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        $pairs = [
            'course_id' => 'courseId',
            'start_date' => 'startDate',
            'end_date' => 'endDate',
            'trainer_ids' => 'trainerIds',
            'student_ids' => 'studentIds',
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
