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
                    'first_name'  => $user->profile->first_name,
                    'last_name'   => $user->profile->last_name,
                    'username'    => $user->profile->username,
                    'slug'        => $user->profile->slug,
                    'avatar'      => $user->profile->avatar
                        ? asset('storage/' . $user->profile->avatar)
                        : asset('default/profile.jpg'),
                    'tagline'     => $user->profile->tagline,
                    'biography'   => $user->profile->biography,
                    'address'     => $user->profile->address,
                    'level'       => $user->profile->level      ?? null,
                    'level_name'  => $user->profile->level_name ?? null,
                ]
                : null,

            // ── Expert Stats (only for expert role) ───────────────────
            'stats' => $stats ? [
                'avg_rating'           => $stats['avg_rating'],
                'total_reviews'        => $stats['total_reviews'],
                'avg_communication'    => $stats['avg_communication'],
                'avg_service'          => $stats['avg_service'],
                'avg_delivery'         => $stats['avg_delivery'],
                'success_score'        => $stats['success_score'],       // 86 → show as "86%"
                'last_month_earnings'  => $stats['last_month_earnings'],  // 436.00
                'last_month_label'     => $stats['last_month_label'],     // "November"
                'avg_response_minutes' => $stats['avg_response_minutes'], // 24
            ] : null,

            // Languages
            'languages' => $user->relationLoaded('languages')
                ? $user->languages->map(function ($language) {
                    return [
                        'id' => $language->id,
                        'name' => $language->name,
                        'display_name' => $language->display_name,
                        'proficiency' => $language->pivot->proficiency,
                    ];
                })
                : [],
        ];
    }
}
