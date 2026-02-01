<?php

namespace Modules\Director\Http\Controllers\Api\Camp;

use Carbon\Carbon;
use App\Models\SportsType;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Helpers\HandlesTimezones;
use App\Services\LocationService;
use Modules\Director\Models\Camp;
use App\Http\Controllers\Controller;
use Modules\Director\Helpers\UploadFile;
use Modules\Director\Transformers\CampResource;
use Modules\Director\Http\Requests\CampCreateRequest;

class CampManageController extends Controller
{
    use ApiResponse;

    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Create camp with timezone support
     */
    public function createCamp(CampCreateRequest $request)
    {
        $user = auth('api')->user();
        $validated = $request->validated();

        // Upload camp logo
        $campLogoPath = null;
        if ($request->hasFile('camp_logo')) {
            $campLogoPath = UploadFile::uploadFiles(
                $request->file('camp_logo'),
                'uploads/camp_logos'
            );
        }

        // Sports Type Validation
        $sportsType = SportsType::find($request->sports_type_id);
        if (!$sportsType) {
            return $this->error('Invalid sports type.', null, 404);
        }

        // Extra price from env
        $extraPrice = Env('CAMP_EXTRA_PRICE');

        // Final price calculation
        $finalPrice = $request->price + $extraPrice;

        // Detect timezone from coordinates if not provided
        $timezone = $request->timezone;
        if (!$timezone && $request->latitude && $request->longitude) {
            $timezone = HandlesTimezones::detectFromCoordinates(
                $request->latitude,
                $request->longitude
            );
        }

        // Handle Location Name
        $locationName = $request->location;
        $latitude = $request->latitude;
        $longitude = $request->longitude;

        // If coordinates provided, fetch proper location name
        if ($latitude && $longitude) {
            // Validate coordinates
            if (!$this->locationService->validateCoordinates($latitude, $longitude)) {
                return $this->error('Invalid coordinates provided.', null, 400);
            }

            // Fetch location from Google
            $locationResult = $this->locationService->getLocationFromCoordinates(
                $latitude,
                $longitude,
                'detailed' // Options: 'short', 'detailed', 'address', 'full'
            );

            if ($locationResult['success']) {
                // Use Google's location if:
                // 1. No location name provided from frontend, OR
                // 2. Frontend location name is too long (> 100 chars)
                if (empty($locationName) || strlen($locationName) > 100) {
                    $locationName = $locationResult['location_name'];
                }

                // Optional: You can also store formatted_address separately
                // $formattedAddress = $locationResult['formatted_address'];
            } else {
                // Google fetch failed, use fallback
                if (empty($locationName)) {
                    $locationName = 'Location coordinates: ' . $latitude . ', ' . $longitude;
                }
            }
        } else {
            // No coordinates provided, location name is required
            if (empty($locationName)) {
                return $this->error('Location name or coordinates are required.', null, 400);
            }
        }

        // CRITICAL: Parse dates in the camp's timezone and store as date-only format
        $campTimezone = $timezone ?? config('app.timezone', 'UTC');

        $startDate = Carbon::parse($request->start_date, $campTimezone)->format('Y-m-d');
        $endDate = Carbon::parse($request->end_date, $campTimezone)->format('Y-m-d');

        // Create Camp
        $camp = Camp::create([
            'director_id'      => $user->id,
            'sports_type_id'   => $sportsType->id,
            'sports_type_name' => $sportsType->sports_name,
            'camp_name'        => $request->camp_name,
            'location'         => $locationName,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'camp_details'     => $request->camp_details,
            'price'            => $finalPrice,
            'camp_logo'        => $campLogoPath,
            'latitude'         => $request->latitude,
            'longitude'        => $request->longitude,
            'timezone'         => $campTimezone,
        ]);

        return $this->success(
            'Camp created successfully.',
            new CampResource($camp),
            201
        );
    }


