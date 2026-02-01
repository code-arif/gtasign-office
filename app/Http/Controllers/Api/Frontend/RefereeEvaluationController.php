<?php

namespace App\Http\Controllers\Api\Frontend;

use Exception;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\RecommendedLevel;
use App\Models\RefereeEvaluation;
use Modules\Director\Models\Camp;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\CampEvaluatorRegistration;
use App\Http\Requests\RefereeEvaluationRequest;
use Modules\Director\Models\CampRefereeCheckin;
use App\Http\Resources\RefereeEvaluationResource;
use App\Http\Resources\RefereeEvaluationListResource;
use Modules\Director\Transformers\Referee\CheckedInRefereeResource;

class RefereeEvaluationController extends Controller
{
    use ApiResponse;

    /**
     * Create or update referee evaluation
     */
    public function storeOrUpdate(RefereeEvaluationRequest $request)
    {
        $user = auth('api')->user();
        $validated = $request->validated();

        // Check if user is evaluator or director
        if (!$user->hasAnyRole(['evaluator', 'director'])) {
            return $this->error([], 'Only evaluators and directors can create evaluations.', 403);
        }

        // Check if camp exists
        $camp = Camp::find($validated['camp_id']);
        if (!$camp) {
            return $this->error([], 'Camp not found.', 404);
        }

        // Check if evaluator is registered and approved for this camp
        if (!RefereeEvaluation::canEvaluateInCamp($user, $camp->id)) {
            if ($user->hasRole('director')) {
                return $this->error([], 'You can only evaluate referees in your own camps.', 403);
            } else {
                return $this->error([], 'You must be registered and approved for this camp to evaluate referees.', 403);
            }
        }

        // Check if referee exists and has role
        $referee = User::find($validated['referee_id']);
        if (!$referee || !$referee->hasRole('referee')) {
            return $this->error([], 'Invalid referee selected.', 404);
        }

        // Prevent self-evaluation
        if ($referee->id === $user->id) {
            return $this->error([], 'You cannot evaluate yourself.', 403);
        }

        // Verify referee has checked in to this camp
        $checkin = CampRefereeCheckin::where('camp_id', $camp->id)
            ->where('referee_id', $referee->id)
            ->first();

        if (!$checkin) {
            return $this->error([], 'Referee has not registered for this camp.', 400);
        }

        DB::beginTransaction();
        try {
            // If evaluation_id provided, update existing one
            if (isset($validated['evaluation_id'])) {
                $evaluation = RefereeEvaluation::find($validated['evaluation_id']);

                if (!$evaluation) {
                    return $this->error([], 'Evaluation not found.', 404);
                }

                // Check ownership
                if ($evaluation->evaluator_id !== $user->id) {
                    return $this->error([], 'You can only update your own evaluations.', 403);
                }

                $evaluation->update([
                    'call_accuracy' => $validated['call_accuracy'] ?? null,
                    'communication_skills' => $validated['communication_skills'] ?? null,
                    'consistency_of_calls' => $validated['consistency_of_calls'] ?? null,
                    'court_position_mechanics' => $validated['court_position_mechanics'] ?? null,
                    'fitness_mobility' => $validated['fitness_mobility'] ?? null,
                    'game_awareness' => $validated['game_awareness'] ?? null,
                    'private_comments' => $validated['private_comments'] ?? null,
                    'referee_feedback' => $validated['referee_feedback'] ?? null,
                    'status' => $validated['status'] ?? 'draft',
                    'submitted_at' => ($validated['status'] ?? 'draft') === 'submitted' ? now() : null,
                ]);

                // Update recommended level
                if (isset($validated['recommended_level'])) {
                    $evaluation->recommendedLevels()->delete();
                    RecommendedLevel::create([
                        'evaluation_id' => $evaluation->id,
                        'level' => $validated['recommended_level'],
                    ]);
                }

                $message = 'Evaluation updated successfully.';
                $statusCode = 200;
            } else {
                // Create new evaluation
                $evaluation = RefereeEvaluation::create([
                    'referee_id' => $validated['referee_id'],
                    'evaluator_id' => $user->id,
                    'camp_id' => $validated['camp_id'],
                    'game_slot_id' => $validated['game_slot_id'] ?? null,
                    'call_accuracy' => $validated['call_accuracy'] ?? null,
                    'communication_skills' => $validated['communication_skills'] ?? null,
                    'consistency_of_calls' => $validated['consistency_of_calls'] ?? null,
                    'court_position_mechanics' => $validated['court_position_mechanics'] ?? null,
                    'fitness_mobility' => $validated['fitness_mobility'] ?? null,
                    'game_awareness' => $validated['game_awareness'] ?? null,
                    'private_comments' => $validated['private_comments'] ?? null,
                    'referee_feedback' => $validated['referee_feedback'] ?? null,
                    'status' => $validated['status'] ?? 'draft',
                    'submitted_at' => ($validated['status'] ?? 'draft') === 'submitted' ? now() : null,
                ]);

                // Add recommended level
                if (isset($validated['recommended_level'])) {
                    RecommendedLevel::create([
                        'evaluation_id' => $evaluation->id,
                        'level' => $validated['recommended_level'],
                    ]);
                }

                $message = 'Evaluation created successfully.';
                $statusCode = 201;
            }

            DB::commit();

            return $this->success(
                $message,
                new RefereeEvaluationResource($evaluation->load(['referee', 'evaluator', 'camp', 'gameSlot', 'recommendedLevels'])),
                $statusCode
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to save evaluation: ' . $e->getMessage(), 500);
        }
    }


    /**
     * Get evaluations by camp (for directors/evaluators) - Excel format
     */
    public function getEvaluationsByCamp(Request $request, $campId)
    {
        $user = auth('api')->user();

        if (!$user->hasAnyRole(['director', 'evaluator', 'referee'])) {
            return $this->error('Unauthorized access.', null, 403);
        }

        $camp = Camp::find($campId);
        if (!$camp) {
            return $this->error([], 'Camp not found.', 404);
        }

        if ($user->hasRole('director') && $camp->director_id !== $user->id) {
            return $this->error([], 'You can only view evaluations from your own camps.', 403);
        }

        if ($user->hasRole('evaluator') && !$user->hasRole('director')) {
            $registration = CampEvaluatorRegistration::where('camp_id', $campId)
                ->where('evaluator_id', $user->id)
                ->where('status', 'approved')
                ->first();

            if (!$registration) {
                return $this->error([], 'You must be registered and approved for this camp.', 403);
            }

            if (!$registration->can_view_own_evaluations) {
                return $this->error([], 'You do not have permission to view evaluations for this camp. Contact the director.', 403);
            }
        }

        if ($user->hasRole('referee') && !$user->hasRole('director')) {
            $referee_registration = CampRefereeCheckin::where('camp_id', $campId)
                ->where('referee_id', $user->id)->first();

            if (!$referee_registration) {
                return $this->error([], 'You must be registered for this camp.', 403);
            }

            if ($camp->publish_ranking_for_referees == false) {
                return $this->error([], 'You do not have permission to view evaluations for this camp. Contact the director.', 403);
            }
        }

        // Get all evaluations with relationships
        $query = RefereeEvaluation::with(['referee', 'evaluator', 'gameSlot', 'recommendedLevels'])
            ->forCamp($campId);

        $evaluations = $query->orderBy('created_at', 'asc')->get();

        // Group by referee
        $groupedByReferee = $evaluations->groupBy('referee_id');

        $formattedData = [];

        foreach ($groupedByReferee as $refereeId => $refereeEvaluations) {
            $referee = $refereeEvaluations->first()->referee;

            // Calculate averages for this referee
            $avgCallAccuracy = round($refereeEvaluations->avg('call_accuracy'), 3);
            $avgCommunication = round($refereeEvaluations->avg('communication_skills'), 3);
            $avgConsistency = round($refereeEvaluations->avg('consistency_of_calls'), 3);
            $avgCourtPosition = round($refereeEvaluations->avg('court_position_mechanics'), 3);
            $avgFitness = round($refereeEvaluations->avg('fitness_mobility'), 3);
            $avgGameAwareness = round($refereeEvaluations->avg('game_awareness'), 3);

            // Calculate overall average
            $overallAvg = round(($avgCallAccuracy + $avgCommunication + $avgConsistency +
                $avgCourtPosition + $avgFitness + $avgGameAwareness) / 6, 3);

            // Get all recommended levels with count
            $recommendedLevels = [];
            foreach ($refereeEvaluations as $evaluation) {
                foreach ($evaluation->recommendedLevels as $level) {
                    if (isset($recommendedLevels[$level->level])) {
                        $recommendedLevels[$level->level]++;
                    } else {
                        $recommendedLevels[$level->level] = 1;
                    }
                }
            }

            // Format recommended levels for display
            $recommendedLevelsFormatted = [];
            foreach ($recommendedLevels as $level => $count) {
                $recommendedLevelsFormatted[] = [
                    'level' => $level,
                    'count' => $count
                ];
            }

            // Sort by count descending
            usort($recommendedLevelsFormatted, fn($a, $b) => $b['count'] <=> $a['count']);

            // Get the highest recommended level (most frequent)
            $highestRecommendedLevel = !empty($recommendedLevelsFormatted)
                ? $recommendedLevelsFormatted[0]['level']
                : null;

            // Collect all evaluator names
            $evaluators = $refereeEvaluations->map(function ($eval) {
                return $eval->evaluator->first_name . ' ' . $eval->evaluator->last_name;
            })->unique()->values()->toArray();

            // Collect all comments
            $allComments = $refereeEvaluations->filter(function ($eval) {
                return !empty($eval->referee_feedback);
            })->pluck('referee_feedback')->toArray();

            $formattedData[] = [
                'referee_id' => $referee->id,
                'referee_name' => $referee->first_name . ' ' . $referee->last_name,
                'referee_email' => $referee->email,
                'total_evaluations' => $refereeEvaluations->count(),
                'averages' => [
                    'overall' => $overallAvg,
                    'call_accuracy' => $avgCallAccuracy,
                    'communication_skills' => $avgCommunication,
                    'consistency_of_calls' => $avgConsistency,
                    'court_position_mechanics' => $avgCourtPosition,
                    'fitness_mobility' => $avgFitness,
                    'game_awareness' => $avgGameAwareness,
                ],
                'recommended_levels' => $recommendedLevelsFormatted,
                'highest_recommended_level' => $highestRecommendedLevel,
                'evaluators' => $evaluators,
                'comments' => $allComments,
            ];
        }

        // Sort by overall average descending
        usort($formattedData, fn($a, $b) => $b['averages']['overall'] <=> $a['averages']['overall']);

        return $this->success(
            'Evaluations retrieved successfully.',
            [
                'evaluations' => $formattedData,
                'total_referees' => count($formattedData),
                'total_evaluations' => $evaluations->count(),
            ]
        );
    }


    /**
     * Get evaluations created by the authenticated evaluator
     */
    public function getMyEvaluations(Request $request, $campId)
    {
        $user = auth('api')->user();

        if (!$user->hasAnyRole(['evaluator', 'director'])) {
            return $this->error([], 'Only evaluators and directors can access this.', 403);
        }

        // Check if camp exists
        $camp = Camp::find($campId);
        if (!$camp) {
            return $this->error([], 'Camp not found.', 404);
        }

        // Permission check for evaluator (not director)
        if ($user->hasRole('evaluator') && !$user->hasRole('director')) {
            $registration = CampEvaluatorRegistration::where('camp_id', $campId)
                ->where('evaluator_id', $user->id)
                ->where('status', 'approved')
                ->first();

            if (!$registration || !$registration->can_view_own_evaluations) {
                return $this->error([], 'You do not have permission to view evaluations for this camp. Contact the director.', 403);
            }
        }

        // Permission check for director
        if ($user->hasRole('director') && $camp->director_id !== $user->id) {
            return $this->error([], 'You can only view evaluations from your own camps.', 403);
        }

        // Get all evaluations by this evaluator for this camp
        $evaluations = RefereeEvaluation::with(['referee', 'camp', 'gameSlot', 'recommendedLevels'])
            ->byEvaluator($user->id)
            ->where('camp_id', $campId)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // Get overall level summary for this evaluator in this camp
        $summaryEvaluations = RefereeEvaluation::with(['recommendedLevels'])
            ->byEvaluator($user->id)
            ->where('camp_id', $campId)
            ->has('recommendedLevels')
            ->get();

        $allLevels = ['NCAA D1', 'NCAA D2', 'NAIA', 'JUCO', 'HS', 'JH/ELEM'];
        $overallSummary = [];

        foreach ($allLevels as $level) {
            $count = RecommendedLevel::whereIn(
                'evaluation_id',
                $summaryEvaluations->pluck('id')
            )->where('level', $level)->count();

            if ($count > 0) {
                $overallSummary[] = [
                    'level' => $level,
                    'count' => $count,
                ];
            }
        }

        // Sort by count descending
        usort($overallSummary, fn($a, $b) => $b['count'] <=> $a['count']);

        // Get unique referee count that this evaluator has evaluated in this camp
        $uniqueRefereesEvaluated = RefereeEvaluation::where('evaluator_id', $user->id)
            ->where('camp_id', $campId)
            ->distinct('referee_id')
            ->count('referee_id');

        return $this->success(
            'Your evaluations retrieved successfully.',
            [
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                    'location' => $camp->location,
                    'start_date' => $camp->start_date,
                    'end_date' => $camp->end_date,
                ],
                'evaluations' => RefereeEvaluationListResource::collection($evaluations),
                'overall_level_summary' => $overallSummary,
                'statistics' => [
                    'total_evaluations' => $evaluations->total(),
                    'unique_referees_evaluated' => $uniqueRefereesEvaluated,
                ],
                'pagination' => [
                    'total'        => $evaluations->total(),
                    'per_page'     => $evaluations->perPage(),
                    'current_page' => $evaluations->currentPage(),
                    'last_page'    => $evaluations->lastPage(),
                ],
            ]
        );
    }


