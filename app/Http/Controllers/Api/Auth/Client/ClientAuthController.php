<?php

namespace App\Http\Controllers\Api\Auth\Client;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\ClientUserResource;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Models\OrderReview;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            $user = auth('api')->user()->load('profile', 'languages');

            if (!$user) {
                return $this->error(
                    null,
                    'User not found',
                    404
                );
            }

            $stats = $this->computeClientStats($user->id);

            return $this->success(
                'User profile retrieved successfully',
                new ClientUserResource(['user' => $user, 'stats' => $stats])
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
                new ClientUserResource($user)
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
                new ClientUserResource($user)
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
     * Compute stats for client (similar to buyer profile stats)
     */
    private function computeClientStats(int $userId): array
    {
        $reviewStats = OrderReview::where('reviewed_user_id', $userId)
            ->selectRaw('
            ROUND(AVG(rating), 1)                   AS avg_rating,
            COUNT(*)                                AS total_reviews,
            ROUND(AVG(communication_rating), 1)     AS avg_communication,
            ROUND(AVG(service_rating), 1)           AS avg_service,
            ROUND(AVG(delivery_rating), 1)          AS avg_delivery
        ')
            ->first();

        $breakdown = OrderReview::where('reviewed_user_id', $userId)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $ratingBreakdown = [
            'five_star'  => $breakdown[5] ?? 0,
            'four_star'  => $breakdown[4] ?? 0,
            'three_star' => $breakdown[3] ?? 0,
            'two_star'   => $breakdown[2] ?? 0,
            'one_star'   => $breakdown[1] ?? 0,
        ];

        $recentReviews = OrderReview::where('reviewed_user_id', $userId)
            ->with(['reviewer.profile', 'order', 'gig'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($review) {
                return [
                    'id'         => $review->id,
                    'rating'     => $review->rating,
                    'content'    => $review->review,
                    'created_at' => $review->created_at->diffForHumans(),
                    'reviewer'   => [
                        'name'    => $review->reviewer->profile->first_name . ' ' . $review->reviewer->profile->last_name,
                        'avatar'  => $review->reviewer->profile->avatar
                            ? asset('storage/' . $review->reviewer->profile->avatar)
                            : asset('default/profile.jpg'),
                        'address' => $review->reviewer->profile->address,
                    ],
                    'order' => [
                        'price'    => $review->order->price ?? 0,
                        'duration' => isset($review->order->delivery_days)
                            ? (($review->order->delivery_days % 7 == 0)
                                ? ($review->order->delivery_days / 7) . ' ' . (($review->order->delivery_days / 7) > 1 ? 'weeks' : 'week')
                                : $review->order->delivery_days . ' ' . ($review->order->delivery_days > 1 ? 'days' : 'day'))
                            : null,
                    ],
                    'gig' => [
                        'id'    => $review->gig->id ?? null,
                        'title' => $review->gig->title ?? null,
                        'image' => $review->gig->primaryImage?->image_url
                            ? asset('storage/' . $review->gig->primaryImage->image_url)
                            : asset('default/no_image.webp'),
                    ],
                ];
            });

        // ─── Expert replies to client's reviews ──────────────────────────────
        // When a client gives a review to an expert, the expert replies to that review.
        // reviewer_id = client, reviewed_user_id = expert, seller_reply = expert's reply
        $receivedReplies = OrderReview::where('reviewer_id', $userId)
            ->whereNotNull('seller_reply')
            ->with(['reviewedUser.profile', 'order', 'gig']) // expert = reviewedUser
            ->latest('replied_at')
            ->limit(10)
            ->get()
            ->map(function ($review) {
                return [
                    'id'          => $review->id,
                    'my_review'   => [
                        'rating'  => $review->rating,
                        'content' => $review->review,
                    ],
                    'expert_reply' => [
                        'content'    => $review->seller_reply,
                        'replied_at' => $review->replied_at
                            ? $review->replied_at->diffForHumans()
                            : null,
                    ],
                    'expert' => [
                        'name'   => $review->reviewedUser->profile->first_name . ' ' . $review->reviewedUser->profile->last_name,
                        'avatar' => $review->reviewedUser->profile->avatar
                            ? asset('storage/' . $review->reviewedUser->profile->avatar)
                            : asset('default/profile.jpg'),
                    ],
                    'order' => [
                        'price'    => $review->order->price ?? 0,
                        'duration' => isset($review->order->delivery_days)
                            ? (($review->order->delivery_days % 7 == 0)
                                ? ($review->order->delivery_days / 7) . ' ' . (($review->order->delivery_days / 7) > 1 ? 'weeks' : 'week')
                                : $review->order->delivery_days . ' ' . ($review->order->delivery_days > 1 ? 'days' : 'day'))
                            : null,
                    ],
                    'gig' => [
                        'id'    => $review->gig->id ?? null,
                        'title' => $review->gig->title ?? null,
                        'image' => $review->gig->primaryImage?->image_url
                            ? asset('storage/' . $review->gig->primaryImage->image_url)
                            : asset('default/no_image.webp'),
                    ],
                ];
            });

        return [
            'avg_rating'           => $reviewStats->avg_rating ?? 0,
            'total_reviews'        => (int) ($reviewStats->total_reviews ?? 0),
            'avg_communication'    => $reviewStats->avg_communication ?? 0,
            'avg_service'          => $reviewStats->avg_service ?? 0,
            'avg_delivery'         => $reviewStats->avg_delivery ?? 0,
            'success_score'        => 0,
            'last_month_earnings'  => 0,
            'avg_response_minutes' => null,
            'last_month_label'     => now()->subMonth()->format('F'),
            'rating_breakdown'     => $ratingBreakdown,
            'recent_reviews'       => $recentReviews,
            'received_replies'     => $receivedReplies, // ← নতুন
        ];
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
