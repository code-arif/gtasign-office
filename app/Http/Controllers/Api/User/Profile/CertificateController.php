<?php

namespace App\Http\Controllers\Api\User\Profile;

use Exception;
use App\Helpers\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Certification;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CertificationRequest;
use App\Http\Resources\User\CertificationResource;

class CertificateController extends Controller
{
    use ApiResponse;

    /**
     * Get logged-in user's certificate list
     */
    public function index()
    {
        try {
            $user = auth('api')->user();
            if (!$user) return $this->error(null, 'User not found', 404);

            $certifications = Certification::where('user_id', $user->id)->latest()->get();
            return $this->success('Certifications retrieved', CertificationResource::collection($certifications));
        } catch (Exception $e) {
            Log::error('Certification index error: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Failed to retrieve certifications', 500);
        }
    }


    /**
     * Store new certificate
     */
    // public function store(CertificationRequest $request)
    // {
    //     try {
    //         $user = auth('api')->user();
    //         if (!$user) return $this->error(null, 'User not found', 404);

    //         $filePath = Helper::fileUpload($request->file('file'), 'certifications');

    //         $certification = Certification::create([
    //             'user_id' => $user->id,
    //             'name' => $request->name,
    //             'awarded_by' => $request->awarded_by,
    //             'year' => $request->year,
    //             'file_path' => $filePath,
    //         ]);

    //         return $this->success('Certification added', new CertificationResource($certification));
    //     } catch (Exception $e) {
    //         Log::error('Certification store error: ' . $e->getMessage());
    //         return $this->error(['exception' => $e->getMessage()], 'Failed to add certification', 500);
    //     }
    // }

    public function store(CertificationRequest $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) return $this->error(null, 'User not found', 404);

            $created = [];

            foreach ($request->certifications as $index => $cert) {

                $filePath = null;

                if ($request->hasFile("certifications.$index.file")) {
                    $filePath = Helper::fileUpload(
                        $request->file("certifications.$index.file"),
                        'certifications'
                    );
                }

                $created[] = Certification::create([
                    'user_id' => $user->id,
                    'name' => $cert['name'],
                    'awarded_by' => $cert['awarded_by'],
                    'year' => $cert['year'],
                    'file_path' => $filePath,
                ]);
            }

            return $this->success(
                'Certifications added successfully',
                CertificationResource::collection(collect($created))
            );
        } catch (Exception $e) {
            Log::error('Certification bulk store error: ' . $e->getMessage());

            return $this->error(['exception' => $e->getMessage()], 'Failed to add certifications', 500);
        }
    }



    /**
     * Update certificate
     */
    // public function update(CertificationRequest $request, $id)
    // {
    //     try {
    //         $user = auth('api')->user();
    //         $cert = Certification::where('id', $id)->where('user_id', $user->id)->first();
    //         if (!$cert) return $this->error(null, 'Certification not found', 404);

    //         if ($request->hasFile('file')) {
    //             Helper::fileDelete($cert->file_path);
    //             $cert->file_path = Helper::fileUpload($request->file('file'), 'certifications');
    //         }

    //         $cert->update($request->only(['name', 'awarded_by', 'year']));

    //         return $this->success('Certification updated', new CertificationResource($cert));
    //     } catch (Exception $e) {
    //         Log::error('Certification update error: ' . $e->getMessage());
    //         return $this->error(['exception' => $e->getMessage()], 'Failed to update certification', 500);
    //     }
    // }

    public function update(CertificationRequest $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) return $this->error(null, 'User not found', 404);

            $existingIds = Certification::where('user_id', $user->id)->pluck('id')->toArray();
            $incomingIds = collect($request->certifications)->pluck('id')->filter()->toArray();

            // Delete removed certifications
            $deleteIds = array_diff($existingIds, $incomingIds);

            $deleteCerts = Certification::whereIn('id', $deleteIds)->get();
            foreach ($deleteCerts as $cert) {
                Helper::fileDelete($cert->file_path);
                $cert->delete();
            }

            foreach ($request->certifications as $index => $cert) {

                $model = Certification::firstOrNew([
                    'id' => $cert['id'] ?? null
                ]);

                $model->user_id = $user->id;
                $model->name = $cert['name'];
                $model->awarded_by = $cert['awarded_by'];
                $model->year = $cert['year'];

                if ($request->hasFile("certifications.$index.file")) {

                    Helper::fileDelete($model->file_path);

                    $model->file_path = Helper::fileUpload(
                        $request->file("certifications.$index.file"),
                        'certifications'
                    );
                }

                $model->save();
            }

            $certifications = Certification::where('user_id', $user->id)->latest()->get();

            return $this->success(
                'Certifications updated successfully',
                CertificationResource::collection($certifications)
            );
        } catch (Exception $e) {
            Log::error('Certification bulk update error: ' . $e->getMessage());

            return $this->error(['exception' => $e->getMessage()], 'Failed to update certifications', 500);
        }
    }


    /**
     * Delete certificate
     */
    public function destroy($id)
    {
        try {
            $user = auth('api')->user();
            $cert = Certification::where('id', $id)->where('user_id', $user->id)->first();
            if (!$cert) return $this->error(null, 'Certification not found', 404);

            Helper::fileDelete($cert->file_path);
            $cert->delete();

            return $this->success('Certification deleted');
        } catch (Exception $e) {
            Log::error('Certification delete error: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Failed to delete certification', 500);
        }
    }
}
