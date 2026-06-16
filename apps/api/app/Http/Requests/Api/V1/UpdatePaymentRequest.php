<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studentId' => ['sometimes', 'required', 'integer', 'exists:students,id'],
            'courseId' => ['sometimes', 'required', 'integer', 'exists:courses,id'],
            'referenceMonth' => ['sometimes', 'required', 'string', 'max:7'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'status' => ['sometimes', 'required', 'in:paid,pending,overdue'],
            'method' => ['sometimes', 'nullable', 'in:transfer,cash,mpesa,emola,deposit'],
            'paymentDate' => ['sometimes', 'nullable', 'date'],
            'dueDate' => ['sometimes', 'required', 'date'],
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
        if ($this->has('studentId')) {
            $this->merge(['student_id' => $this->studentId]);
        }
        if ($this->has('courseId')) {
            $this->merge(['course_id' => $this->courseId]);
        }
        if ($this->has('referenceMonth')) {
            $this->merge(['reference_month' => $this->referenceMonth]);
        }
        if ($this->has('paymentDate')) {
            $this->merge(['payment_date' => $this->paymentDate]);
        }
        if ($this->has('dueDate')) {
            $this->merge(['due_date' => $this->dueDate]);
        }
    }
}
