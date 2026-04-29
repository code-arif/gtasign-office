<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Support both direct User model and ['user' => ..., 'stats' => ...] array
        $user  = is_array($this->resource) ? $this->resource['user']  : $this->resource;
        $stats = is_array($this->resource) ? $this->resource['stats'] : null;

        return [
            'id'         => $user->id,
            'email'      => $user->email,
            'status'     => $user->status,
            'role'       => $user->role ?? null,
            'created_at' => $user->created_at->toDateTimeString(),

            // ── Profile ───────────────────────────────────────────────
            'profile' => $user->relationLoaded('profile') && $user->profile
                ? [
                    'first_name' => $user->profile->first_name,
                    'last_name'  => $user->profile->last_name,
                    'username'   => $user->profile->username,
                    'slug'       => $user->profile->slug,
                    'avatar'     => $user->profile->avatar
                        ? asset('storage/' . $user->profile->avatar)
                        : asset('default/profile.jpg'),
                    'tagline'    => $user->profile->tagline,
                    'biography'  => $user->profile->biography,
                    'address'    => $user->profile->address,
                ]
                : null,

            // ── Client Stats ──────────────────────────────────────────
            'stats' => $stats ? [
                'total_reviews'    => $stats['total_reviews']    ?? 0,
                'avg_rating'       => $stats['avg_rating']       ?? 0,
                'rating_breakdown' => $stats['rating_breakdown'] ?? [],
                'recent_reviews'   => $stats['recent_reviews']   ?? [],
                'received_replies' => $stats['received_replies'] ?? [],
            ] : null,

            // ── Languages ─────────────────────────────────────────────
            'languages' => $user->relationLoaded('languages')
                ? $user->languages->map(function ($language) {
                    return [
                        'id'           => $language->id,
                        'name'         => $language->name,
                        'display_name' => $language->display_name,
                        'proficiency'  => $language->pivot->proficiency,
                    ];
                })
                : [],
        ];
    }
}
