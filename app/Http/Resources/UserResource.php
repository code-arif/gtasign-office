<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Profile; // Add this line

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'email'      => $this->email,
            'status'     => $this->status,
            'role'       => $this->role ?? null,
            'created_at' => $this->created_at->toDateTimeString(),

            // Profile info
            'profile' => $this->whenLoaded('profile', function() {
                return [
                    'first_name' => $this->profile->first_name,
                    'last_name'  => $this->profile->last_name,
                    'username'   => $this->profile->username,
                    'slug'       => $this->profile->slug,
                    'avatar'     => $this->profile->avatar,
                    'tagline'    => $this->profile->tagline,
                    'biography'  => $this->profile->biography,
                    'address'    => $this->profile->address,
                ];
            }),
        ];
    }
}
