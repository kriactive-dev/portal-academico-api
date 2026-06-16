<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:enrollment,registration,exam,certificate,other'],
            'amount' => ['required', 'numeric', 'min:0'],
            'courseId' => ['nullable', 'integer', 'exists:courses,id'],
            'isActive' => ['required', 'boolean'],
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
        $this->merge([
            'course_id' => $this->courseId,
            'is_active' => $this->isActive,
        ]);
    }
}
