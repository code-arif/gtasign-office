<?php

namespace App\Http\Controllers\Api\Frontend\Evaluator;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Director\Models\Camp;
use App\Http\Controllers\Controller;
use Modules\Director\Transformers\CampResource;

class EvaluatorController extends Controller
{
    use ApiResponse;

    /**
     * All Active Camps List
     */
    public function getActiveCamps(Request $request)
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
}
