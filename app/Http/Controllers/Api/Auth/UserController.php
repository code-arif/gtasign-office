<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Certification;
use App\Models\Chat;
use App\Models\CustomOffer;
use App\Models\Education;
use App\Models\ExtensionRequest;
use App\Models\FirebaseTokens;
use App\Models\Gig;
use App\Models\GigDocument;
use App\Models\GigImage;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\OrderDelivery;
use App\Models\OrderQaReview;
use App\Models\OrderReview;
use App\Models\OtpVerification;
use App\Models\Profile;
use App\Models\Room;
use App\Models\RoomPin;
use App\Models\SellerEarnings;
use App\Models\User;
use App\Models\UserAvailability;
use App\Models\UserExperience;
use App\Models\UserSecurityToken;
use App\Models\WithdrawalRequests;
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
    public function profile()
    {
        try {
            $user = auth('api')->user()->load('profile', 'languages');

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
        // Rating & Reviews
        $reviewStats = OrderReview::where('reviewed_user_id', $userId)
            ->selectRaw('
            ROUND(AVG(rating), 1)                   AS avg_rating,
            COUNT(*)                                AS total_reviews,
            ROUND(AVG(communication_rating), 1)     AS avg_communication,
            ROUND(AVG(service_rating), 1)           AS avg_service,
            ROUND(AVG(delivery_rating), 1)          AS avg_delivery
        ')
            ->first();

        // Success Score
        // Success = completed / (completed + cancelled) × 100
        $orderCounts = Order::where('seller_id', $userId)
            ->whereIn('status', ['completed', 'cancelled'])
            ->selectRaw("
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed
        ")
            ->first();

        $successScore = ($orderCounts->total > 0)
            ? round(($orderCounts->completed / $orderCounts->total) * 100)
            : 0;

        // Last Month Earnings
        $lastMonth         = now()->subMonth();
        $lastMonthEarnings = SellerEarnings::where('seller_id', $userId)
            ->whereIn('status', ['available', 'withdrawn'])
            ->whereYear('created_at', $lastMonth->year)
            ->whereMonth('created_at', $lastMonth->month)
            ->sum('net_amount');

        // Avg Response Time (minutes)
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

        // Rating Breakdown
        $breakdown = OrderReview::where('reviewed_user_id', $userId)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $ratingBreakdown = [
            'five_star' => $breakdown[5] ?? 0,
            'four_star' => $breakdown[4] ?? 0,
            'three_star' => $breakdown[3] ?? 0,
            'two_star' => $breakdown[2] ?? 0,
            'one_star' => $breakdown[1] ?? 0,
        ];



        // Recent Reviews
        $recentReviews = OrderReview::where('reviewed_user_id', $userId)
            ->with(['reviewer.profile', 'order', 'gig'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'content' => $review->review,
                    'created_at' => $review->created_at->diffForHumans(),
                    'reviewer' => [
                        'name' => $review->reviewer->profile->first_name . ' ' . $review->reviewer->profile->last_name,
                        'avatar' => $review->reviewer->profile->avatar
                            ? asset('storage/' . $review->reviewer->profile->avatar)
                            : asset('default/profile.jpg'),
                        'address' => $review->reviewer->profile->address,
                    ],
                    'order' => [
                        'price' => $review->order->price,
                        'duration' => ($review->order->delivery_days % 7 == 0)
                            ? ($review->order->delivery_days / 7) . ' ' . (($review->order->delivery_days / 7) > 1 ? 'weeks' : 'week')
                            : $review->order->delivery_days . ' ' . ($review->order->delivery_days > 1 ? 'days' : 'day'),
                    ],

                    'gig' => [
                        'id' => $review->gig->id ?? null,
                        'title' => $review->gig->title ?? null,
                        'image' => $review->gig->primaryImage?->image_url
                            ? asset('storage/' . $review->gig->primaryImage->image_url)
                            : asset('default/no_image.webp'),
                    ]
                ];
            });

        return [
            'avg_rating' => $reviewStats->avg_rating ?? 0,
            'total_reviews' => (int) ($reviewStats->total_reviews ?? 0),
            'avg_communication' => $reviewStats->avg_communication ?? 0,
            'avg_service' => $reviewStats->avg_service ?? 0,
            'avg_delivery' => $reviewStats->avg_delivery ?? 0,
            'success_score' => $successScore,
            'last_month_earnings' => round((float) $lastMonthEarnings, 2),
            'avg_response_minutes' => $avgResponseMinutes ? (int) $avgResponseMinutes : null,
            'last_month_label' => $lastMonth->format('F'),
            'rating_breakdown' => $ratingBreakdown,
            'recent_reviews' => $recentReviews,
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
                'avatar'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
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

            // Ensure profile exists
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

                // Avatar upload if provided
                if (isset($validatedData['avatar'])) {
                    $profileData['avatar'] = Helper::fileUpload($request->file('avatar'), 'user/avatar');
                }

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

                // Avatar upload if provided
                if (isset($validatedData['avatar'])) {
                    // Delete old avatar if exists
                    if (!empty($user->profile->avatar) && file_exists(public_path($user->profile->avatar))) {
                        Helper::fileDelete(public_path($user->profile->avatar));
                    }

                    $profileData['avatar'] = Helper::fileUpload($request->file('avatar'), 'user/avatar');
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
     * Delete User Profile — cascades all related assets.
     * @method DELETE
     * @route  /api/v1/delete-profile
     * @middleware auth:api
     *
     * Deletion order (respects FK dependencies):
     *  1. Order child records (activities, QA reviews, extension requests, deliveries)
     *  2. Order reviews  (reviewer / reviewed)
     *  3. Seller earnings & withdrawal requests
     *  4. Orders (force-delete, both as buyer & seller)
     *  5. Gig assets (images + documents with file cleanup, tag pivots, gigs)
     *  6. Chat messages in shared rooms (with file cleanup), custom offers, room-pins, rooms
     *  7. Any remaining direct chats / custom offers not tied to a room
     *  8. Notifications
     *  9. Firebase tokens
     * 10. Profile sub-records (education, certifications, skills, availability, language pivots)
     * 11. OTP & security tokens
     * 12. Avatar file  →  profile record
     * 13. JWT logout  →  force-delete user
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

            DB::transaction(function () use ($user) {
                $userId = $user->id;

                // 1. Collect IDs we need repeatedly
                $gigIds = Gig::withTrashed()
                    ->where('user_id', $userId)
                    ->pluck('id')
                    ->toArray();

                $orderIds = Order::withTrashed()
                    ->where('buyer_id', $userId)
                    ->orWhere('seller_id', $userId)
                    ->pluck('id')
                    ->toArray();

                $roomIds = Room::where('first_user_id', $userId)
                    ->orWhere('second_user_id', $userId)
                    ->pluck('id')
                    ->toArray();

                // 2. Order child records
                if (!empty($orderIds)) {
                    OrderActivity::whereIn('order_id', $orderIds)->delete();
                    OrderQaReview::whereIn('order_id', $orderIds)->delete();
                    ExtensionRequest::whereIn('order_id', $orderIds)->delete();
                    // OrderDelivery files are stored in the order room's chat;
                    // we handle file cleanup in step 6 (chat deletion).
                    OrderDelivery::whereIn('order_id', $orderIds)->delete();
                }

                // 3. Order reviews
                OrderReview::where('reviewer_id', $userId)
                    ->orWhere('reviewed_user_id', $userId)
                    ->delete();

                // 4. Seller earnings & withdrawal requests
                SellerEarnings::where('seller_id', $userId)->delete();
                WithdrawalRequests::where('seller_id', $userId)->delete();

                // 5. Orders (buyer + seller)
                if (!empty($orderIds)) {
                    Order::withTrashed()->whereIn('id', $orderIds)->forceDelete();
                }

                // 6. Gig assets
                if (!empty($gigIds)) {
                    // Model boot events delete the physical files automatically
                    GigImage::whereIn('gig_id', $gigIds)
                        ->get()
                        ->each(fn($img) => $img->delete());

                    GigDocument::whereIn('gig_id', $gigIds)
                        ->get()
                        ->each(fn($doc) => $doc->delete());

                    // Detach tag pivots
                    DB::table('gig_tags')->whereIn('gig_id', $gigIds)->delete();

                    // Force-delete gigs (uses SoftDeletes)
                    Gig::withTrashed()->whereIn('id', $gigIds)->forceDelete();
                }

                // 7. Chats, custom offers & rooms
                if (!empty($roomIds)) {
                    // Chat model boot event deletes attached files
                    Chat::withTrashed()
                        ->whereIn('room_id', $roomIds)
                        ->get()
                        ->each(fn($msg) => $msg->forceDelete());

                    CustomOffer::whereIn('room_id', $roomIds)->delete();
                    RoomPin::whereIn('room_id', $roomIds)->delete();
                    Room::whereIn('id', $roomIds)->delete();
                }

                // Clean up any direct chats not belonging to a room
                Chat::withTrashed()
                    ->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId)
                    ->get()
                    ->each(fn($msg) => $msg->forceDelete());

                // Clean up any custom offers not already deleted above
                CustomOffer::where('expert_id', $userId)
                    ->orWhere('client_id', $userId)
                    ->delete();

                // 8. Notifications
                Notification::where('notifiable_type', User::class)
                    ->where('notifiable_id', $userId)
                    ->delete();

                // 9. Firebase tokens
                FirebaseTokens::where('user_id', $userId)->delete();

                // 10. Profile sub-records
                Education::where('user_id', $userId)->delete();
                Certification::where('user_id', $userId)->delete();
                UserExperience::where('user_id', $userId)->delete();
                UserAvailability::where('user_id', $userId)->delete();
                DB::table('user_languages')->where('user_id', $userId)->delete();

                // 11. OTP & security tokens
                OtpVerification::where('user_id', $userId)->delete();
                UserSecurityToken::where('user_id', $userId)->delete();

                // 12. Avatar file & profile record
                if ($user->profile?->avatar) {
                    Helper::fileDelete($user->profile->avatar);
                }
                $user->profile?->delete();

                // ── 13. Revoke JWT & permanently delete user ──────────────
                auth('api')->logout();
                $user->forceDelete();
            });

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
        while (Profile::where('username', $username)->exists()) {
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
        while (Profile::where('slug', $slug)->exists()) {
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
