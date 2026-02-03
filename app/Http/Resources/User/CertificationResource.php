<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'awarded_by' => $this->awarded_by,
            'year' => $this->year,
            'file_url' => $this->file_path ? asset('storage/' . $this->file_path) : null,
            'created_at' => $this->created_at->format('y-m-d | H:i A'),
        ];
    }
}
