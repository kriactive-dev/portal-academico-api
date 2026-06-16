<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studentId' => ['required', 'integer', 'exists:students,id'],
            'courseId' => ['required', 'integer', 'exists:courses,id'],
            'referenceMonth' => ['required', 'string', 'max:7'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:paid,pending,overdue'],
            'method' => ['nullable', 'in:transfer,cash,mpesa,emola,deposit'],
            'paymentDate' => ['nullable', 'date'],
            'dueDate' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'studentId.required' => 'The student is required.',
            'studentId.exists' => 'The selected student does not exist.',
            'courseId.required' => 'The course is required.',
            'courseId.exists' => 'The selected course does not exist.',
            'referenceMonth.required' => 'The reference month is required.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be paid, pending, or overdue.',
            'method.in' => 'The payment method must be one of: transfer, cash, mpesa, emola, or deposit.',
            'paymentDate.date' => 'The payment date must be a valid date.',
            'dueDate.required' => 'The due date is required.',
            'dueDate.date' => 'The due date must be a valid date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'student_id' => $this->studentId,
            'course_id' => $this->courseId,
            'reference_month' => $this->referenceMonth,
            'payment_date' => $this->paymentDate,
            'due_date' => $this->dueDate,
        ]);
    }
}
