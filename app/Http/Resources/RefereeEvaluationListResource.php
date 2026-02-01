<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefereeEvaluationListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = auth('api')->user();
        $isEvaluator = $user && $user->id === $this->evaluator_id;
        $isDirector = $user && $user->hasRole('director');

        // Get recommended levels for this specific referee across all evaluations
        $refereeLevels = $this->getRefereeLevelsSummary();

        return [
            'id' => $this->id,
            'referee' => [
                'id' => $this->referee_id,
                'name' => trim($this->referee->first_name . ' ' . $this->referee->last_name),
                'email' => $this->referee->email,
                'avatar' => $this->referee->avatar ? asset($this->referee->avatar) : asset('default/profile.jpg'),
                'recommended_levels' => $refereeLevels,
            ],
            'evaluator' => [
                'id' => $this->evaluator_id,
                'name' => trim($this->evaluator->first_name . ' ' . $this->evaluator->last_name),
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
            'private_comments' => $this->when(
                $isEvaluator || $isDirector,
                $this->private_comments
            ),
            'referee_feedback' => $this->referee_feedback,
            'current_recommended_level' => $this->relationLoaded('recommendedLevels')
                ? $this->recommendedLevels->first()?->level
                : null,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get referee's recommended levels summary across all evaluations
     */
    private function getRefereeLevelsSummary(): array
    {
        // Get all evaluations for this referee with recommended levels
        $allEvaluations = \App\Models\RefereeEvaluation::where('referee_id', $this->referee_id)
            ->with('recommendedLevels')
            ->has('recommendedLevels')
            ->get();

        if ($allEvaluations->isEmpty()) {
            return [
                'total_evaluations_with_recommendations' => 0,
                'levels' => [],
                'most_recommended' => null,
            ];
        }

        // Collect all recommended levels
        $allLevels = $allEvaluations->flatMap(function ($evaluation) {
            return $evaluation->recommendedLevels->pluck('level');
        })->toArray();

        // Count occurrences
        $levelCounts = array_count_values($allLevels);

        // Format as array of objects with level name and count
        $levelsArray = [];
        foreach ($levelCounts as $level => $count) {
            $levelsArray[] = [
                'level' => $level,
                'count' => $count,
            ];
        }

        // Sort by count descending
        usort($levelsArray, fn($a, $b) => $b['count'] <=> $a['count']);

        // Get most recommended
        $mostRecommended = !empty($levelsArray) ? $levelsArray[0] : null;

        return [
            'total_evaluations_with_recommendations' => $allEvaluations->count(),
            'levels' => $levelsArray,
            'most_recommended' => $mostRecommended,
        ];
    }
}