    /**
     * Get a single evaluation by ID
     */
    public function show($id)
    {
        $user = auth('api')->user();

        $evaluation = RefereeEvaluation::with(['referee', 'evaluator', 'camp', 'gameSlot'])->find($id);

        if (!$evaluation) {
            return $this->error([], 'Evaluation not found.', 404);
        }

        // Check if user can view this evaluation
        if (!$evaluation->canBeViewedBy($user)) {
            return $this->error([], 'You do not have permission to view this evaluation.', 403);
        }

        return $this->success(
            'Evaluation retrieved successfully.',
            new RefereeEvaluationResource($evaluation)
        );
    }

    /**
     * Delete an evaluation (only by creator or director)
     */
    public function destroy($id)
    {
        $user = auth('api')->user();

        $evaluation = RefereeEvaluation::find($id);
        if (!$evaluation) {
            return $this->error([], 'Evaluation not found.', 404);
        }

        if (!$evaluation->canBeEditedBy($user)) {
            return $this->error([], 'You do not have permission to delete this evaluation.', 403);
        }

        $evaluation->delete();

        return $this->success('Evaluation deleted successfully.', [], 200);
    }


    /**
     * Get referee statistics (average scores, count, etc.)
     */
    public function getRefereeStats($refereeId)
    {
        $user = auth('api')->user();

        // Only directors, evaluators, and the referee themselves can view stats
        if (!$user->hasAnyRole(['director', 'evaluator']) && $user->id != $refereeId) {
            return $this->error('Unauthorized access.', null, 403);
        }

        $referee = User::find($refereeId);
        if (!$referee || !$referee->hasRole('referee')) {
            return $this->error('Referee not found.', null, 404);
        }

        $evaluations = RefereeEvaluation::forReferee($refereeId)->submitted()->get();

        if ($evaluations->isEmpty()) {
            return $this->success('No evaluations found for this referee.', [
                'referee' => [
                    'id' => $referee->id,
                    'name' => $referee->first_name . ' ' . $referee->last_name,
                ],
                'total_evaluations' => 0,
                'averages' => null,
            ]);
        }

        $stats = [
            'referee' => [
                'id' => $referee->id,
                'name' => $referee->first_name . ' ' . $referee->last_name,
                'email' => $referee->email,
            ],
            'total_evaluations' => $evaluations->count(),
            'averages' => [
                'call_accuracy' => round($evaluations->avg('call_accuracy'), 2),
                'communication_skills' => round($evaluations->avg('communication_skills'), 2),
                'consistency_of_calls' => round($evaluations->avg('consistency_of_calls'), 2),
                'court_position_mechanics' => round($evaluations->avg('court_position_mechanics'), 2),
                'fitness_mobility' => round($evaluations->avg('fitness_mobility'), 2),
                'game_awareness' => round($evaluations->avg('game_awareness'), 2),
                'total_score' => round($evaluations->avg('total_score'), 2),
            ],
            'overall_percentage' => round(($evaluations->avg('total_score') / 60) * 100, 2),
        ];

        return $this->success('Referee statistics retrieved successfully.', $stats);
    }


