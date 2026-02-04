<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Helpers\Helper;
use App\Traits\ApiResponse;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * Get User Profile
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
     * Update profile
     */
    public function updateProfile(Request $request)
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
                'first_name' => 'nullable|string|max:100',
                'last_name'  => 'nullable|string|max:100',
                'biography'  => 'nullable|string|max:2500',
                'tagline'    => 'nullable|string|max:255',
                'phone'      => 'nullable|string|max:150|unique:users,phone,' . $user->id,
                'address'    => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->validationError(
                    $validator->errors()->toArray(),
                    'Validation failed',
                    422
                );
            }

            $validatedData = $validator->validated();

            // Update user table fields (phone)
            if (isset($validatedData['phone'])) {
                $user->update(['phone' => $validatedData['phone']]);
            }

            // Update or create profile
            if (!$user->profile) {
                // Create profile if doesn't exist
                $profileData = [
                    'first_name' => $validatedData['first_name'] ?? null,
                    'last_name'  => $validatedData['last_name'] ?? null,
                    'biography'  => $validatedData['biography'] ?? null,
                    'tagline'    => $validatedData['tagline'] ?? null,
                    'address'    => $validatedData['address'] ?? null,
                    'username'   => $this->generateUsername($validatedData['first_name'] ?? 'user'),
                    'slug'       => $this->generateSlug($validatedData['first_name'] ?? 'user'),
                ];

                $user->profile()->create($profileData);
            } else {
                // Update existing profile
                $profileData = [];

                if (isset($validatedData['first_name'])) {
                    $profileData['first_name'] = $validatedData['first_name'];
                }
                if (isset($validatedData['last_name'])) {
                    $profileData['last_name'] = $validatedData['last_name'];
                }
                if (isset($validatedData['biography'])) {
                    $profileData['biography'] = $validatedData['biography'];
                }
                if (isset($validatedData['tagline'])) {
                    $profileData['tagline'] = $validatedData['tagline'];
                }
                if (isset($validatedData['address'])) {
                    $profileData['address'] = $validatedData['address'];
                }

                $user->profile->update($profileData);
            }

            // Reload user with profile
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
     * Update User Avatar
     */
    public function updateAvatar(Request $request)
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
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
            ]);

            if ($validator->fails()) {
                return $this->validationError(
                    $validator->errors()->toArray(),
                    'Validation failed',
                    422
                );
            }

            // Ensure profile exists
            if (!$user->profile) {
                return $this->error(
                    null,
                    'Profile not found. Please update your profile first.',
                    404
                );
            }

            // Delete old avatar if exists
            if (!empty($user->profile->avatar) && file_exists(public_path($user->profile->avatar))) {
                Helper::fileDelete(public_path($user->profile->avatar));
            }

            // Upload new avatar
            $avatarPath = Helper::fileUpload($request->file('avatar'), 'user/avatar');

            // Update profile avatar
            $user->profile->update(['avatar' => $avatarPath]);

            // Reload user with profile
            $user->refresh()->load('profile');

            return $this->success(
                'Avatar updated successfully',
                new UserResource($user)
            );
        } catch (Exception $e) {
            Log::error('Update avatar error: ' . $e->getMessage());
            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to update avatar',
                500
            );
        }
    }

    /**
     * Delete User Profile
     * @method DELETE
     * @route /api/v1/delete-profile
     * @middleware auth:api
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        try {
            $user = auth('api')->user()->load('profile');

            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            // Confirm password
            if (!Hash::check($request->password, $user->password)) {
                return $this->error(null, 'Invalid password', 403);
            }

            // Delete avatar from storage
            if ($user->profile?->avatar) {
                Helper::fileDelete($user->profile->avatar);
            }

            // Logout user
            auth('api')->logout();

            // Permanently delete user (profile auto deleted)
            $user->forceDelete();

            return $this->success('Account deleted successfully');
        } catch (Exception $e) {
            Log::error('Delete profile error: ' . $e->getMessage());
            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to delete account',
                500
            );
        }
    }


    /**
     * Change User Password
     * @method POST
     * @route /api/v1/change-password
     * @middleware auth:api
     */
    public function changePassword(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error(
                    null,
                    'User not found',
                    404
                );
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'old_password'     => 'required|string',
                'new_password'     => 'required|string|min:6|max:50',
                'confirm_password' => 'required|string|same:new_password',
            ]);

            if ($validator->fails()) {
                return $this->validationError(
                    $validator->errors()->toArray(),
                    'Validation failed',
                    422
                );
            }

            // Check if old password is correct
            if (!Hash::check($request->old_password, $user->password)) {
                return $this->error(
                    null,
                    'Old password does not match',
                    400
                );
            }

            // Update with new password
            $user->password = Hash::make($request->new_password);
            $user->save();

            return $this->success(
                'Password changed successfully',
                null
            );
        } catch (Exception $e) {
            Log::error('Change password error: ' . $e->getMessage());
            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to change password',
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
