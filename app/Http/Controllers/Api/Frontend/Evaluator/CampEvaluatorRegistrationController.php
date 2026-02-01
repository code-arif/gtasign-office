<?php

namespace App\Http\Controllers\Api\Frontend\Evaluator;

use App\Models\User;
use App\Models\CampPayment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Director\Models\Camp;
use App\Http\Controllers\Controller;
use App\Models\CampEvaluatorRegistration;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Evaluator\CampEvaluatorRegistrationResource;

class CampEvaluatorRegistrationController extends Controller
{
    use ApiResponse;

    /**
     * Evaluator registers for a camp
     */
    public function register(Request $request)
    {
        $user = auth('api')->user();

        // Only evaluators can register
        if (!$user->hasRole('evaluator')) {
            return $this->error([], 'Only evaluators can register for camps.', 403);
        }

        $validator = Validator::make($request->all(), [
            'camp_id' => 'required|exists:camps,id',
            'registration_note' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed.', 422);
        }

        $campId = $request->camp_id;

        // Check if camp exists and is active
        $camp = Camp::where('id', $campId)->where('status', 'active')->first();
        if (!$camp) {
            return $this->error([], 'Camp not found or inactive.', 404);
        }

        // Check if already registered
        $existingRegistration = CampEvaluatorRegistration::where('camp_id', $campId)
            ->where('evaluator_id', $user->id)
            ->first();

        if ($existingRegistration) {
            if ($existingRegistration->status === 'pending') {
                return $this->error([], 'Your registration is already pending approval.', 409);
            } elseif ($existingRegistration->status === 'approved') {
                return $this->error([], 'You are already registered and approved for this camp.', 409);
            } elseif ($existingRegistration->status === 'rejected') {
                // Allow re-registration if previously rejected
                $existingRegistration->update([
                    'status' => 'pending',
                    'registration_note' => $request->registration_note,
                    'rejection_reason' => null,
                    'registered_at' => now(),
                    'rejected_at' => null,
                ]);

                $existingRegistration->load(['camp', 'evaluator']);

                return $this->success(
                    'Re-registration submitted successfully. Waiting for director approval.',
                    [
                        'registration' => new CampEvaluatorRegistrationResource($existingRegistration),
                    ]
                );
            }
        }

        // Create new registration
        $registration = CampEvaluatorRegistration::create([
            'camp_id' => $campId,
            'evaluator_id' => $user->id,
            'status' => 'pending',
            'registration_note' => $request->registration_note,
            'registered_at' => now(),
        ]);

        $registration->load(['camp', 'evaluator']);

        return $this->success(
            'Registration submitted successfully. Waiting for director approval.',
            [
                'registration' => new CampEvaluatorRegistrationResource($registration),
            ],
            201
        );
    }

    /**
     * Get evaluator's own registrations
     */
    public function myRegistrations(Request $request)
    {
        $user = auth('api')->user();
        $today = now()->toDateString();

        if (!$user->hasRole('evaluator')) {
            return $this->error([], 'Only evaluators can access this.', 403);
        }

        $query = CampEvaluatorRegistration::with(['camp.director', 'approver'])
            ->where('evaluator_id', $user->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $registrations = $query->orderBy('registered_at', 'desc')
            ->whereHas('camp', function ($query) use ($today) {
                $query->where('end_date', '>=', $today);
            })
            ->paginate($request->get('per_page', 12));

        return $this->success(
            'Registrations retrieved successfully.',
            [
                'registrations' => CampEvaluatorRegistrationResource::collection($registrations),
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
     * Cancel registration (by evaluator)
     */
    public function cancel($registrationId)
    {
        $user = auth('api')->user();

        if (!$user->hasRole('evaluator')) {
            return $this->error([], 'Only evaluators can cancel their registrations.', 403);
        }

        $registration = CampEvaluatorRegistration::where('id', $registrationId)
            ->where('evaluator_id', $user->id)
            ->first();

        if (!$registration) {
            return $this->error([], 'Registration not found.', 404);
        }

        // Only pending or rejected registrations can be cancelled
        if (!in_array($registration->status, ['pending', 'rejected'])) {
            return $this->error([], 'Only pending or rejected registrations can be cancelled.', 400);
        }

        $registration->delete();

        return $this->success('Registration cancelled successfully.', []);
    }

    /**
     * Previous camp
     */
    public function previousCamp(Request $request)
    {
        $user = auth('api')->user();
        $today = now()->toDateString();

        if (!$user->hasRole('evaluator')) {
            return $this->error([], 'Only evaluators can access this.', 403);
        }

        $query = CampEvaluatorRegistration::with(['camp.director', 'approver'])
            ->where('evaluator_id', $user->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $registrations = $query->orderBy('registered_at', 'desc')
            ->whereHas('camp', function ($query) use ($today) {
                $query->where('end_date', '<', $today);
            })
            ->paginate($request->get('per_page', 12));

        return $this->success(
            'Previous retrieved successfully.',
            [
                'registrations' => CampEvaluatorRegistrationResource::collection($registrations),
                'pagination' => [
                    'total' => $registrations->total(),
                    'per_page' => $registrations->perPage(),
                    'current_page' => $registrations->currentPage(),
                    'last_page' => $registrations->lastPage(),
                ],
            ]
        );
    }
}
