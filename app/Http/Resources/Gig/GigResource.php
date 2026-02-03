<?php

namespace App\Http\Resources\Gig;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GigResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => [
                'id' => $this->category_id,
                'name' => $this->category?->name,
            ],
            'sub_category' => $this->when($this->sub_category_id, [
                'id' => $this->sub_category_id,
                'name' => $this->subCategory?->name,
            ]),
            'search_tags' => $this->search_tags ?? [],
            'scope' => $this->scope,
            'price' => $this->price ? (float) $this->price : null,
            'delivery_days' => $this->delivery_days,
            'secaax_questions' => $this->secaax_questions ?? [],
            'custom_questions' => $this->custom_questions ?? [],
            'images' => $this->images ? array_map(function ($image) {
                return asset('storage/' . $image);
            }, $this->images) : [],
            'documents' => $this->documents ? array_map(function ($document) {
                return [
                    'name' => basename($document),
                    'url' => asset('storage/' . $document),
                ];
            }, $this->documents) : [],
            'status' => $this->status,
            'rejection_reason' => $this->when($this->status === 'rejected', $this->rejection_reason),
            'analytics' => [
                'impressions' => $this->impressions,
                'clicks' => $this->clicks,
                'orders' => $this->orders,
                'cancellations' => $this->cancellations,
                'cancellation_rate' => $this->cancellation_rate,
            ],
            'user' => $this->when($this->relationLoaded('user'), [
                'id' => $this->user?->id,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
            ]),
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
