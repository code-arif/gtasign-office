<?php

namespace App\Http\Resources\Inbox;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExtensionRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'additional_days' => $this->additional_days,
            'reason' => $this->reason,
            'status' => $this->status,
            'requested_by' => [
                'id' => $this->requester->id,
                'name' => $this->requester->profile->full_name,
            ],
            'requested_at' => $this->requested_at->format('Y-m-d H:i:s'),
            'responded_at' => $this->responded_at?->format('Y-m-d H:i:s'),
        ];
    }
}
