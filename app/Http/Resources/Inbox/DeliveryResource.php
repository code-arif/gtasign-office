<?php

namespace App\Http\Resources\Inbox;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $files = [];
        if ($this->files && is_array($this->files)) {
            foreach ($this->files as $file) {
                $files[] = [
                    'path' => $file,
                    'url' => Storage::disk('public')->url($file),
                    'name' => basename($file),
                ];
            }
        }

        return [
            'id' => $this->id,
            'delivery_number' => $this->delivery_number,
            'message' => $this->message,
            'files' => $files,
            'status' => $this->status,
            'revision_reason' => $this->revision_reason,
            'qa_feedback' => $this->qa_feedback,
            'submitted_at' => $this->submitted_at->format('Y-m-d H:i:s'),
            'qa_reviewed_at' => $this->qa_reviewed_at?->format('Y-m-d H:i:s'),
            'delivered_to_client_at' => $this->delivered_to_client_at?->format('Y-m-d H:i:s'),
            'client_reviewed_at' => $this->client_reviewed_at?->format('Y-m-d H:i:s'),
        ];
    }
}
