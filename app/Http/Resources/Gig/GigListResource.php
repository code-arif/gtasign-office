<?php

namespace App\Http\Resources\Gig;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GigListResource extends JsonResource
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
            'category' => $this->category?->name,
            'price' => $this->price ? (float) $this->price : null,
            'delivery_days' => $this->delivery_days,
            'thumbnail' => $this->images && count($this->images) > 0
                ? asset('storage/' . $this->images[0])
                : null,
            'status' => $this->status,
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
            ]),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
