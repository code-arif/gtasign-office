<?php

namespace App\Http\Resources\Evaluator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampRegistraionsListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'registration_note' => $this->registration_note,
            'rejection_reason' => $this->rejection_reason,
            'registered_at' => $this->registered_at,
            'approved_at' => $this->approved_at,
            'rejected_at' => $this->rejected_at,

            // evaluator nested – limited fields only
            'evaluator' => [
                'id' => $this->evaluator->id,
                'name' => $this->evaluator->first_name . ' ' . $this->evaluator->last_name ?? null,
                'email' => $this->evaluator->email,
                'phone' => $this->evaluator->phone,
                'address' => $this->evaluator->address,
                'avatar' => $this->avatar ? asset('' . $this->avatar) : asset('default/profile.jpg'),
            ],
        ];
    }
}