    // Edit Camp
    public function updateCamp(Request $request, $id)
    {
        $user = auth('api')->user();

        // Find Camp
        $camp = Camp::where('id', $id)
            ->where('director_id', $user->id)
            ->first();

        if (!$camp) {
            return $this->error('Camp not found.', null, 404);
        }

        // Validate request fields
        $validated = $request->validate([
            'camp_name'       => 'sometimes|string|max:255',
            'location'        => 'sometimes|string|max:255',
            'start_date'      => 'sometimes|date',
            'end_date'        => 'sometimes|date|after_or_equal:start_date',
            'camp_details'    => 'sometimes|string',
            'price'           => 'sometimes|numeric',
            'sports_type_id'  => 'sometimes|exists:sports_types,id',
            'camp_logo'       => 'sometimes|image|max:2048',
            'latitude'        => 'sometimes|numeric',
            'longitude'       => 'sometimes|numeric',
            'timezone'        => 'sometimes|string|timezone',
        ]);

        // Update Sports Type
        if ($request->filled('sports_type_id')) {
            $sportsType = SportsType::find($request->sports_type_id);
            if ($sportsType) {
                $camp->sports_type_id   = $sportsType->id;
                $camp->sports_type_name = $sportsType->sports_name;
            }
        }

        // Update Logo
        if ($request->hasFile('camp_logo')) {
            if ($camp->camp_logo) {
                UploadFile::deleteImage($camp->camp_logo);
            }

            $camp->camp_logo = UploadFile::uploadFiles(
                $request->file('camp_logo'),
                'uploads/camp_logos'
            );
        }

        /**
         * Handle Coordinates, Location & Timezone
         */
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $latitude  = $request->latitude;
            $longitude = $request->longitude;

            if (!$this->locationService->validateCoordinates($latitude, $longitude)) {
                return $this->error('Invalid coordinates provided.', null, 400);
            }

            $camp->latitude  = $latitude;
            $camp->longitude = $longitude;

            // Auto update location if not provided
            if (!$request->filled('location')) {
                $locationResult = $this->locationService->getLocationFromCoordinates(
                    $latitude,
                    $longitude,
                    'detailed'
                );

                if ($locationResult['success']) {
                    $camp->location = $locationResult['location_name'];
                }
            }

            // Auto detect timezone if not provided
            if (!$request->filled('timezone')) {
                $camp->timezone = HandlesTimezones::detectFromCoordinates(
                    $latitude,
                    $longitude
                );
            }
        }

        // Explicit location update
        if ($request->filled('location')) {
            $locationName = $request->location;

            if (strlen($locationName) > 100 && $camp->latitude && $camp->longitude) {
                $locationResult = $this->locationService->getLocationFromCoordinates(
                    $camp->latitude,
                    $camp->longitude,
                    'detailed'
                );

                if ($locationResult['success']) {
                    $locationName = $locationResult['location_name'];
                }
            }

            $camp->location = $locationName;
        }

        /**
         * CRITICAL: Price handling (same logic as createCamp)
         */
        if ($request->filled('price')) {
            $extraPrice  = env('CAMP_EXTRA_PRICE', 0);
            $camp->price = $request->price + $extraPrice;
        }

        /**
         * Date handling (timezone-safe, date only)
         */
        $campTimezone = $request->timezone ?? $camp->timezone ?? config('app.timezone');

        if ($request->filled('start_date')) {
            $camp->start_date = Carbon::parse(
                $request->start_date,
                $campTimezone
            )->format('Y-m-d');
        }

        if ($request->filled('end_date')) {
            $camp->end_date = Carbon::parse(
                $request->end_date,
                $campTimezone
            )->format('Y-m-d');
        }

