<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\OrderReview;
use App\Models\SellerEarnings;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * Get User Profile
     */
    // public function profile()
    // {
    //     try {
    //         $user = auth('api')->user()->load('profile');

    //         if (!$user) {
    //             return $this->error(
    //                 null,
    //                 'User not found',
    //                 404
    //             );
    //         }

    //         return $this->success(
    //             'User profile retrieved successfully',
    //             new UserResource($user)
    //         );
    //     } catch (Exception $e) {
    //         Log::error('Get profile error: ' . $e->getMessage());
    //         return $this->error(
    //             ['exception' => $e->getMessage()],
    //             'Failed to retrieve profile',
    //             500
    //         );
    //     }
    // }

    public function profile()
    {
        try {
            $user = auth('api')->user()->load('profile');

            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            $stats = null;

            if ($user->role === 'expert') {
                $stats = $this->computeExpertStats($user->id);
            }

            return $this->success(
                'User profile retrieved successfully',
                new UserResource(['user' => $user, 'stats' => $stats])
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

    private function computeExpertStats(int $userId): array
    {
        // ─── Rating & Reviews ───────────────────────────────────────────
        $reviewStats = OrderReview::where('reviewed_user_id', $userId)
            ->selectRaw('
            ROUND(AVG(rating), 1)                   AS avg_rating,
            COUNT(*)                                AS total_reviews,
            ROUND(AVG(communication_rating), 1)     AS avg_communication,
            ROUND(AVG(service_rating), 1)           AS avg_service,
            ROUND(AVG(delivery_rating), 1)          AS avg_delivery
        ')
            ->first();

        // ─── Success Score ───────────────────────────────────────────────
        // Success = completed / (completed + cancelled) × 100
        $orderCounts = \App\Models\Order::where('seller_id', $userId)
            ->whereIn('status', ['completed', 'cancelled'])
            ->selectRaw("
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed
        ")
            ->first();

        $successScore = ($orderCounts->total > 0)
            ? round(($orderCounts->completed / $orderCounts->total) * 100)
            : 0;

        // ─── Last Month Earnings ─────────────────────────────────────────
        $lastMonth         = now()->subMonth();
        $lastMonthEarnings = SellerEarnings::where('seller_id', $userId)
            ->whereIn('status', ['available', 'withdrawn'])
            ->whereYear('created_at', $lastMonth->year)
            ->whereMonth('created_at', $lastMonth->month)
            ->sum('net_amount');

        // ─── Avg Response Time (minutes) ─────────────────────────────────
        // Logic: first reply from expert (sender_id = userId) after each
        // incoming message (receiver_id = userId), grouped per room
        $avgResponseMinutes = DB::select("
        SELECT ROUND(AVG(TIMESTAMPDIFF(MINUTE, incoming.created_at, reply.created_at))) AS avg_minutes
        FROM chats AS incoming
        INNER JOIN chats AS reply
            ON  reply.room_id    = incoming.room_id
            AND reply.sender_id  = :seller_id
            AND reply.created_at > incoming.created_at
            AND reply.id = (
                SELECT MIN(r2.id)
                FROM chats r2
                WHERE r2.room_id   = incoming.room_id
                  AND r2.sender_id = :seller_id2
                  AND r2.created_at > incoming.created_at
                  AND r2.deleted_at IS NULL
            )
        WHERE incoming.receiver_id = :seller_id3
          AND incoming.deleted_at IS NULL
    ", [
            'seller_id'  => $userId,
            'seller_id2' => $userId,
            'seller_id3' => $userId,
        ]);

        $avgResponseMinutes = $avgResponseMinutes[0]->avg_minutes ?? null;

        return [
            'avg_rating'          => $reviewStats->avg_rating       ?? 0,
            'total_reviews'       => (int) ($reviewStats->total_reviews ?? 0),
            'avg_communication'   => $reviewStats->avg_communication ?? 0,
            'avg_service'         => $reviewStats->avg_service       ?? 0,
            'avg_delivery'        => $reviewStats->avg_delivery      ?? 0,
            'success_score'       => $successScore,                       // e.g. 86
            'last_month_earnings' => round((float) $lastMonthEarnings, 2), // e.g. 436.00
            'avg_response_minutes' => $avgResponseMinutes              // e.g. 24
                ? (int) $avgResponseMinutes
                : null,
            'last_month_label'    => $lastMonth->format('F'),             // e.g. "November"
        ];
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
