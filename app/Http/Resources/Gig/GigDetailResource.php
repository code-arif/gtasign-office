<?php

namespace App\Http\Resources\Gig;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GigDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'scope' => $this->scope,

            'category' => new CategoryResource($this->whenLoaded('category')),
            'sub_category' => new CategoryResource($this->whenLoaded('subCategory')),

            'price' => (float) $this->price,
            'delivery_days' => $this->delivery_days,

            'system_questions' => $this->system_questions ?? [],
            'custom_questions' => $this->custom_questions ?? [],

            'images' => GigImageResource::collection(
                $this->whenLoaded('images')
            ),

            'documents' => GigDocumentResource::collection(
                $this->whenLoaded('documents')
            ),

            'tags' => TagResource::collection(
                $this->whenLoaded('tags')
            ),

            'analytics' => new GigAnalyticsResource($this->resource),

            'reviews_count' => $this->reviews_count ?? 0,

            'average_rating' => $this->reviews_avg_rating
                ? round((float) $this->reviews_avg_rating, 1)
                : 0,

            'reviews' => ReviewResource::collection(
                $this->whenLoaded('reviews')
            ),

            'status' => $this->status,
            'rejection_reason' => $this->when(
                $this->status === 'rejected',
                $this->rejection_reason
            ),

            'user' => new GigUserResource($this->whenLoaded('user')),

            'room_id' => $this->room_id ?? null, // room থাকলে ID, না থাকলে null

            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
