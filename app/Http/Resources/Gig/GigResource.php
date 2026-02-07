<?php

namespace App\Http\Resources\Gig;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'title'   => $this->title,

            'gallery' => [
                'images' => GigImageResource::collection(
                    $this->whenLoaded('images')
                ),

                'documents' => GigDocumentResource::collection(
                    $this->whenLoaded('documents')
                ),
            ],
        ];
    }
}
