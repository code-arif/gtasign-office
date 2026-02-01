<?php

namespace Modules\Director\Transformers\Referee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class AvailableRefereeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'     => $this->id,
            'name'   => $this->first_name . ' ' . $this->last_name,
            'email'  => $this->email,
            'phone'  => $this->phone,
            'avatar' => $this->avatar
                ? asset('' . $this->avatar)
                : asset('default/profile.jpg'),
        ];
    }
}
