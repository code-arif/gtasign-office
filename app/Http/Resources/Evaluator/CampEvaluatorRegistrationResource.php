<?php

namespace App\Http\Resources\Evaluator;

use Illuminate\Http\Request;
use Modules\Director\Transformers\CampResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CampEvaluatorRegistrationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'camp_id' => $this->camp_id,
            'evaluator_id' => $this->evaluator_id,
            'status' => $this->status,
            'registration_note' => $this->registration_note,
            'registered_at' => $this->registered_at,
            'camp' => new CampForEvaluatorResource($this->whenLoaded('camp')),
            'evaluator' => new EvaluatorResource($this->whenLoaded('evaluator')),
        ];
    }
}
