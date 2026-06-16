<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
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
            'course_id' => ['sometimes', 'required', 'integer', 'exists:courses,id'],
            'reference_month' => ['sometimes', 'required', 'string', 'max:7'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'status' => ['sometimes', 'required', 'in:paid,pending,overdue'],
            'method' => ['sometimes', 'nullable', 'in:transfer,cash,mpesa,emola,deposit'],
            'payment_date' => ['sometimes', 'nullable', 'date'],
            'due_date' => ['sometimes', 'required', 'date'],
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
            'course_id.required' => 'The course is required.',
            'course_id.exists' => 'The selected course does not exist.',
            'reference_month.required' => 'The reference month is required.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be paid, pending, or overdue.',
            'method.in' => 'The payment method must be one of: transfer, cash, mpesa, emola, or deposit.',
            'payment_date.date' => 'The payment date must be a valid date.',
            'due_date.required' => 'The due date is required.',
            'due_date.date' => 'The due date must be a valid date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        $pairs = [
            'student_id' => 'studentId',
            'course_id' => 'courseId',
            'reference_month' => 'referenceMonth',
            'payment_date' => 'paymentDate',
            'due_date' => 'dueDate',
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
