<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeeRequest extends FormRequest
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
            'type' => ['sometimes', 'required', 'in:enrollment,registration,exam,certificate,other,monthly'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'course_id' => ['sometimes', 'nullable', 'integer', 'exists:courses,id'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The fee name is required.',
            'type.required' => 'The fee type is required.',
            'type.in' => 'The fee type must be one of: enrollment, registration, exam, certificate, monthly, or other.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'course_id.exists' => 'The selected course does not exist.',
            'is_active.required' => 'The active status is required.',
            'is_active.boolean' => 'The active status must be true or false.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        $data['course_id'] = $this->input('course_id') ?? $this->input('courseId');
        if ($this->has('is_active') || $this->has('isActive')) {
            $data['is_active'] = $this->input('is_active') ?? $this->input('isActive');
        }
        if ($data['course_id'] !== null || isset($data['is_active'])) {
            $this->merge($data);
        }
    }
}
