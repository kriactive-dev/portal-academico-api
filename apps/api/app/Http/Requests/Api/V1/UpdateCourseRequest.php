<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
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
            'description' => ['sometimes', 'nullable', 'string'],
            'duration_months' => ['sometimes', 'required', 'integer', 'min:1'],
            'tuition' => ['sometimes', 'required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The course name is required.',
            'duration_months.required' => 'The duration in months is required.',
            'duration_months.integer' => 'The duration must be a whole number.',
            'tuition.required' => 'The tuition amount is required.',
            'tuition.numeric' => 'The tuition must be a valid number.',
            'is_active.required' => 'The active status is required.',
            'is_active.boolean' => 'The active status must be true or false.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        $data['duration_months'] = $this->input('duration_months') ?? $this->input('durationMonths');
        if ($this->has('is_active') || $this->has('isActive')) {
            $data['is_active'] = $this->input('is_active') ?? $this->input('isActive');
        }
        if ($data['duration_months'] !== null || $this->has('is_active') || $this->has('isActive')) {
            $this->merge($data);
        }
    }
}
