<?php

namespace Modules\Director\Transformers\CourtAssign;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameSlotRefereeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'assignment_id' => $this->id,                    // GameSlotAssignment er id
            'referee_id'    => $this->assignable_id,         // since polymorphic User
            'name'          => $this->assignable->full_name ?? ($this->assignable->first_name . ' ' . $this->assignable->last_name),
            'email'         => $this->assignable->email,
            'avatar'        => $this->assignable->avatar ? asset($this->assignable->avatar) : asset('default/profile.jpg'),
            // jodi extra field lagbe, add korte paro
        ];
    }
}
