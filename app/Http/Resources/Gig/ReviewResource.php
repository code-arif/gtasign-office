<?php

namespace App\Http\Resources\Gig;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        $reviewer = $this->reviewer?->profile;

        return [
            'id'         => $this->id,
            'rating'     => $this->rating,
            'review'     => $this->review,
            'reply'      => $this->seller_reply,
            'replied_at' => $this->replied_at?->diffForHumans(),
            'created_at' => $this->created_at?->diffForHumans(),

            'reviewer' => [
                'id'       => $this->reviewer_id,
                'name'     => $reviewer
                    ? trim($reviewer->first_name . ' ' . ($reviewer->last_name ?? ''))
                    : 'Anonymous',
                'username' => $reviewer?->username,
                'avatar'   => $reviewer?->avatar
                    ? asset('storage/' . $reviewer->avatar)
                    : asset('default/profile.jpg'),
            ],

            // Helpful for client to know if they can still reply
            'can_reply'  => $this->whenLoaded(
                'reviewedUser',
                fn() =>
                auth('api')->id() === $this->reviewed_user_id && !$this->seller_reply
            ),
        ];
    }
}
