<?php

namespace Modules\Director\Http\Controllers\Api\Schedule;

use App\Models\User;
use App\Models\CampPayment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Director\Models\Camp;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Services\StripePaymentService;
use App\Mail\CampCheckinNotificationMail;
use Illuminate\Support\Facades\Validator;
use Modules\Director\Models\CampRefereeCheckin;

class RefereeCheckinController extends Controller
{
    use ApiResponse;

    protected $stripeService;

    public function __construct(StripePaymentService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Register for camp (Payment = Registration)
     */
    public function registerForCamp(Request $request, $campId)
    {
        $referee = auth('api')->user();

        // Verify camp exists and is active
        $camp = Camp::where('id', $campId)
            ->where('status', 'active')
            ->first();

        if (!$camp) {
            return $this->error('Camp not found or inactive.', null, 404);
        }

        // Check if already registered
        $existingRegistration = CampRefereeCheckin::where('camp_id', $campId)
            ->where('referee_id', $referee->id)
            ->first();

        if ($existingRegistration) {
            return $this->error(
                [
                    'registration_status' => $existingRegistration->registration_status,
                    'registered_at' => $existingRegistration->registered_at,
                    'checked_in_at' => $existingRegistration->checked_in_at,
                    'can_check_in' => $existingRegistration->canCheckIn($camp),
                ],
                'Already registered for this camp.',
                400
            );
        }

        // Check if payment already completed (edge case: payment done but registration missing)
        $hasValidPayment = $this->stripeService->hasValidPayment($campId, $referee->id);

        if ($hasValidPayment) {
            // Payment exists but no registration record - create it
            $payment = CampPayment::where('camp_id', $campId)
                ->where('referee_id', $referee->id)
                ->where('status', 'succeeded')
                ->first();

            $registration = CampRefereeCheckin::create([
                'camp_id' => $campId,
                'referee_id' => $referee->id,
                'registration_status' => 'registered',
                'registered_at' => $payment->paid_at,
                'checked_in_at' => null,
            ]);

            return $this->success(
                'Registration completed successfully.',
                [
                    'registration' => [
                        'id' => $registration->id,
                        'status' => $registration->registration_status,
                        'registered_at' => $registration->registered_at,
                        'can_check_in' => $registration->canCheckIn($camp),
                    ],
                    'camp' => [
                        'id' => $camp->id,
                        'name' => $camp->camp_name,
                        'location' => $camp->location,
                        'start_date' => $camp->start_date->toDateString(),
                        'end_date' => $camp->end_date->toDateString(),
                    ],
                    'payment' => [
                        'amount' => $payment->amount,
                        'paid_at' => $payment->paid_at->format('Y-m-d H:i:s'),
                    ]
                ],
                201
            );
        }

        // Payment not done yet - initiate payment
        $paymentResult = $this->stripeService->createCheckoutSession($camp, $referee);

        if (!$paymentResult['success']) {
            return $this->error(
                [
                    'message' => $paymentResult['message'] ?? $paymentResult['error'],
                    'details' => $paymentResult['data'] ?? null
                ],
                $paymentResult['error'],
                400
            );
        }

        // Return payment URL
        return $this->success(
            'Redirecting to payment...',
            [
                'payment_required' => true,
                'checkout_url' => $paymentResult['checkout_url'],
                'session_id' => $paymentResult['session_id'],
                'expires_at' => $paymentResult['expires_at'],
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                    'price' => $camp->price,
                    'location' => $camp->location,
                    'start_date' => $camp->start_date->toDateString(),
                    'end_date' => $camp->end_date->toDateString(),
                ]
            ],
            200
        );
    }

