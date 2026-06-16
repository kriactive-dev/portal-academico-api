<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'in:enrollment,registration,exam,certificate,other'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'courseId' => ['sometimes', 'nullable', 'integer', 'exists:courses,id'],
            'isActive' => ['sometimes', 'required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The fee name is required.',
            'type.required' => 'The fee type is required.',
            'type.in' => 'The fee type must be one of: enrollment, registration, exam, certificate, or other.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'courseId.exists' => 'The selected course does not exist.',
            'isActive.required' => 'The active status is required.',
            'isActive.boolean' => 'The active status must be true or false.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('courseId')) {
            $this->merge(['course_id' => $this->courseId]);
        }
        if ($this->has('isActive')) {
            $this->merge(['is_active' => $this->isActive]);
        }
    }
}
