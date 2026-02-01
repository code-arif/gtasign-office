<?php

namespace App\Http\Resources\Evaluator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampForEvaluatorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'director_id' => $this->director_id,
            'sports_type_name' => $this->sports_type_name,
            'camp_name' => $this->camp_name,
            'camp_logo' => $this->camp_logo ? asset($this->camp_logo) : asset('default/no_image.webp'),
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date->toDateString(),
            'price' => $this->price,
            'status' => $this->status,

            // Add director information
            'director' => $this->when($this->relationLoaded('director'), function () {
                return [
                    'id' => $this->director->id,
                    'name' => $this->director->first_name . ' ' . $this->director->last_name,
                    'email' => $this->director->email,
                    'phone' => $this->director->phone,
                    'avatar' => $this->director->avatar ? asset($this->director->avatar) : asset('default/profile.jpg'),
                ];
            }),

        ];
    }
}
