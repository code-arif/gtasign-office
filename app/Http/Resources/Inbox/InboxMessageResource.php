<?php

namespace App\Http\Resources\Inbox;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InboxMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'text' => $this->text,
            'file' => $this->file ? asset('storage/' . $this->file) : null,
            'status' => $this->status,
            'is_sender' => $this->sender_id === auth()->id(),
            'sender' => [
                'id' => $this->sender?->id,
                'name' => $this->sender->profile?->first_name . ' ' . $this->sender->profile?->last_name,
                'role' => $this->sender->role,
                'avatar' => $this->sender->profile?->avatar ? asset('storage/' . $this->sender->profile?->avatar) : asset('default/profile.jpg'),
            ],
            'metadata' => $this->metadata,

            // Include related data based on message type
            'custom_offer' => $this->when(
                $this->type === 'custom_offer' && $this->customOffer,
                new CustomOfferResource($this->customOffer)
            ),
            'order' => $this->when(
                in_array($this->type, ['order_placed', 'delivery_submitted', 'delivery_sent', 'order_completed']),
                new OrderInboxResource($this->order)
            ),
            'delivery' => $this->when(
                in_array($this->type, ['delivery_submitted', 'delivery_approved', 'delivery_sent']),
                new DeliveryResource($this->delivery)
            ),
            'extension' => $this->when(
                in_array($this->type, ['extension_request', 'extension_approved', 'extension_rejected']),
                fn() => $this->extensionRequest 
                    ? ExtensionRequestResource::make($this->extensionRequest)
                    : null
            ),

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'time_ago' => $this->created_at->diffForHumans(),
        ];
    }
}