    /**
     * Get all checked-in referees for a camp that the evaluator can evaluate
     */
    public function getAllRegisteredInReferees($campId)
    {
        $user = auth('api')->user();

        // Check if camp exists
        $camp = Camp::find($campId);
        if (!$camp) {
            return $this->error('Camp not found.', null, 404);
        }

        // **NEW: Check if user can evaluate in this camp**
        if (!RefereeEvaluation::canEvaluateInCamp($user, $campId)) {
            if ($user->hasRole('director')) {
                return $this->error([], 'You can only view referees from your own camps.', 403);
            } else {
                return $this->error([], 'You must be registered and approved for this camp.', 403);
            }
        }

        $perPage = request()->get('per_page', 15);

        $checkedInReferees = CampRefereeCheckin::where('camp_id', $campId)
            ->where('registration_status', 'registered')
            ->with('referee')
            ->paginate($perPage);

        return $this->success('Registered referees fetched successfully.', [
            'total' => $checkedInReferees->total(),
            'referees' => CheckedInRefereeResource::collection($checkedInReferees),
            'pagination' => [
                'total'         => $checkedInReferees->total(),
                'per_page'      => $checkedInReferees->perPage(),
                'current_page'  => $checkedInReferees->currentPage(),
                'last_page'     => $checkedInReferees->lastPage(),
            ],
        ], 200);
    }

