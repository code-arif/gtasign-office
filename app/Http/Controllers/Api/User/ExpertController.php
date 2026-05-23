<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ExpertController extends Controller
{
    use ApiResponse;

    /**
     * Get a paginated list of active experts.
     *
     * Response shape per expert (matches the card UI):
     * {
     *   "id"       : 5,
     *   "name"     : "Jerome Bell",
     *   "tagline"  : "AI Automation Engineer",
     *   "avatar"   : "https://...",
     *   "slug"     : "jerome-bell-abc123",
     *   "skills"   : ["Workflow Automation", "AI Agents", "API Integration"]
     * }
     *
     * @GET /v1/experts
     * Query params:
     *   - per_page  (default 12)
     *   - page      (default 1)
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 12), 50);

        $experts = User::role('expert')            // only the expert role
            ->where('status', 'active')
            ->whereHas('profile')                  // must have a profile
            ->with([
                'profile:user_id,first_name,last_name,tagline,avatar,slug',
                'experiences:user_id,skill_name',  // skills shown as tags
            ])
            ->inRandomOrder()
            ->paginate($perPage);

        $mappedData = collect($experts->items())->map(function ($user) {
            $profile = $user->profile;

            return [
                'id'      => $user->id,
                'name'    => trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? '')),
                'tagline' => $profile->tagline ?? null,
                'avatar'  => $profile->avatar
                    ? asset('storage/' . $profile->avatar)
                    : asset('default/profile.jpg'),
                'slug'    => $profile->slug ?? null,
                'skills'  => $user->experiences
                    ->pluck('skill_name')
                    ->filter()
                    ->values(),
            ];
        });

        $data = [
            'experts'    => $mappedData,
            'pagination' => [
                'page'         => $experts->lastPage(), // total pages
                'per_page'     => $experts->perPage(),
                'current_page' => $experts->currentPage(),
            ],
        ];

        return $this->success('Experts retrieved successfully', $data);
    }
}
