<?php

namespace App\Http\Resources\Gig;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class GigDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'name' => basename($this->path),
            'url'  => Storage::disk('public')->url($this->path),
        ];
    }
}
