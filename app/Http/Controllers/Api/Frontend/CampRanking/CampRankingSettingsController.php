<?php

namespace App\Http\Controllers\Api\Frontend\CampRanking;

use Exception;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Modules\Director\Models\Camp;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\CampEvaluatorRegistration;
use Illuminate\Support\Facades\Validator;

class CampRankingSettingsController extends Controller
{
    use ApiResponse;

    /**
     * Get ranking settings for a camp
     * Only director can access
     */
    public function getRankingSettings($campId)
    {
        $user = auth('api')->user();

        // Check if user is director
        if (!$user->hasRole('director')) {
            return $this->error([], 'Only directors can access ranking settings.', 403);
        }

        // Check if camp exists and belongs to this director
        $camp = Camp::find($campId);
        if (!$camp) {
            return $this->error([], 'Camp not found.', 404);
        }

        if ($camp->director_id !== $user->id) {
            return $this->error([], 'You can only manage settings for your own camps.', 403);
        }

        // Get current settings
        $settings = [
            'camp_id' => $camp->id,
            'camp_name' => $camp->camp_name,
            'publish_for_evaluators' => $camp->publish_ranking_for_evaluators ?? true,
            'hide_evaluator_name' => $camp->hide_evaluator_name_from_referees ?? false,
            'hide_ranking_numbers' => $camp->hide_ranking_numbers_from_referees ?? false,
            'publish_for_referees' => $camp->publish_ranking_for_referees ?? false,
        ];

        return $this->success('Ranking settings retrieved successfully.', $settings);
    }

    /**
     * Update ranking settings for a camp
     * Only director can update
     */
    public function updateRankingSettings(Request $request, $campId)
    {
        $user = auth('api')->user();

        // Check if user is director
        if (!$user->hasRole('director')) {
            return $this->error([], 'Only directors can update ranking settings.', 403);
        }

        // Check if camp exists and belongs to this director
        $camp = Camp::find($campId);
        if (!$camp) {
            return $this->error([], 'Camp not found.', 404);
        }

        if ($camp->director_id !== $user->id) {
            return $this->error([], 'You can only manage settings for your own camps.', 403);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'publish_for_evaluators' => 'sometimes|boolean',
            'hide_evaluator_name' => 'sometimes|boolean',
            'hide_ranking_numbers' => 'sometimes|boolean',
            'publish_for_referees' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed.', 422);
        }

        DB::beginTransaction();
        try {
            $validated = $validator->validated();

            // Update camp settings
            if (isset($validated['hide_evaluator_name'])) {
                $camp->hide_evaluator_name_from_referees = $validated['hide_evaluator_name'];
            }

            if (isset($validated['hide_ranking_numbers'])) {
                $camp->hide_ranking_numbers_from_referees = $validated['hide_ranking_numbers'];
            }

            if (isset($validated['publish_for_referees'])) {
                $camp->publish_ranking_for_referees = $validated['publish_for_referees'];
            }

            if (isset($validated['publish_for_evaluators'])) {
                $camp->publish_ranking_for_evaluators = $validated['publish_for_evaluators'];

                // Update all evaluator registrations for this camp
                CampEvaluatorRegistration::where('camp_id', $campId)
                    ->where('status', 'approved')
                    ->update([
                        'can_view_own_evaluations' => $validated['publish_for_evaluators']
                    ]);
            }

            $camp->save();

            DB::commit();

            $settings = [
                'camp_id' => $camp->id,
                'camp_name' => $camp->camp_name,
                'publish_for_evaluators' => $camp->publish_ranking_for_evaluators ?? true,
                'hide_evaluator_name' => $camp->hide_evaluator_name_from_referees ?? false,
                'hide_ranking_numbers' => $camp->hide_ranking_numbers_from_referees ?? false,
                'publish_for_referees' => $camp->publish_ranking_for_referees ?? false,
            ];

            return $this->success('Ranking settings updated successfully.', $settings);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to update settings: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Toggle individual evaluator's permission to view evaluations
     * Only director can update
     */
    public function toggleEvaluatorPermission(Request $request, $campId, $evaluatorId)
    {
        $user = auth('api')->user();

        // Check if user is director
        if (!$user->hasRole('director')) {
            return $this->error([], 'Only directors can manage evaluator permissions.', 403);
        }

        // Check if camp exists and belongs to this director
        $camp = Camp::find($campId);
        if (!$camp) {
            return $this->error([], 'Camp not found.', 404);
        }

        if ($camp->director_id !== $user->id) {
            return $this->error([], 'You can only manage evaluators for your own camps.', 403);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'can_view_evaluations' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed.', 422);
        }

        // Find evaluator registration
        $registration = CampEvaluatorRegistration::where('camp_id', $campId)
            ->where('evaluator_id', $evaluatorId)
            ->where('status', 'approved')
            ->first();

        if (!$registration) {
            return $this->error([], 'Evaluator registration not found or not approved.', 404);
        }

        try {
            $registration->can_view_own_evaluations = $request->can_view_evaluations;
            $registration->save();

            return $this->success('Evaluator permission updated successfully.', [
                'evaluator_id' => $evaluatorId,
                'camp_id' => $campId,
                'can_view_evaluations' => $registration->can_view_own_evaluations,
            ]);
        } catch (Exception $e) {
            return $this->error([], 'Failed to update permission: ' . $e->getMessage(), 500);
        }
    }
}
