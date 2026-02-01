<?php

namespace Modules\Director\Transformers\Referee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckedInRefereeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id'     => $this->id,
            'referee_id' =>$this->referee->id,
            'name'   => $this->referee->first_name . ' ' . $this->referee->last_name,
            'email'  => $this->referee->email,
            'phone'  => $this->referee->phone,
            'address'  => $this->referee->address,
            'avatar' => $this->referee->avatar
                ? asset('' . $this->referee->avatar)
                : asset('default/profile.jpg'),
        ];
    }
}