    /**
     * Handle payment success callback
     * Automatically creates registration when payment succeeds
     */
    public function handlePaymentSuccess(Request $request)
    {
        $referee = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'camp_id' => 'required|exists:camps,id'
        ]);

        if ($validator->fails()) {
            return $this->error('Invalid request.', $validator->errors(), 422);
        }

        $campId = $request->camp_id;

        // Check if already registered
        $existingRegistration = CampRefereeCheckin::where('camp_id', $campId)
            ->where('referee_id', $referee->id)
            ->first();

        if ($existingRegistration) {
            return $this->success(
                'Already registered for this camp.',
                [
                    'registration' => [
                        'id' => $existingRegistration->id,
                        'status' => $existingRegistration->registration_status,
                        'registered_at' => $existingRegistration->registered_at->format('Y-m-d H:i:s'),
                        'checked_in_at' => $existingRegistration->checked_in_at?->format('Y-m-d H:i:s'),
                    ]
                ],
                200
            );
        }

        // Process payment
        $paymentResult = $this->stripeService->handleSuccess($request->session_id);

        if (!$paymentResult['success']) {
            return $this->error(
                'Payment verification failed.',
                ['message' => $paymentResult['error']],
                400
            );
        }

        // Payment success = Auto registration
        $registration = CampRefereeCheckin::create([
            'camp_id' => $campId,
            'referee_id' => $referee->id,
            'registration_status' => 'registered',
            'registered_at' => $paymentResult['payment']->paid_at, // Use payment time
            'checked_in_at' => null,
        ]);

        $camp = Camp::find($campId);

        return $this->success(
            'Payment successful! You are now registered. Check-in when the camp starts.',
            [
                'registration' => [
                    'id' => $registration->id,
                    'status' => $registration->registration_status,
                    'registered_at' => $registration->registered_at->format('Y-m-d H:i:s'),
                    'can_check_in' => $registration->canCheckIn($camp),
                ],
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                    'location' => $camp->location,
                    'start_date' => $camp->start_date->toDateString(),
                    'end_date' => $camp->end_date->toDateString(),
                ],
                'payment' => [
                    'amount' => $paymentResult['payment']->amount,
                    'paid_at' => $paymentResult['payment']->paid_at->format('Y-m-d H:i:s'),
                ],
                'next_step' => "You can check-in starting from {$camp->start_date->toDateString()}.",
            ],
            201
        );
    }

    /**
     * STEP 2: Check-in to camp (Physical attendance)
     * Only works during camp dates (start_date to end_date)
     */
    public function checkIn($campId)
    {
        $referee = auth('api')->user();

        // Verify camp exists
        $camp = Camp::where('id', $campId)
            ->where('status', 'active')
            ->first();

        if (!$camp) {
            return $this->error('Camp not found or inactive.', null, 404);
        }

        // Check registration status
        $registration = CampRefereeCheckin::where('camp_id', $campId)
            ->where('referee_id', $referee->id)
            ->first();

        if (!$registration) {
            return $this->error(
                [
                    'requires_registration' => true,
                    'camp' => [
                        'id' => $camp->id,
                        'name' => $camp->camp_name,
                        'price' => $camp->price,
                    ]
                ],
                'You are not registered for this camp. Please register first.',
                403
            );
        }

        // Check if already checked in
        if ($registration->isCheckedIn()) {
            return $this->error(
                'You have already checked in to this camp.',
                [
                    'checked_in_at' => $registration->checked_in_at->format('Y-m-d H:i:s'),
                ],
                400
            );
        }

        // Check if camp has started
        // $today = now()->toDateString();

        // if ($camp->start_date > $today) {
        //     $daysRemaining = now()->diffInDays($camp->start_date, false);
        //     return $this->error(
        //         [
        //             'camp_starts_on' => $camp->start_date->toDateString(),
        //             'days_remaining' => ceil($daysRemaining),
        //         ],
        //         "Camp has not started yet. Check-in will be available from {$camp->start_date->toDateString()}.",
        //         400
        //     );
        // }

        // // Check if camp has ended
        // if ($camp->end_date < $today) {
        //     return $this->error(
        //         [
        //             'camp_ended_on' => $camp->end_date->toDateString(),
        //         ],
        //         'This camp has already ended. Check-in is no longer available.',
        //         400
        //     );
        // }

        $now = now();
        $checkinStartTime = $camp->start_date->subHours(24);

        // Too early
        if ($now->lt($checkinStartTime)) {
            return $this->error(
                [
                    'checkin_available_from' => $checkinStartTime->format('Y-m-d H:i:s'),
                    'camp_starts_on' => $camp->start_date->format('Y-m-d H:i:s'),
                ],
                'Check-in will be available 24 hours before the camp starts.',
                400
            );
        }

        // Camp already ended
        if ($now->gt($camp->end_date)) {
            return $this->error(
                [
                    'camp_ended_on' => $camp->end_date->format('Y-m-d H:i:s'),
                ],
                'This camp has already ended. Check-in is no longer available.',
                400
            );
        }


        // All checks passed - Update to checked-in
        $registration->update([
            'registration_status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        // Send notification email to camp director(s)
        $this->sendCheckinNotificationToDirectors($camp, $referee, $registration);

        return $this->success(
            'Successfully checked in! Welcome to the camp.',
            [
                'registration' => [
                    'id' => $registration->id,
                    'status' => $registration->registration_status,
                    'registered_at' => $registration->registered_at->format('Y-m-d H:i:s'),
                    'checked_in_at' => $registration->checked_in_at->format('Y-m-d H:i:s'),
                ],
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                    'location' => $camp->location,
                    'start_date' => $camp->start_date->toDateString(),
                    'end_date' => $camp->end_date->toDateString(),
                ],
            ],
            200
        );
    }

    /**
     * Get my registrations (all camps I've registered for)
     */
    public function getMyRegistrations(Request $request)
    {
        $referee = auth('api')->user();
        $today = now()->toDateString();

        // Get per_page from request, default 10
        $perPage = $request->get('per_page', 10);

        $total = CampRefereeCheckin::where('referee_id', $referee->id)->count();

        $registrations = CampRefereeCheckin::where('referee_id', $referee->id)
            ->where('registration_status', 'registered')
            ->whereHas('camp', function ($query) use ($today) {
                $query->where('end_date', '>=', $today);
            })
            ->with([
                'camp:id,camp_name,location,start_date,end_date,camp_logo,price,sports_type_name',
                'camp.sportsType:id,sports_name,icon',
            ])
            ->with('camp.director')
            ->latest('registered_at')
            ->paginate($perPage);

        // Get payment info for current page camps only
        $campIds = $registrations->pluck('camp_id')->toArray();
        $payments = CampPayment::where('referee_id', $referee->id)
            ->whereIn('camp_id', $campIds)
            ->get()
            ->keyBy('camp_id');

        $formatted = $registrations->getCollection()->map(function ($registration) use ($payments, $today) {
            $camp = $registration->camp;
            $payment = $payments->get($camp->id);

            // Determine camp status
            if ($camp->end_date < $today) {
                $campStatus = 'completed';
            } elseif ($camp->start_date > $today) {
                $campStatus = 'upcoming';
            } else {
                $campStatus = 'ongoing';
            }

            return [
                'registration_id' => $registration->id,
                'registration_status' => $registration->registration_status,
                'can_check_in' => $registration->canCheckIn($camp),
                'registered_at' => $registration->registered_at->format('Y-m-d H:i:s'),
                'checked_in_at' => $registration->checked_in_at?->format('Y-m-d H:i:s'),
                'camp' => [
                    'id' => $camp->id,
                    'camp_name' => $camp->camp_name,
                    'location' => $camp->location,
                    'logo' => $camp->camp_logo ? asset($camp->camp_logo) : asset('default/no_image.webp'),
                    'start_date' => $camp->start_date->toDateString(),
                    'end_date' => $camp->end_date->toDateString(),
                    'price' => $camp->price,
                    'status' => $campStatus,
                    'sports_type' => $camp->sportsType ? [
                        'id' => $camp->sportsType->id,
                        'name' => $camp->sportsType->sports_name,
                        'icon' => $camp->sportsType->icon ? asset($camp->sportsType->icon) : asset('default/no_image.webp'),
                    ] : null,
                    'director' => [
                        'id' => $camp->director->id,
                        'name' => $camp->director->first_name . ' ' . $camp->director->last_name ?? null,
                    ],
                ],
                'payment' => $payment ? [
                    'amount' => $payment->amount,
                    'paid_at' => $payment->paid_at->format('Y-m-d H:i:s'),
                ] : null,
            ];
        });

        // Get total counts for summary (from all records, not just current page)
        $totalRegistered = CampRefereeCheckin::where('referee_id', $referee->id)
            ->where('registration_status', 'registered')
            ->whereHas('camp', function ($query) use ($today) {
                $query->where('end_date', '>=', $today);
            })
            ->count();

        $totalCheckedIn = CampRefereeCheckin::where('referee_id', $referee->id)
            ->where('registration_status', 'checked_in')
            ->whereHas('camp', function ($query) use ($today) {
                $query->where('end_date', '>=', $today);
            })
            ->count();

        return $this->success(
            'My registered camp fetched successfully.',
            [
                'registrations' => $formatted,
                'pagination' => [
                    'total' => $registrations->total(),
                    'per_page' => $registrations->perPage(),
                    'current_page' => $registrations->currentPage(),
                    'last_page' => $registrations->lastPage(),
                ],
                'summary' => [
                    'total' => $total,
                    'registered_only' => $totalRegistered,
                    'checked_in' => $totalCheckedIn,
                ]
            ],
            200
        );
    }

    /**
     * Get my checked-in camps (Referee)
     */
    public function getMyCheckins(Request $request)
    {
        $referee = auth('api')->user();
        $today = now()->toDateString();

        // Get per_page from request, default 10
        $perPage = $request->get('per_page', 10);
        $total = CampRefereeCheckin::where('referee_id', $referee->id)->count();

        $checkins = CampRefereeCheckin::where('referee_id', $referee->id)
            ->where('registration_status', 'checked_in')
            ->whereHas('camp', function ($query) use ($today) {
                $query->where('end_date', '>=', $today);
            })
            ->whereNotNull('checked_in_at')
            ->with([
                'camp:id,camp_name,location,start_date,end_date,camp_logo,price,sports_type_name',
                'camp.sportsType:id,sports_name,icon',
            ])
            ->with('camp.director')
            ->latest('checked_in_at')
            ->paginate($perPage);

        // Get payment info
        $campIds = $checkins->pluck('camp_id')->toArray();
        $payments = CampPayment::where('referee_id', $referee->id)
            ->whereIn('camp_id', $campIds)
            ->get()
            ->keyBy('camp_id');

        $formatted = $checkins->map(function ($checkin) use ($payments, $today) {
            $camp = $checkin->camp;
            $payment = $payments->get($camp->id);

            // Determine camp status
            if ($camp->end_date < $today) {
                $campStatus = 'completed';
            } elseif ($camp->start_date > $today) {
                $campStatus = 'upcoming';
            } else {
                $campStatus = 'ongoing';
            }

            return [
                'registration_id' => $checkin->id,
                'registration_status' => $checkin->registration_status,
                'can_check_in' => $checkin->canCheckIn($camp),
                'registered_at' => $checkin->registered_at->format('Y-m-d H:i:s'),
                'checked_in_at' => $checkin->checked_in_at?->format('Y-m-d H:i:s'),
                'camp' => [
                    'id' => $camp->id,
                    'camp_name' => $camp->camp_name,
                    'location' => $camp->location,
                    'logo' => $camp->camp_logo ? asset($camp->camp_logo) : asset('default/no_image.webp'),
                    'start_date' => $camp->start_date->toDateString(),
                    'end_date' => $camp->end_date->toDateString(),
                    'price' => $camp->price,
                    'status' => $campStatus,
                    'sports_type' => $camp->sportsType ? [
                        'id' => $camp->sportsType->id,
                        'name' => $camp->sportsType->sports_name,
                        'icon' => $camp->sportsType->icon ? asset($camp->sportsType->icon) : asset('default/no_image.webp'),
                    ] : null,
                    'director' => [
                        'id' => $camp->director->id,
                        'name' => $camp->director->first_name . ' ' . $camp->director->last_name ?? null,
                    ],
                ],
                'payment' => $payment ? [
                    'amount' => $payment->amount,
                    'paid_at' => $payment->paid_at->format('Y-m-d H:i:s'),
                ] : null,
            ];
        });

        // Get total counts for summary (from all records, not just current page)
        $totalRegistered = CampRefereeCheckin::where('referee_id', $referee->id)
            ->where('registration_status', 'registered')
            ->whereHas('camp', function ($query) use ($today) {
                $query->where('end_date', '>=', $today);
            })
            ->count();

        $totalCheckedIn = CampRefereeCheckin::where('referee_id', $referee->id)
            ->where('registration_status', 'checked_in')
            ->whereHas('camp', function ($query) use ($today) {
                $query->where('end_date', '>=', $today);
            })
            ->count();

        return $this->success(
            'My checked-in camp fetched successfully.',
            [
                'checked_in' => $formatted,
                'pagination' => [
                    'total' => $checkins->total(),
                    'per_page' => $checkins->perPage(),
                    'current_page' => $checkins->currentPage(),
                    'last_page' => $checkins->lastPage(),
                ],
                'summary' => [
                    'total' => $total,
                    'registered_only' => $totalRegistered,
                    'checked_in' => $totalCheckedIn,
                ]
            ],
            200
        );
    }

    /**
     * Get active camps (upcoming + ongoing)
     */
    public function getActiveCamps(Request $request)
    {
        $referee = auth('api')->user();
        $today = now()->toDateString();

        // Get per_page from request, default 10
        $perPage = $request->get('per_page', 10);

        $registrations = CampRefereeCheckin::where('referee_id', $referee->id)
            ->with([
                'camp:id,camp_name,location,start_date,end_date,camp_logo,price,sports_type_name',
                'camp.sportsType:id,sports_name,icon',
            ])
            ->whereHas('camp', function ($query) use ($today) {
                $query->where('end_date', '>=', $today);
            })
            ->latest('registered_at')
            ->paginate($perPage);

        $campIds = $registrations->pluck('camp_id')->toArray();
        $payments = CampPayment::where('referee_id', $referee->id)
            ->whereIn('camp_id', $campIds)
            ->get()
            ->keyBy('camp_id');

        $registrations->getCollection()->map(function ($registration) use ($payments, $today) {
            $camp = $registration->camp;
            $payment = $payments->get($camp->id);

            $campStatus = $camp->start_date > $today ? 'upcoming' : 'ongoing';

            return [
                'registration_id' => $registration->id,
                'registration_status' => $registration->registration_status,
                'can_check_in' => $registration->canCheckIn($camp),
                'registered_at' => $registration->registered_at?->format('Y-m-d H:i:s'),
                'checked_in_at' => $registration->checked_in_at?->format('Y-m-d H:i:s'),
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                    'location' => $camp->location,
                    'logo' => $camp->camp_logo
                        ? asset($camp->camp_logo)
                        : asset('default/no_image.webp'),
                    'start_date' => $camp->start_date->toDateString(),
                    'end_date' => $camp->end_date->toDateString(),
                    'status' => $campStatus,
                    'sports_type' => $camp->sportsType ? [
                        'name' => $camp->sportsType->sports_name,
                        'icon' => $camp->sportsType->icon
                            ? asset($camp->sportsType->icon)
                            : null,
                    ] : null,
                ],
                'payment' => $payment ? [
                    'amount' => $payment->amount,
                    'paid_at' => $payment->paid_at->format('Y-m-d H:i:s'),
                ] : null,
            ];
        });

        return $this->success(
            'Active camps fetched successfully.',
            [
                'active_camps' => $registrations->items(),
                'pagination' => [
                    'total' => $registrations->total(),
                    'per_page' => $registrations->perPage(),
                    'current_page' => $registrations->currentPage(),
                    'last_page' => $registrations->lastPage(),
                ],
            ],
            200
        );
    }

    /**
     * Get previous/completed camps
     */
    public function getPreviousCamps(Request $request)
    {
        $referee = auth('api')->user();
        $today = now()->toDateString();

        // Get per_page from request, default 10
        $perPage = $request->get('per_page', 10);

        $registrations = CampRefereeCheckin::where('referee_id', $referee->id)
            ->with([
                'camp:id,camp_name,location,start_date,end_date,camp_logo,price',
                'camp.sportsType:id,sports_name,icon',
            ])
            ->with('camp.director')
            ->whereHas('camp', function ($query) use ($today) {
                $query->where('end_date', '<', $today);
            })
            ->latest('registered_at')
            ->paginate($perPage);

        $campIds = $registrations->pluck('camp_id')->toArray();
        $payments = CampPayment::where('referee_id', $referee->id)
            ->whereIn('camp_id', $campIds)
            ->get()
            ->keyBy('camp_id');

        $formatted = $registrations->getCollection()->map(function ($registration) use ($payments) {
            $camp = $registration->camp;
            $payment = $payments->get($camp->id);

            return [
                'registration_id' => $registration->id,
                'registration_status' => $registration->registration_status,
                'registered_at' => $registration->registered_at->format('Y-m-d H:i:s'),
                'checked_in_at' => $registration->checked_in_at?->format('Y-m-d H:i:s'),
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                    'location' => $camp->location,
                    'logo' => $camp->camp_logo ? asset($camp->camp_logo) : asset('default/no_image.webp'),
                    'start_date' => $camp->start_date->toDateString(),
                    'end_date' => $camp->end_date->toDateString(),
                    'status' => 'completed',
                    'director' => [
                        'id' => $camp->director->id ?? null,
                        'name' => trim(($camp->director->first_name ?? '') . ' ' . ($camp->director->last_name ?? '')),
                    ],
                ],
                'payment' => $payment ? [
                    'amount' => $payment->amount,
                    'paid_at' => $payment->paid_at->format('Y-m-d H:i:s'),
                ] : null,
            ];
        });

        return $this->success(
            'Previous camps fetched successfully.',
            [
                'previous_camps' => $formatted->values(), // ✔️ Collection data
                'pagination' => [
                    'total' => $registrations->total(),
                    'per_page' => $registrations->perPage(),
                    'current_page' => $registrations->currentPage(),
                    'last_page' => $registrations->lastPage(),
                ],
            ],
            200
        );
    }


    /**
     * Send check-in notification to camp directors
     */
    private function sendCheckinNotificationToDirectors(Camp $camp, User $referee, CampRefereeCheckin $registration)
    {
        try {
            // Assuming camp has a relationship with directors
            // You might need to adjust this based on your actual database structure
            $directors = [];

            // Option 1: If camp has a director_id field
            if ($camp->director_id) {
                $director = User::find($camp->director_id);
                if ($director) {
                    $directors[] = $director;
                }
            }

            // Option 4: If camp has an organizer/creator
            if (!$directors && $camp->created_by) {
                $director = User::find($camp->created_by);
                if ($director) {
                    $directors[] = $director;
                }
            }

            // If no directors found, try to get admin users
            if (empty($directors)) {
                $directors = User::role('admin')->take(3)->get();
            }

            // Send email to each director
            foreach ($directors as $director) {
                try {
                    Mail::to($director->email)->queue(
                        new CampCheckinNotificationMail($director, $referee, $camp, $registration)
                    );

                    Log::info('Check-in notification sent to director', [
                        'director_id' => $director->id,
                        'director_email' => $director->email,
                        'referee_id' => $referee->id,
                        'camp_id' => $camp->id,
                        'registration_id' => $registration->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send check-in notification to director', [
                        'director_id' => $director->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in sendCheckinNotificationToDirectors', [
                'error' => $e->getMessage(),
                'camp_id' => $camp->id,
                'referee_id' => $referee->id
            ]);
        }
    }
}
