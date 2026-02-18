<?php

namespace App\Http\Resources\Inbox;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'other_user' => [
                'id' => $this->other_user?->id,
                'name' => $this->other_user?->profile?->first_name . ' ' . $this->other_user?->profile?->last_name,
                'username' => $this->other_user?->profile?->username,
                'avatar' => $this->other_user?->profile?->avatar ? asset('storage/' . $this->other_user?->profile?->avatar) : asset('default/profile.jpg'),
                // 'is_online' => $this->other_user->isOnline(),
                'is_online' => false, // TODO: Implement online status
                'pinned' => false, // TODO: Implement pinned status 
            ],
            'has_active_order' => $this->has_active_order,
            'unread_count' => $this->unread_count,
            'last_message' => $this->last_message ? [
                'text' => $this->last_message->text,
                'type' => $this->last_message->type,
                'created_at' => $this->last_message->created_at->diffForHumans(),
            ] : null,
            'last_message_at' => $this->last_message_at?->diffForHumans(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
