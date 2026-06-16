<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'isActive' => (bool) $this->is_active,
            'role' => $this->whenLoaded('roles', fn () => $this->roles->first()?->name),
            'createdAt' => $this->created_at,
        ];
    }
}
