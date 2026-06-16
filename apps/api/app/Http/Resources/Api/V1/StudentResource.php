<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'studentNumber' => $this->student_number,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'birthDate' => $this->birth_date,
            'status' => $this->status,
            'enrollmentDate' => $this->enrollment_date,
            'guardianName' => $this->guardian_name,
            'guardianPhone' => $this->guardian_phone,
            'guardianRelationship' => $this->guardian_relationship,
            'schoolClasses' => SchoolClassResource::collection($this->whenLoaded('school_classes')),
            'payments' => [
                'count' => $this->whenCounted('payments'),
            ],
            'createdAt' => $this->created_at,
        ];
    }
}
