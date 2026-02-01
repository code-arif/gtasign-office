<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefereeEvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = auth('api')->user();
        $isEvaluator = $user && $user->id === $this->evaluator_id;
        $isDirector = $user && $user->hasRole('director');

        return [
            'id' => $this->id,
            'referee' => [
                'id' => $this->referee_id,
                'name' => $this->referee->first_name . ' ' . $this->referee->last_name,
                'email' => $this->referee->email,
                'avatar' => $this->referee->avatar ? asset($this->referee->avatar) : asset('default/profile.jpg'),
            ],
            'evaluator' => [
                'id' => $this->evaluator_id,
                'name' => $this->evaluator->first_name . ' ' . $this->evaluator->last_name,
                'role' => $this->evaluator->getRoleNames()->first(),
            ],
            'camp' => [
                'id' => $this->camp_id,
                'name' => $this->camp->camp_name,
                'location' => $this->camp->location,
            ],
            'game_slot' => $this->when($this->game_slot_id, [
                'id' => $this->gameSlot?->id,
                'date' => $this->gameSlot?->game_date,
                'time' => $this->gameSlot?->start_time . ' - ' . $this->gameSlot?->end_time,
                'court' => $this->gameSlot?->court_name,
            ]),
            'performance_criteria' => [
                'call_accuracy' => $this->call_accuracy,
                'communication_skills' => $this->communication_skills,
                'consistency_of_calls' => $this->consistency_of_calls,
                'court_position_mechanics' => $this->court_position_mechanics,
                'fitness_mobility' => $this->fitness_mobility,
                'game_awareness' => $this->game_awareness,
            ],
            'total_score' => (float) $this->total_score,
            'average_score' => (float) $this->average_score,
            'max_score' => 60,
            'percentage' => $this->total_score ? round(($this->total_score / 60) * 100, 2) : 0,

            // Private comments - only visible to evaluator and directors
            'private_comments' => $this->when(
                $isEvaluator || $isDirector,
                $this->private_comments
            ),

            // Referee feedback - visible to referee, evaluator, and directors
            'referee_feedback' => $this->referee_feedback,

            // 'recommended_level' => $this->whenLoaded('recommendedLevels', function () {
            //     return $this->recommendedLevels->first()?->level;
            // }),

            'recommended_level' => $this->relationLoaded('recommendedLevels')
                ? $this->recommendedLevels->first()?->level
                : null,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at?->format('Y-m-d H:i:s'),
        ];
    }
}
