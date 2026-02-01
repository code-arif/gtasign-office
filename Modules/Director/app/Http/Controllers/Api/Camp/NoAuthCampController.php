<?php

namespace Modules\Director\Http\Controllers\Api\Camp;

use App\Models\SportsType;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Director\Models\Camp;
use App\Http\Controllers\Controller;
use Modules\Director\Transformers\CampResource;
use Illuminate\Pagination\LengthAwarePaginator;

class NoAuthCampController extends Controller
{
    use ApiResponse;

    // get sports type
    public function getSportsType()
    {
        $sportsTypes = SportsType::where('status', 'active')->get();

        if ($sportsTypes->isEmpty()) {
            return response()->json([
                'message' => 'No active sports types found.'
            ], 404);
        }

        return response()->json([
            'status' => 'true',
            'message' => 'Active sports types retrieved successfully.',
            'data' => $sportsTypes
        ], 200);
    }

    /**
     * List camps
     */
    public function campList(Request $request)
    {
        $query = Camp::query()->where('status', 'active');

        // -----------------------------
        // Filter: Sports Type
        // -----------------------------
        if ($request->filled('sports_type_id')) {
            $query->where('sports_type_id', $request->sports_type_id);
        }

        // -----------------------------
        // Filter: Location
        // -----------------------------
        if ($request->filled('location')) {
            $query->where('location', 'LIKE', "%{$request->location}%");
        }

        // -----------------------------
        // Filter: Date Range
        // -----------------------------
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        }

        // -----------------------------
        // Sorting Type → newest / oldest / upcoming
        // -----------------------------
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'upcoming':
                    $query->where('start_date', '>=', now()->format('Y-m-d'))
                        ->orderBy('start_date', 'asc');
                    break;
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        // -----------------------------
        // Pagination
        // -----------------------------
        $perPage = $request->filled('per_page') ? intval($request->per_page) : 10;
        $camps = $query->paginate($perPage);

        // Custom Pagination Format
        $response = [
            'camp_list'         => CampResource::collection($camps->items()),
            'pagination'   => [
                'total'         => $camps->total(),
                'per_page'      => $camps->perPage(),
                'current_page'  => $camps->currentPage(),
                'last_page'     => $camps->lastPage(),
            ],
        ];

        return $this->success(
            'Camp list fetched successfully.',
            $response,
            200
        );
    }

    /**
     * Get list of locations
     */
    public function getLocations(Request $request)
    {
        // Get all unique first-word locations
        $locations = Camp::selectRaw("DISTINCT SUBSTRING_INDEX(location, ',', 1) as location")
            ->whereNotNull('location')
            ->pluck('location');

        // Manual Pagination
        $perPage = $request->filled('per_page') ? intval($request->per_page) : 10;
        $page = $request->filled('page') ? intval($request->page) : 1;

        $total = $locations->count();
        $results = $locations->slice(($page - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Custom format for Trait
        $response = [
            'location_list'         => $paginated->items(),
            'pagination'   => [
                'total'         => $paginated->total(),
                'per_page'      => $paginated->perPage(),
                'current_page'  => $paginated->currentPage(),
                'last_page'     => $paginated->lastPage(),
            ],
        ];

        return $this->success(
            'Location list fetched successfully.',
            $response,
            200
        );
    }
}
