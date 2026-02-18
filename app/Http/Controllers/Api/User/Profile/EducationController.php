<?php

namespace App\Http\Controllers\Api\User\Profile;

use Exception;
use App\Models\Education;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\EducationRequest;
use App\Http\Resources\User\EducationResource;

class EducationController extends Controller
{
    use ApiResponse;

    /**
     * Get logged-in user's education list
     */
    public function index()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            $educations = Education::where('user_id', $user->id)
                ->latest()
                ->get();

            return $this->success(
                'Education list retrieved successfully',
                EducationResource::collection($educations)
            );
        } catch (Exception $e) {
            Log::error('Education index error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to retrieve education list',
                500
            );
        }
    }

    /**
     * Store new education
     */
    // public function store(EducationRequest $request)
    // {
    //     try {
    //         $user = auth('api')->user();

    //         if (!$user) {
    //             return $this->error(null, 'User not found', 404);
    //         }

    //         $education = Education::create([
    //             'user_id'          => $user->id,
    //             'country'          => $request->country,
    //             'institution_name' => $request->institution_name,
    //             'degree'           => $request->degree,
    //             'major'            => $request->major,
    //             'graduation_year'  => $request->graduation_year,
    //         ]);

    //         return $this->success(
    //             'Education added successfully',
    //             new EducationResource($education)
    //         );
    //     } catch (Exception $e) {
    //         Log::error('Education store error: ' . $e->getMessage());

    //         return $this->error(
    //             ['exception' => $e->getMessage()],
    //             'Failed to add education',
    //             500
    //         );
    //     }
    // }


    public function store(EducationRequest $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            $educationsData = [];

            foreach ($request->educations as $edu) {
                $educationsData[] = [
                    'user_id'          => $user->id,
                    'country'          => $edu['country'] ?? null,
                    'institution_name' => $edu['institution_name'],
                    'degree'           => $edu['degree'],
                    'major'            => $edu['major'] ?? null,
                    'graduation_year'  => $edu['graduation_year'] ?? null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }

            // Bulk insert (fast + clean)
            Education::insert($educationsData);

            $educations = Education::where('user_id', $user->id)->latest()->get();

            return $this->success(
                'Educations added successfully',
                EducationResource::collection($educations)
            );
        } catch (Exception $e) {
            Log::error('Education bulk store error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to add educations',
                500
            );
        }
    }


    /**
     * Update education
     */
    // public function update(EducationRequest $request, $id)
    // {
    //     try {
    //         $user = auth('api')->user();

    //         $education = Education::where('id', $id)
    //             ->where('user_id', $user->id)
    //             ->first();

    //         if (!$education) {
    //             return $this->error(null, 'Education record not found', 404);
    //         }

    //         $education->update($request->validated());

    //         return $this->success(
    //             'Education updated successfully',
    //             new EducationResource($education)
    //         );
    //     } catch (Exception $e) {
    //         Log::error('Education update error: ' . $e->getMessage());

    //         return $this->error(
    //             ['exception' => $e->getMessage()],
    //             'Failed to update education',
    //             500
    //         );
    //     }
    // }


    public function update(EducationRequest $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            $existingIds = Education::where('user_id', $user->id)->pluck('id')->toArray();
            $incomingIds = collect($request->educations)->pluck('id')->filter()->toArray();

            // Delete removed educations
            $deleteIds = array_diff($existingIds, $incomingIds);
            Education::whereIn('id', $deleteIds)->delete();

            foreach ($request->educations as $edu) {

                Education::updateOrCreate(
                    [
                        'id' => $edu['id'] ?? null
                    ],
                    [
                        'user_id'          => $user->id,
                        'country'          => $edu['country'] ?? null,
                        'institution_name' => $edu['institution_name'],
                        'degree'           => $edu['degree'],
                        'major'            => $edu['major'] ?? null,
                        'graduation_year'  => $edu['graduation_year'] ?? null,
                    ]
                );
            }

            $educations = Education::where('user_id', $user->id)->latest()->get();

            return $this->success(
                'Educations updated successfully',
                EducationResource::collection($educations)
            );
        } catch (Exception $e) {
            Log::error('Education bulk update error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to update educations',
                500
            );
        }
    }


    /**
     * Delete education
     */
    public function destroy($id)
    {
        try {
            $user = auth('api')->user();

            $education = Education::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$education) {
                return $this->error(null, 'Education record not found', 404);
            }

            $education->delete();

            return $this->success(
                'Education deleted successfully'
            );
        } catch (Exception $e) {
            Log::error('Education delete error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to delete education',
                500
            );
        }
    }
}
