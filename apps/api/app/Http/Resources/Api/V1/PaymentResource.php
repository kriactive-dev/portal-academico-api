<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Payment */
class PaymentResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'studentId' => $this->student_id,
            'student' => [
                'id' => $this->whenLoaded('student', fn () => $this->student->id),
                'name' => $this->whenLoaded('student', fn () => $this->student->name),
                'studentNumber' => $this->whenLoaded('student', fn () => $this->student->student_number),
            ],
            'courseId' => $this->course_id,
            'course' => [
                'id' => $this->whenLoaded('course', fn () => $this->course->id),
                'name' => $this->whenLoaded('course', fn () => $this->course->name),
            ],
            'referenceMonth' => $this->reference_month,
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'method' => $this->method,
            'paymentDate' => $this->payment_date,
            'dueDate' => $this->due_date,
            'createdAt' => $this->created_at,
        ];
    }
}
