<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
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
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'reference_month' => ['required', 'string', 'max:7'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:paid,pending,overdue'],
            'method' => ['nullable', 'in:transfer,cash,mpesa,emola,deposit'],
            'payment_date' => ['nullable', 'date'],
            'due_date' => ['required', 'date'],
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
        $this->merge([
            'student_id' => $this->input('student_id') ?? $this->input('studentId'),
            'course_id' => $this->input('course_id') ?? $this->input('courseId'),
            'reference_month' => $this->input('reference_month') ?? $this->input('referenceMonth'),
            'payment_date' => $this->input('payment_date') ?? $this->input('paymentDate'),
            'due_date' => $this->input('due_date') ?? $this->input('dueDate'),
        ]);
    }
}
