<?php

namespace App\Http\Controllers\Api\Auth\Client;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class UserLanguageController extends Controller
{
    use ApiResponse;

    /**
     * Get user's languages
     */
    public function languages()
    {
        try {
            $user = auth('api')->user()->load('languages');

            return $this->success(
                'User languages retrieved successfully',
                $user->languages
            );
        } catch (Exception $e) {
            Log::error('Get languages error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to retrieve languages',
                500
            );
        }
    }


    /**
     * Update user languages
     */
    public function updateLanguages(Request $request)
    {
        try {
            $user = auth('api')->user();

            $validator = Validator::make($request->all(), [
                'languages' => 'required|array|min:1',
                'languages.*.language_id' => 'required|exists:languages,id',
                'languages.*.proficiency' => 'nullable|in:basic,conversational,fluent,native',
            ]);

            if ($validator->fails()) {
                return $this->validationError(
                    $validator->errors()->toArray(),
                    'Validation failed',
                    422
                );
            }

            $syncData = [];

            foreach ($request->languages as $language) {
                $syncData[$language['language_id']] = [
                    'proficiency' => $language['proficiency'] ?? null
                ];
            }

            $user->languages()->sync($syncData);

            $user->refresh()->load('languages');

            return $this->success(
                'Languages updated successfully',
                $user->languages
            );
        } catch (Exception $e) {
            Log::error('Update languages error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to update languages',
                500
            );
        }
    }


    /**
     * Remove a language from the user's profile
     */
    public function removeLanguage($languageId)
    {
        try {
            $user = auth('api')->user();

            if (!$user->languages()->where('language_id', $languageId)->exists()) {
                return $this->error(
                    null,
                    'Language not found for this user',
                    404
                );
            }

            $user->languages()->detach($languageId);

            return $this->success(
                'Language removed successfully',
                null
            );
        } catch (Exception $e) {
            Log::error('Remove language error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to remove language',
                500
            );
        }
    }
}
