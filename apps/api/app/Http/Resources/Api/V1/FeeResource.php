<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class FeeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'courseId' => $this->course_id,
            'course' => new CourseResource($this->whenLoaded('course')),
            'isActive' => (bool) $this->is_active,
            'createdAt' => $this->created_at,
        ];
    }
}
