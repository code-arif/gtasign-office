<?php

namespace App\Http\Controllers\Api\Frontend\Referee;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\RefereeEvaluation;
use Modules\Director\Models\Camp;
use App\Http\Controllers\Controller;
use Modules\Director\Models\CampRefereeCheckin;

class EvaluatedRefereeController extends Controller
{
    use ApiResponse;

    /**
     * Get all evaluations for a specific referee (for referee's own view)
     * Camp specific
     */
    public function getRefereeEvaluations($campId)
    {
        $user = auth('api')->user();

        // Only referees can access their own evaluations
        if (!$user->hasRole('referee')) {
            return $this->error([], 'This endpoint is only for referees.', 403);
        }

        // Check if camp exists
        $camp = Camp::find($campId);
        if (!$camp) {
            return $this->error([], 'Camp not found.', 404);
        }

        // Check if ranking is published for referees
        // if (!$camp->publish_ranking_for_referees) {
        //     return $this->error([], 'Evaluations are not yet published for referees in this camp.', 403);
        // }

        // Check if referee is registered for this camp
        $checkin = CampRefereeCheckin::where('camp_id', $campId)
            ->where('referee_id', $user->id)
            ->first();

        if (!$checkin) {
            return $this->error([], 'You are not registered for this camp.', 403);
        }

        // Get all evaluations for this referee in this camp
        $evaluations = RefereeEvaluation::with(['evaluator', 'camp', 'gameSlot', 'recommendedLevels'])
            ->forReferee($user->id)
            ->where('camp_id', $campId)
            ->submitted()
            ->orderBy('submitted_at', 'desc')
            ->get();

        if ($evaluations->isEmpty()) {
            return $this->success(
                'No evaluations found for this camp.',
                [
                    'camp' => [
                        'id' => $camp->id,
                        'name' => $camp->camp_name,
                        'location' => $camp->location,
                        'start_date' => $camp->start_date,
                        'end_date' => $camp->end_date,
                    ],
                    'overall_performance' => null,
                    'performance_breakdown' => null,
                    'recent_feedback' => [],
                ]
            );
        }

        // Calculate overall averages
        $avgCallAccuracy = round($evaluations->avg('call_accuracy'), 3);
        $avgCommunication = round($evaluations->avg('communication_skills'), 3);
        $avgConsistency = round($evaluations->avg('consistency_of_calls'), 3);
        $avgCourtPosition = round($evaluations->avg('court_position_mechanics'), 3);
        $avgFitness = round($evaluations->avg('fitness_mobility'), 3);
        $avgGameAwareness = round($evaluations->avg('game_awareness'), 3);

        // Calculate overall average
        $overallAvg = round(($avgCallAccuracy + $avgCommunication + $avgConsistency +
            $avgCourtPosition + $avgFitness + $avgGameAwareness) / 6, 3);

        // Overall percentage (out of 10)
        $overallPercentage = round(($overallAvg / 10) * 100, 3);

        // Determine performance rating
        $performanceRating = $this->getPerformanceRating($overallAvg);

        // ============================================
        // CALCULATE RANKING POSITION
        // ============================================
        $rankingData = $this->calculateRefereeRanking($campId, $user->id, $overallAvg);

        // Get recommended levels with count
        $recommendedLevels = [];
        foreach ($evaluations as $evaluation) {
            foreach ($evaluation->recommendedLevels as $level) {
                if (isset($recommendedLevels[$level->level])) {
                    $recommendedLevels[$level->level]++;
                } else {
                    $recommendedLevels[$level->level] = 1;
                }
            }
        }

        // Format recommended levels
        $recommendedLevelsFormatted = [];
        foreach ($recommendedLevels as $level => $count) {
            $recommendedLevelsFormatted[] = [
                'level' => $level,
                'count' => $count
            ];
        }

        // Sort by count descending
        usort($recommendedLevelsFormatted, fn($a, $b) => $b['count'] <=> $a['count']);

        // Get highest recommended level
        $highestRecommendedLevel = !empty($recommendedLevelsFormatted)
            ? $recommendedLevelsFormatted[0]['level']
            : null;

        // Recent Feedback (last 5 with feedback)
        $recentFeedback = $evaluations->filter(function ($eval) {
            return !empty($eval->referee_feedback);
        })->take(10)->map(function ($eval) use ($camp) {
            $feedback = [
                'id' => $eval->id,
                'camp_name' => $eval->camp->camp_name,
                'total_score' => (float) $eval->total_score,
                'max_score' => 60,
                'feedback' => $eval->referee_feedback,
                'submitted_at' => $eval->submitted_at->format('M j, Y'),
            ];

            // Add evaluator name only if not hidden
            if (!$camp->hide_evaluator_name_from_referees) {
                $feedback['evaluator_name'] = $eval->evaluator->first_name . ' ' . $eval->evaluator->last_name;
            } else {
                $feedback['evaluator_name'] = 'Anonymous Evaluator';
            }

            return $feedback;
        })->values();

        // Build response
        $response = [
            'camp' => [
                'id' => $camp->id,
                'name' => $camp->camp_name,
                'location' => $camp->location,
                'start_date' => $camp->start_date,
                'end_date' => $camp->end_date,
            ],
            'overall_performance' => [
                'score' => $overallAvg,
                'max_score' => 10,
                'percentage' => $overallPercentage,
                'rating' => $performanceRating,
                'total_evaluations' => $evaluations->count(),
            ],
            'ranking' => $rankingData,
            'performance_breakdown' => [
                'call_accuracy' => [
                    'score' => $avgCallAccuracy,
                    'max_score' => 10,
                ],
                'communication_skills' => [
                    'score' => $avgCommunication,
                    'max_score' => 10,
                ],
                'consistency_of_calls' => [
                    'score' => $avgConsistency,
                    'max_score' => 10,
                ],
                'court_position_mechanics' => [
                    'score' => $avgCourtPosition,
                    'max_score' => 10,
                ],
                'fitness_mobility' => [
                    'score' => $avgFitness,
                    'max_score' => 10,
                ],
                'game_awareness' => [
                    'score' => $avgGameAwareness,
                    'max_score' => 10,
                ],
            ],
            'recommended_levels' => $recommendedLevelsFormatted,
            'highest_recommended_level' => $highestRecommendedLevel,
            'recent_feedback' => $recentFeedback,
            'settings' => [
                'hide_evaluator_name' => $camp->hide_evaluator_name_from_referees,
                'hide_ranking_numbers' => $camp->hide_ranking_numbers_from_referees,
            ],
        ];

        // If ranking numbers are hidden, remove numerical scores
        if ($camp->hide_ranking_numbers_from_referees) {
            // Hide ranking position
            $response['ranking'] = null;

            // Keep scores but you can modify this based on requirements
            $response['overall_performance']['score'] = $overallAvg;

            // Remove scores from recent feedback
            $response['recent_feedback'] = $recentFeedback->map(function ($feedback) {
                // unset($feedback['total_score']);
                // unset($feedback['max_score']);
                return $feedback;
            });
        }

        return $this->success(
            'Your evaluations retrieved successfully.',
            $response
        );
    }

