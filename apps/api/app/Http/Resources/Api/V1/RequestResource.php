<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
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
            'type' => $this->type,
            'status' => $this->status,
            'submissionDate' => $this->submission_date,
            'responseDate' => $this->response_date,
            'denialReason' => $this->denial_reason,
            'details' => $this->details,
            'createdAt' => $this->created_at,
        ];
    }
}
