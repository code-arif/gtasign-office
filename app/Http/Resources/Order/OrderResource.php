<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'status' => $this->status,
            'price' => (float) $this->price,
            'delivery_days' => $this->delivery_days,
            'expected_delivery_at' => $this->expected_delivery_at?->format('Y-m-d H:i:s'),
            'days_until_delivery' => $this->daysUntilDelivery(),
            'is_late' => $this->isLate(),
            'room_id' => $this->room_id,
            'gig' => $this->when($this->gig, [
                'id' => $this->gig?->id,
                'title' => $this->gig?->title,
                // Primary Image
                'image' => $this->gig?->primaryImage
                    ? asset( 'storage/' . $this->gig?->primaryImage->path)
                    : asset('default/no_image.webp'),
            ]),
            'buyer' => [
                'id' => $this->buyer->id,
                'name' => $this->buyer->profile->full_name,
                'avatar' => $this->buyer->profile->avatar_url ? asset($this->buyer->profile->avatar_url) : asset('default/profile.jpg'),
            ],
            'seller' => [
                'id' => $this->seller->id,
                'name' => $this->seller->profile->full_name,
                'avatar' => $this->seller->profile->avatar_url ? asset($this->seller->profile->avatar_url) : asset('default/profile.jpg'),
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