    /**
     * Calculate referee's ranking position in the camp
     *
     * @param int $campId
     * @param int $refereeId
     * @param float $currentRefereeAvg - Current referee's overall average
     * @return array
     */
    private function calculateRefereeRanking($campId, $refereeId, $currentRefereeAvg)
    {
        // Get all referees who have been evaluated in this camp
        $allRefereeScores = RefereeEvaluation::select('referee_id')
            ->selectRaw('
                ROUND(
                    (AVG(call_accuracy) +
                     AVG(communication_skills) +
                     AVG(consistency_of_calls) +
                     AVG(court_position_mechanics) +
                     AVG(fitness_mobility) +
                     AVG(game_awareness)) / 6,
                    1
                ) as overall_average
            ')
            ->where('camp_id', $campId)
            ->where('status', 'submitted')
            ->groupBy('referee_id')
            ->having('overall_average', '>', 0)
            ->orderByDesc('overall_average')
            ->get();

        $totalReferees = $allRefereeScores->count();

        if ($totalReferees == 0) {
            return null;
        }

        // Find current referee's position
        $position = 1;
        foreach ($allRefereeScores as $index => $score) {
            if ($score->referee_id == $refereeId) {
                $position = $index + 1;
                break;
            }
        }

        // Calculate percentile (higher is better)
        $percentile = 100 - (($position - 1) / $totalReferees * 100);

        // Determine ranking category
        $rankingCategory = '';
        if ($percentile >= 90) {
            $rankingCategory = 'Top 10%';
        } elseif ($percentile >= 75) {
            $rankingCategory = 'Top 25%';
        } elseif ($percentile >= 50) {
            $rankingCategory = 'Top 50%';
        } else {
            $rankingCategory = 'Below Average';
        }

        return [
            'position' => $position,
            // 'total_referees' => $totalReferees,
            // 'percentile' => round($percentile, 1),
            // 'category' => $rankingCategory,
            // 'message' => "You ranked #{$position} out of {$totalReferees} referees",
        ];
    }

    /**
     * Helper function to get performance rating from score
     */
    private function getPerformanceRating($score)
    {
        if ($score >= 9) {
            return 'Excellent';
        } elseif ($score >= 7) {
            return 'Good';
        } elseif ($score >= 5) {
            return 'Average';
        } else {
            return 'Needs Improvement';
        }
    }
}
