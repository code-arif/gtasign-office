<?php

namespace App\Http\Resources\Gig;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GigImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // 'url' => asset('storage/' . $this->path),
            'url' => $this->path
                ? asset('storage/' . $this->path)
                : asset('default/profile.jpg'),
            'is_primary' => (bool) $this->is_primary,
            'sort_order' => $this->sort_order,
        ];
    }
}
