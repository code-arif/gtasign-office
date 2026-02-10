<?php

namespace App\Http\Controllers\Api\Auth\Client;

use Exception;
use App\Helpers\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;

class ClientAuthController extends Controller
{
    use ApiResponse;

    /**
     * Get Client Profile
     */
    public function profile()
    {
        try {
            $user = auth('api')->user()->load('profile');

            if (!$user) {
                return $this->error(
                    null,
                    'User not found',
                    404
                );
            }

            return $this->success(
                'User profile retrieved successfully',
                new UserResource($user)
            );
        } catch (Exception $e) {
            Log::error('Get profile error: ' . $e->getMessage());
            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to retrieve profile',
                500
            );
        }
    }

    /**
     * Update Client Profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = auth('api')->user()->load('profile');

            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            // Validation (only allowed fields)
            $validator = Validator::make($request->all(), [
                'first_name' => 'nullable|string|max:100',
                'last_name'  => 'nullable|string|max:100',
                'avatar'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
            ]);

            if ($validator->fails()) {
                return $this->validationError(
                    $validator->errors()->toArray(),
                    'Validation failed',
                    422
                );
            }

            $validated = $validator->validated();

            /*
        |--------------------------------------------------------------------------
        | Ensure profile exists
        |--------------------------------------------------------------------------
        */
            if (!$user->profile) {
                $user->profile()->create([
                    'first_name' => $validated['first_name'] ?? null,
                    'last_name'  => $validated['last_name'] ?? null,
                    'username'   => $this->generateUsername($validated['first_name'] ?? 'user'),
                    'slug'       => $this->generateSlug($validated['first_name'] ?? 'user'),
                ]);
            }

            $profileData = [];

            // Update name fields
            if (array_key_exists('first_name', $validated)) {
                $profileData['first_name'] = $validated['first_name'];
            }

            if (array_key_exists('last_name', $validated)) {
                $profileData['last_name'] = $validated['last_name'];
            }

            /*
        |--------------------------------------------------------------------------
        | Avatar upload (copied from updateAvatar)
        |--------------------------------------------------------------------------
        */
            if (isset($validated['avatar'])) {

                // Delete old avatar if exists
                if (!empty($user->profile->avatar) && file_exists(public_path($user->profile->avatar))) {
                    Helper::fileDelete(public_path($user->profile->avatar));
                }

                // Upload new avatar
                $avatarPath = Helper::fileUpload($validated['avatar'], 'user/avatar');
                $profileData['avatar'] = $avatarPath;
            }

            // Update profile
            $user->profile->update($profileData);

            // Reload fresh data
            $user->refresh()->load('profile');

            return $this->success(
                'Profile updated successfully',
                new UserResource($user)
            );
        } catch (Exception $e) {
            Log::error('Update profile error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to update profile',
                500
            );
        }
    }

    /**
     * Update overview (biography and tagline)
     */
    public function updateOverview(Request $request)
    {
        try {
            $user = auth('api')->user()->load('profile');

            if (!$user) {
                return $this->error(
                    null,
                    'User not found',
                    404
                );
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'biography'  => 'nullable|string|max:2500',
                'tagline'    => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->validationError(
                    $validator->errors()->toArray(),
                    'Validation failed',
                    422
                );
            }

            $validatedData = $validator->validated();

            // Update or create profile
            if (!$user->profile) {
                // Create profile if doesn't exist
                $profileData = [
                    'biography'  => $validatedData['biography'] ?? null,
                    'tagline'    => $validatedData['tagline'] ?? null,
                ];

                $user->profile()->create($profileData);
            } else {
                // Update existing profile
                $profileData = [];
                if (isset($validatedData['biography'])) {
                    $profileData['biography'] = $validatedData['biography'];
                }
                if (isset($validatedData['tagline'])) {
                    $profileData['tagline'] = $validatedData['tagline'];
                }
                $user->profile->update($profileData);
            }

            // Reload user with profile
            $user->refresh()->load('profile');

            return $this->success(
                'Overview updated successfully',
                new UserResource($user)
            );
        } catch (Exception $e) {
            Log::error('Update overview error: ' . $e->getMessage());
            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to update profile',
                500
            );
        }
    }


    /**
     * Generate unique username
     */
    private function generateUsername($firstName)
    {
        $baseUsername = strtolower(str_replace(' ', '_', $firstName));
        $username = $baseUsername . '_' . $this->randomAlphaNum(4);

        // Check if username exists, regenerate if needed
        while (\App\Models\Profile::where('username', $username)->exists()) {
            $username = $baseUsername . '_' . $this->randomAlphaNum(4);
        }

        return $username;
    }

    /**
     * Generate unique slug
     */
    private function generateSlug($firstName)
    {
        $baseSlug = strtolower(str_replace(' ', '-', $firstName));
        $slug = $baseSlug . '-' . $this->randomAlphaNum(6);

        // Check if slug exists, regenerate if needed
        while (\App\Models\Profile::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $this->randomAlphaNum(6);
        }

        return $slug;
    }

    /**
     * Generate random alphanumeric strings
     */
    private function randomAlphaNum($length = 4)
    {
        return substr(
            str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'),
            0,
            $length
        );
    }
}
