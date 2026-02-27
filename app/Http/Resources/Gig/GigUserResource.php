<?php

namespace App\Http\Resources\Gig;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GigUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->profile->first_name . ' ' . $this->profile->last_name ?? null,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->getAvatarUrl(),
            'level' =>$this->profile->level ?? null,
            'level_name' => $this->profile->level_name ?? null,
        ];
    }

    /**
     * Get full avatar URL
     */
    private function getAvatarUrl(): ?string
    {
        // If profile relationship is loaded and has avatar
        if ($this->relationLoaded('profile') && $this->profile && $this->profile->avatar) {
            return asset('storage/' . $this->profile->avatar);
        }

        // Return default avatar or null
        return asset('default/profile.jpg'); // or return null;
    }
}
