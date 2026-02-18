<?php

namespace App\Http\Controllers\Api\User\Profile;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\UserExperience;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserExperienceRequest;
use App\Http\Resources\User\UserExperienceResource;

class UserExperienceController extends Controller
{
    use ApiResponse;

    /**
     * List user skills
     */
    public function index()
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            $skills = UserExperience::where('user_id', $user->id)
                ->latest()
                ->get();

            return $this->success(
                'Skill list retrieved successfully',
                UserExperienceResource::collection($skills)
            );
        } catch (Exception $e) {
            Log::error('UserSkill index error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to retrieve skills',
                500
            );
        }
    }

    /**
     * Store skill
     */
    // public function store(UserExperienceRequest $request)
    // {
    //     try {
    //         $user = auth('api')->user();
    //         if (!$user) {
    //             return $this->error(null, 'User not found', 404);
    //         }

    //         $skill = UserExperience::create([
    //             'user_id' => $user->id,
    //             'skill_name' => $request->skill_name,
    //             'level' => $request->level,
    //         ]);

    //         return $this->success(
    //             'Skill added successfully',
    //             new UserExperienceResource($skill)
    //         );
    //     } catch (Exception $e) {
    //         Log::error('UserSkill store error: ' . $e->getMessage());

    //         return $this->error(
    //             ['exception' => $e->getMessage()],
    //             'Failed to add skill',
    //             500
    //         );
    //     }
    // }

    public function store(UserExperienceRequest $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) return $this->error(null, 'User not found', 404);

            $data = [];

            foreach ($request->skills as $skill) {
                $data[] = [
                    'user_id' => $user->id,
                    'skill_name' => $skill['skill_name'],
                    'level' => $skill['level'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            UserExperience::insert($data);

            $skills = UserExperience::where('user_id', $user->id)->latest()->get();

            return $this->success(
                'Skills added successfully',
                UserExperienceResource::collection($skills)
            );
        } catch (Exception $e) {
            Log::error('UserSkill bulk store error: ' . $e->getMessage());

            return $this->error(['exception' => $e->getMessage()], 'Failed to add skills', 500);
        }
    }


    /**
     * Update skill
     */
    // public function update(UserExperienceRequest $request, $id)
    // {
    //     try {
    //         $user = auth('api')->user();

    //         $skill = UserExperience::where('id', $id)
    //             ->where('user_id', $user->id)
    //             ->first();

    //         if (!$skill) {
    //             return $this->error(null, 'Skill not found', 404);
    //         }

    //         $skill->update($request->validated());

    //         return $this->success(
    //             'Skill updated successfully',
    //             new UserExperienceResource($skill)
    //         );
    //     } catch (Exception $e) {
    //         Log::error('UserSkill update error: ' . $e->getMessage());

    //         return $this->error(
    //             ['exception' => $e->getMessage()],
    //             'Failed to update skill',
    //             500
    //         );
    //     }
    // }

    public function update(UserExperienceRequest $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) return $this->error(null, 'User not found', 404);

            $existingIds = UserExperience::where('user_id', $user->id)->pluck('id')->toArray();
            $incomingIds = collect($request->skills)->pluck('id')->filter()->toArray();

            // Delete removed skills
            $deleteIds = array_diff($existingIds, $incomingIds);
            UserExperience::whereIn('id', $deleteIds)->delete();

            foreach ($request->skills as $skill) {

                UserExperience::updateOrCreate(
                    ['id' => $skill['id'] ?? null],
                    [
                        'user_id' => $user->id,
                        'skill_name' => $skill['skill_name'],
                        'level' => $skill['level'],
                    ]
                );
            }

            $skills = UserExperience::where('user_id', $user->id)->latest()->get();

            return $this->success(
                'Skills updated successfully',
                UserExperienceResource::collection($skills)
            );
        } catch (Exception $e) {
            Log::error('UserSkill bulk update error: ' . $e->getMessage());

            return $this->error(['exception' => $e->getMessage()], 'Failed to update skills', 500);
        }
    }


    /**
     * Delete skill
     */
    public function destroy($id)
    {
        try {
            $user = auth('api')->user();

            $skill = UserExperience::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$skill) {
                return $this->error(null, 'Skill not found', 404);
            }

            $skill->delete();

            return $this->success('Skill deleted successfully');
        } catch (Exception $e) {
            Log::error('UserSkill delete error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to delete skill',
                500
            );
        }
    }
}
