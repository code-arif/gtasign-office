<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserExperienceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'skill_name' => $this->skill_name,
            'level' => $this->level,
            'created_at' => $this->created_at?->format('y-m-d | H:i A'),
            'updated_at' => $this->updated_at?->format('y-m-d | H:i A'),
        ];
    }
}
