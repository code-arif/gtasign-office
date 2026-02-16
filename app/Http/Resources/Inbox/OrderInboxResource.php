<?php

namespace App\Http\Resources\Inbox;

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
            'gig' => $this->when($this->gig, [
                'id' => $this->gig?->id,
                'title' => $this->gig?->title,
            ]),
            'buyer' => [
                'id' => $this->buyer->id,
                'name' => $this->buyer->profile->full_name,
            ],
            'seller' => [
                'id' => $this->seller->id,
                'name' => $this->seller->profile->full_name,
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
