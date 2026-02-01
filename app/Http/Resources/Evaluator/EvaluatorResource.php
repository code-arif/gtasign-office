<?php

namespace App\Http\Resources\Evaluator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluatorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name'      => $this->first_name . ' ' . $this->last_name ?? null,
            'email' => $this->email,
            'avatar' => $this->avatar ? asset('' . $this->avatar) : asset('default/profile.jpg'),
        ];
    }
}
