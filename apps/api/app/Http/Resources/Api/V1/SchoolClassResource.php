<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class SchoolClassResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'courseId' => $this->course_id,
            'course' => new CourseResource($this->whenLoaded('course')),
            'trainers' => TrainerResource::collection($this->whenLoaded('trainers')),
            'students' => StudentResource::collection($this->whenLoaded('students')),
            'shift' => $this->shift,
            'status' => $this->status,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
            'createdAt' => $this->created_at,
        ];
    }
}
