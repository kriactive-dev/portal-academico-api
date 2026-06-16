<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'durationMonths' => ['required', 'integer', 'min:1'],
            'tuition' => ['required', 'numeric', 'min:0'],
            'isActive' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The course name is required.',
            'durationMonths.required' => 'The duration in months is required.',
            'durationMonths.integer' => 'The duration must be a whole number.',
            'tuition.required' => 'The tuition amount is required.',
            'tuition.numeric' => 'The tuition must be a valid number.',
            'isActive.required' => 'The active status is required.',
            'isActive.boolean' => 'The active status must be true or false.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'duration_months' => $this->durationMonths,
            'is_active' => $this->isActive,
        ]);
    }
}
