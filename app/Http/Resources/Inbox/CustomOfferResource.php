<?php

namespace App\Http\Resources\Inbox;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomOfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => (float) $this->price,
            'delivery_days' => $this->delivery_days,
            'revisions' => $this->revisions,
            'status' => $this->status,
            'gig' => $this->when($this->gig, [
                'id' => $this->gig?->id,
                'title' => $this->gig?->title,
            ]),
            'expert' => [
                'id' => $this->expert->id,
                'name' => $this->expert->profile->full_name,
                'username' => $this->expert->profile->username,
            ],
            'expires_at' => $this->expires_at->format('Y-m-d H:i:s'),
            'expires_in' => $this->expires_at->diffForHumans(),
            'is_expired' => $this->isExpired(),
            'can_accept' => $this->canAccept(),
            'accepted_at' => $this->accepted_at?->format('Y-m-d H:i:s'),
            'rejected_at' => $this->rejected_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
