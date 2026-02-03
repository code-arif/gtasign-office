<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EducationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'country'          => $this->country,
            'institution_name' => $this->institution_name,
            'degree'           => $this->degree,
            'major'            => $this->major,
            'graduation_year'  => (int) $this->graduation_year,

            'created_at'       => $this->created_at?->format('y-m-d | H:i A'),
            'updated_at'       => $this->updated_at?->format('y-m-d | H:i A'),
        ];
    }
}
