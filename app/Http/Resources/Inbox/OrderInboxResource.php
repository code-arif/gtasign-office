<?php

namespace App\Http\Resources\Inbox;

use App\Models\OrderReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderInboxResource extends JsonResource
{
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
            'max_revisions' => $this->max_revisions,
            'revision_count' => $this->revision_count,
            'can_request_revision' => $this->canRequestRevision(),
            'can_request_extension' => $this->canRequestExtension(),
            'can_accept' => $this->canAccept(),
            'quantity' => $this->gig?->price
                ? (int) round($this->price / $this->gig->price)
                : 1,
            'gig' => $this->when($this->gig, [
                'id' => $this->gig?->id,
                'title' => $this->gig?->title,
                'image' => $this->gig?->images?->first()?->path ? asset('storage/' . $this->gig?->images?->first()?->path) : asset('default/profile.jpg'),
                'delivery_days' => $this->gig?->delivery_days,
                'price' => $this->gig?->price,
            ]),
            'buyer' => [
                'id' => $this->buyer->id,
                'name' => $this->buyer->profile->first_name . ' ' . $this->buyer->profile->last_name,
                'avatar' => $this->buyer->profile?->avatar ? asset('storage/' . $this->buyer->profile?->avatar) : asset('default/profile.jpg'),
                'address' => $this->buyer->profile?->address,
            ],
            'seller' => [
                'id' => $this->seller->id,
                'name' => $this->seller->profile->first_name . ' ' . $this->seller->profile->last_name,
                'avatar' => $this->seller->profile?->avatar ? asset('storage/' . $this->seller->profile?->avatar) : asset('default/profile.jpg'),
                'level' => $this->seller->profile->level,
                'level_name' => $this->seller->profile->level_name,
                'average_rating' => round(OrderReview::where('reviewed_user_id', $this->seller->id)->avg('rating') ?? 0, 1),
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