        /**
         * Update remaining simple fields
         */
        $fields = ['camp_name', 'camp_details', 'timezone'];
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $camp->$field = $request->$field;
            }
        }

        $camp->save();

        return $this->success(
            'Camp updated successfully.',
            new CampResource($camp),
            200
        );
    }


    /**
     * Get available timezones for dropdown
     */
    public function getAvailableTimezones()
    {
        $timezones = HandlesTimezones::getAllTimezones();

        $formatted = collect($timezones)->map(function ($name, $value) {
            return [
                'value' => $value,
                'label' => $name,
                'offset' => Carbon::now($value)->offsetHours,
            ];
        })->values();

        return $this->success(
            'Timezones fetched successfully.',
            ['timezones' => $formatted],
            200
        );
    }

    /*
     * Update camp status
     */
    public function updateStatus(Request $request, $id)
    {
        $user = auth('api')->user();

        // Validation
        $request->validate([
            'status' => 'required|in:active,inactive'
        ]);

        // Find camp
        $camp = Camp::where('id', $id)
            ->where('director_id', $user->id)
            ->first();

        if (!$camp) {
            return $this->error(null, 'Camp not found.', 404);
        }

        // Update Status
        $camp->status = $request->status;
        $camp->save();

        return $this->success(
            'Camp status updated successfully.',
            new CampResource($camp),
            200
        );
    }

    /**
     * Get camp details
     */
    public function campDetails($id)
    {
        $user = auth('api')->user();

        $camp = Camp::with(['sportsType'])
            ->when($user->hasRole('referee'), function ($query) use ($user) {
                $query->with(['checkedInReferees' => function ($q) use ($user) {
                    $q->where('referee_id', $user->id);
                }]);
            })

            ->when($user->hasRole('evaluator'), function ($query) use ($user) {
                $query->with(['evaluatorRegistrations' => function ($q) use ($user) {
                    $q->where('evaluator_id', $user->id);
                }]);
            })
            ->find($id);

        if (!$camp) {
            return $this->error(null, 'Camp not found.', 404);
        }

        return $this->success(
            'Camp details fetched successfully.',
            new CampResource($camp),
            200
        );
    }

    /**
     * No Auth camp details
     */
    public function noAuthCampDetails($id)
    {
        $camp = Camp::with(['sportsType', 'director'])->find($id);

        // return $camp;exit();

        if (!$camp) {
            return $this->error(null, 'Camp not found.', 404);
        }

        return $this->success(
            'Camp details fetched successfully.',
            new CampResource($camp),
            200
        );
    }


    /**
     * Delete camp
     */
    public function deleteCamp($id)
    {
        $user = auth('api')->user();

        // Find camp
        $camp = Camp::where('id', $id)
            ->where('director_id', $user->id)
            ->first();

        if (!$camp) {
            return $this->error(null, 'Camp not found.', 404);
        }

        // Delete camp logo if exists
        if ($camp->camp_logo) {
            UploadFile::deleteImage($camp->camp_logo);
        }

        // Delete camp
        $camp->delete();

        return $this->success(
            'Camp deleted successfully.',
            null,
            200
        );
    }

    /**
     * Director Camp List
     */
    public function directorCampList(Request $request)
    {
        $user = auth('api')->user();

        // Fetch camps created by the logged-in director
        $camps = Camp::where('director_id', $user->id)
            ->with(['sportsType', 'checkedInReferees', 'schedule'])
            ->orderBy('created_at', 'desc')
            ->paginate(8);

        if ($camps->isEmpty()) {
            return $this->error(null, 'No camps found.', 404);
        }

        $response = [
            'camp_list' => $camps->map(function ($camp) {
                return [
                    'camp_id'        => $camp->id,
                    'camp_name'      => $camp->camp_name,
                    'camp_logo'      => $camp->camp_logo ? asset($camp->camp_logo) : asset('default/no_image.webp'),
                    'location'       => $camp->location,
                    'timezone'       => $camp->timezone,
                    'timezone_name'  => $camp->timezone_display_name,
                    'sports_type'    => $camp->sportsType->sports_name ?? null,
                    'sports_type_id' => $camp->sports_type_id,
                    'status'         => $camp->status,

                    // totals
                    'total_referees' => $camp->checkedInReferees->count(),
                    'total_courts'    => $camp->schedule ? $camp->schedule->gameSlots()->count() : 0,

                    // metadata
                    'director_id'    => $camp->director_id,
                    'created_at'     => $camp->created_at->format('Y-m-d H:i:s'),
                    'updated_at'     => $camp->updated_at->format('Y-m-d H:i:s'),
                ];
            }),

            'pagination' => [
                'total'         => $camps->total(),
                'per_page'      => $camps->perPage(),
                'current_page'  => $camps->currentPage(),
                'last_page'     => $camps->lastPage(),
            ],
        ];

        return $this->success(
            'Camps fetched successfully.',
            $response,
            200
        );
    }

    /**
     * Get Admin Fee
     */
    public function getAdminFee()
    {
        $adminFee = config('camp.extra_price');
        // অথবা config('app.camp_extra_price')

        return $this->success(
            'Admin fee fetched successfully.',
            ['admin_fee' => $adminFee],
            200
        );
    }
}
