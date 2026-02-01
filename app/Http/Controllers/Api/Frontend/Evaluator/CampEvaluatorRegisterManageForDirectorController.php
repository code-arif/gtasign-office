<?php

namespace App\Http\Controllers\Api\Frontend\Evaluator;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Director\Models\Camp;
use App\Http\Controllers\Controller;
use App\Models\CampEvaluatorRegistration;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Evaluator\CampRegistraionsListResource;

class CampEvaluatorRegisterManageForDirectorController extends Controller
{
    use ApiResponse;

    /**
     * Director views evaluator registrations for their camp
     */
    public function getCampRegistrations(Request $request, $campId)
    {
        $user = auth('api')->user();

        // Only directors can view registrations
        if (!$user->hasRole('director')) {
            return $this->error([], 'Only directors can view camp registrations.', 403);
        }

        // Verify camp ownership
        $camp = Camp::where('id', $campId)
            ->where('director_id', $user->id)
            ->first();

        if (!$camp) {
            return $this->error([], 'Camp not found or unauthorized.', 404);
        }

        $query = CampEvaluatorRegistration::with(['evaluator'])
            ->where('camp_id', $campId);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $registrations = $query->orderBy('registered_at', 'desc')
            ->paginate($request->get('per_page', 12));

        $formattedRegistrations = CampRegistraionsListResource::collection($registrations);

        return $this->success(
            'Camp registrations retrieved successfully.',
            [
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                ],
                'registrations' => $formattedRegistrations,
                'pagination' => [
                    'total' => $registrations->total(),
                    'per_page' => $registrations->perPage(),
                    'current_page' => $registrations->currentPage(),
                    'last_page' => $registrations->lastPage(),
                ],
            ]
        );
    }

    /**
     * Director approves evaluator registration
     */
    public function approve(Request $request, $registrationId)
    {
        $user = auth('api')->user();

        if (!$user->hasRole('director')) {
            return $this->error([], 'Only directors can approve registrations.', 403);
        }

        $registration = CampEvaluatorRegistration::with('camp')->find($registrationId);

        if (!$registration) {
            return $this->error([], 'Registration not found.', 404);
        }

        // Verify camp ownership
        if ($registration->camp->director_id !== $user->id) {
            return $this->error([], 'Unauthorized to approve this registration.', 403);
        }

        if ($registration->status !== 'pending') {
            return $this->error([], 'Only pending registrations can be approved.', 400);
        }

        $registration->approve($user);

        return $this->success(
            'Evaluator registration approved successfully.',
            // [
            //     'registration' => $registration->fresh()->load(['evaluator', 'camp', 'approver']),
            // ]
            [],
            200
        );
    }

    /**
     * Director rejects evaluator registration
     */
    public function reject(Request $request, $registrationId)
    {
        $user = auth('api')->user();

        if (!$user->hasRole('director')) {
            return $this->error([], 'Only directors can reject registrations.', 403);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed.', 422);
        }

        $registration = CampEvaluatorRegistration::with('camp')->find($registrationId);

        if (!$registration) {
            return $this->error([], 'Registration not found.', 404);
        }

        // Verify camp ownership
        if ($registration->camp->director_id !== $user->id) {
            return $this->error([], 'Unauthorized to reject this registration.', 403);
        }

        if ($registration->status !== 'pending') {
            return $this->error([], 'Only pending registrations can be rejected.', 400);
        }

        $registration->reject($request->rejection_reason);

        return $this->success(
            'Evaluator registration rejected.',
            // [
            //     'registration' => $registration->fresh()->load(['evaluator', 'camp']),
            // ]
            [],
            200
        );
    }

    /**
     * Director toggles evaluator's permission to view their own evaluations
     */
    public function toggleEvaluatorVisibility(Request $request, $registrationId)
    {
        $user = auth('api')->user();

        if (!$user->hasRole('director')) {
            return $this->error([], 'Only directors can toggle evaluator permissions.', 403);
        }

        $registration = CampEvaluatorRegistration::with('camp')->find($registrationId);

        if (!$registration) {
            return $this->error([], 'Registration not found.', 404);
        }

        // Verify camp ownership
        if ($registration->camp->director_id !== $user->id) {
            return $this->error([], 'Unauthorized to modify this registration.', 403);
        }

        // Only approved evaluators can have their visibility toggled
        if ($registration->status !== 'approved') {
            return $this->error([], 'Only approved evaluators can have their permissions modified.', 400);
        }

        $newStatus = $registration->toggleVisibility();

        return $this->success(
            $newStatus
                ? 'Evaluator can now view their evaluations.'
                : 'Evaluator visibility disabled.',
            [
                'registration' => $registration->fresh()->load(['evaluator', 'camp']),
                'can_view_evaluations' => $newStatus,
            ]
        );
    }

    /**
     * Director removes evaluator from camp (delete registration)
     */
    public function removeEvaluator($registrationId)
    {
        $user = auth('api')->user();

        if (!$user->hasRole('director')) {
            return $this->error([], 'Only directors can remove evaluators.', 403);
        }

        $registration = CampEvaluatorRegistration::with(['camp', 'evaluator'])->find($registrationId);

        if (!$registration) {
            return $this->error([], 'Registration not found.', 404);
        }

        // Verify camp ownership
        if ($registration->camp->director_id !== $user->id) {
            return $this->error([], 'Unauthorized to remove this evaluator.', 403);
        }

        // Store evaluator name for response message
        $evaluatorName = $registration->evaluator->first_name . ' ' . $registration->evaluator->last_name;
        $campName = $registration->camp->camp_name;

        // Delete the registration
        $registration->delete();

        return $this->success(
            "Evaluator {$evaluatorName} has been removed from {$campName}.",
            [],
            200
        );
    }
}