    /**
     * Get all referees for a camp that the evaluator can evaluate
     */
    public function getAllReferees($campId)
    {
        $user = auth('api')->user();

        // Check if camp exists
        $camp = Camp::find($campId);
        if (!$camp) {
            return $this->error('Camp not found.', null, 404);
        }

        // **NEW: Check if user can evaluate in this camp**
        if (!RefereeEvaluation::canEvaluateInCamp($user, $campId)) {
            if ($user->hasRole('director')) {
                return $this->error([], 'You can only view referees from your own camps.', 403);
            } else {
                return $this->error([], 'You must be registered and approved for this camp.', 403);
            }
        }

        $checkedInReferees = CampRefereeCheckin::where('camp_id', $campId)
            ->with('referee')
            ->get();

        return $this->success('Registered referees fetched successfully.', [
            'total' => $checkedInReferees->count(),
            'referees' => CheckedInRefereeResource::collection($checkedInReferees),
        ], 200);
    }

    /**
     * Get evaluation history for a specific referee in a camp
     * Shows all individual evaluations with details
     */
    public function getRefereeEvaluationHistory(Request $request, $campId, $refereeId)
    {
        $user = auth('api')->user();

        if (!$user->hasAnyRole(['director', 'evaluator'])) {
            return $this->error('Unauthorized access.', null, 403);
        }

        // Check if camp exists
        $camp = Camp::find($campId);
        if (!$camp) {
            return $this->error([], 'Camp not found.', 404);
        }

        // Permission check for director
        if ($user->hasRole('director') && $camp->director_id !== $user->id) {
            return $this->error([], 'You can only view evaluations from your own camps.', 403);
        }

        // Permission check for evaluator
        if ($user->hasRole('evaluator') && !$user->hasRole('director')) {
            $registration = CampEvaluatorRegistration::where('camp_id', $campId)
                ->where('evaluator_id', $user->id)
                ->where('status', 'approved')
                ->first();

            if (!$registration) {
                return $this->error([], 'You must be registered and approved for this camp.', 403);
            }

            if (!$registration->can_view_own_evaluations) {
                return $this->error([], 'You do not have permission to view evaluations for this camp. Contact the director.', 403);
            }
        }

        // Check if referee exists
        $referee = User::find($refereeId);
        if (!$referee || !$referee->hasRole('referee')) {
            return $this->error([], 'Referee not found.', 404);
        }

        // Check if referee is registered for this camp
        $checkin = CampRefereeCheckin::where('camp_id', $campId)
            ->where('referee_id', $refereeId)
            ->first();

        if (!$checkin) {
            return $this->error([], 'Referee is not registered for this camp.', 404);
        }

        // Get all evaluations for this referee in this camp
        $query = RefereeEvaluation::with(['evaluator', 'gameSlot', 'recommendedLevels'])
            ->where('camp_id', $campId)
            ->where('referee_id', $refereeId)
            ->orderBy('submitted_at', 'desc');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // If evaluator (not director), show only their own evaluations
        if ($user->hasRole('evaluator') && !$user->hasRole('director')) {
            $query->where('evaluator_id', $user->id);
        }

        $perPage = $request->get('per_page', 15);
        $evaluations = $query->paginate($perPage);

        // Get all evaluations for summary (without pagination)
        $allEvaluations = RefereeEvaluation::where('camp_id', $campId)
            ->where('referee_id', $refereeId)
            ->when($user->hasRole('evaluator') && !$user->hasRole('director'), function ($q) use ($user) {
                $q->where('evaluator_id', $user->id);
            })
            ->get();

        // Calculate overall statistics
        $statistics = null;
        if ($allEvaluations->isNotEmpty()) {
            $avgCallAccuracy = round($allEvaluations->avg('call_accuracy'), 3);
            $avgCommunication = round($allEvaluations->avg('communication_skills'), 3);
            $avgConsistency = round($allEvaluations->avg('consistency_of_calls'), 3);
            $avgCourtPosition = round($allEvaluations->avg('court_position_mechanics'), 3);
            $avgFitness = round($allEvaluations->avg('fitness_mobility'), 3);
            $avgGameAwareness = round($allEvaluations->avg('game_awareness'), 3);

            $overallAvg = round(($avgCallAccuracy + $avgCommunication + $avgConsistency +
                $avgCourtPosition + $avgFitness + $avgGameAwareness) / 6, 3);

            // Get recommended levels count
            $recommendedLevels = [];
            foreach ($allEvaluations as $evaluation) {
                if ($evaluation->relationLoaded('recommendedLevels')) {
                    foreach ($evaluation->recommendedLevels as $level) {
                        if (isset($recommendedLevels[$level->level])) {
                            $recommendedLevels[$level->level]++;
                        } else {
                            $recommendedLevels[$level->level] = 1;
                        }
                    }
                }
            }

            $recommendedLevelsFormatted = [];
            foreach ($recommendedLevels as $level => $count) {
                $recommendedLevelsFormatted[] = [
                    'level' => $level,
                    'count' => $count
                ];
            }

            usort($recommendedLevelsFormatted, fn($a, $b) => $b['count'] <=> $a['count']);

            $statistics = [
                'total_evaluations' => $allEvaluations->count(),
                'averages' => [
                    'overall' => $overallAvg,
                    'call_accuracy' => $avgCallAccuracy,
                    'communication_skills' => $avgCommunication,
                    'consistency_of_calls' => $avgConsistency,
                    'court_position_mechanics' => $avgCourtPosition,
                    'fitness_mobility' => $avgFitness,
                    'game_awareness' => $avgGameAwareness,
                ],
                'recommended_levels' => $recommendedLevelsFormatted,
                'highest_recommended_level' => !empty($recommendedLevelsFormatted)
                    ? $recommendedLevelsFormatted[0]['level']
                    : null,
            ];
        }

        // Format individual evaluations
        $formattedEvaluations = $evaluations->map(function ($evaluation) {
            return [
                'id' => $evaluation->id,
                'evaluator' => [
                    'id' => $evaluation->evaluator_id,
                    'name' => $evaluation->evaluator->first_name . ' ' . $evaluation->evaluator->last_name,
                    'email' => $evaluation->evaluator->email,
                    'role' => $evaluation->evaluator->getRoleNames()->first(),
                ],
                'game_slot' => $evaluation->game_slot_id ? [
                    'id' => $evaluation->gameSlot->id,
                    'date' => $evaluation->gameSlot->game_date,
                    'time' => $evaluation->gameSlot->start_time . ' - ' . $evaluation->gameSlot->end_time,
                    'court' => $evaluation->gameSlot->court_name ?? 'N/A',
                ] : null,
                'scores' => [
                    'call_accuracy' => $evaluation->call_accuracy,
                    'communication_skills' => $evaluation->communication_skills,
                    'consistency_of_calls' => $evaluation->consistency_of_calls,
                    'court_position_mechanics' => $evaluation->court_position_mechanics,
                    'fitness_mobility' => $evaluation->fitness_mobility,
                    'game_awareness' => $evaluation->game_awareness,
                ],
                'total_score' => (float) $evaluation->total_score,
                'average_score' => (float) $evaluation->average_score,
                'max_score' => 60,
                'percentage' => $evaluation->total_score ? round(($evaluation->total_score / 60) * 100, 2) : 0,
                'recommended_level' => $evaluation->relationLoaded('recommendedLevels') && $evaluation->recommendedLevels->isNotEmpty()
                    ? $evaluation->recommendedLevels->first()->level
                    : null,
                'private_comments' => $evaluation->private_comments,
                'referee_feedback' => $evaluation->referee_feedback,
                'status' => $evaluation->status,
                'submitted_at' => $evaluation->submitted_at ? $evaluation->submitted_at->format('Y-m-d H:i:s') : null,
                'created_at' => $evaluation->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return $this->success(
            'Referee evaluation history retrieved successfully.',
            [
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                    'location' => $camp->location,
                    'start_date' => $camp->start_date,
                    'end_date' => $camp->end_date,
                ],
                'referee' => [
                    'id' => $referee->id,
                    'name' => $referee->first_name . ' ' . $referee->last_name,
                    'email' => $referee->email,
                    'avatar' => $referee->avatar ? asset($referee->avatar) : asset('default/profile.jpg'),
                ],
                'statistics' => $statistics,
                'evaluations' => $formattedEvaluations,
                'pagination' => [
                    'total' => $evaluations->total(),
                    'per_page' => $evaluations->perPage(),
                    'current_page' => $evaluations->currentPage(),
                    'last_page' => $evaluations->lastPage(),
                ],
            ]
        );
    }
}
