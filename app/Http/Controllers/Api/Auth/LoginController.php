<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\UserSecurityToken;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    use ApiResponse;

    /**
     * User Login
     * Flow:
     * 1. Validate input
     * 2. Verify user existence and status
     * 3. Verify password
     * 4. Verify email verification
     * 5. Update last activity / clear temporary fields
     * 6. Generate JWT token
     */
    public function login(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            // Fetch user WITH profile relationship
            $user = User::with('profile')
                ->where('email', strtolower($request->email))
                ->first();

            // Check if user exists
            if (!$user) {
                return $this->error(null, 'Invalid credentials', 422);
            }

            // Check if user is active
            if ($user->status !== 'active') {
                return $this->error(null, 'User account is not active', 403);
            }

            // Check password
            if (!Hash::check($request->password, $user->password)) {
                return $this->error(null, 'Invalid credentials', 422);
            }

            // Check email verification (optional, uncomment if needed)
            if (is_null($user->email_verified_at)) {
                return $this->error(null, 'Please verify your email first', 403);
            }

            // Generate JWT token
            $token = auth('api')->login($user);
            $expiresIn = auth('api')->factory()->getTTL() * 60;

            // Success response
            return $this->success(
                'Login successful',
                [
                    'user'       => new UserResource($user),
                    'token'      => $token,
                    'token_type' => 'bearer',
                    'expires_in' => $expiresIn,
                ]
            );
        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Login failed', 500);
        }
    }

    /**
     * Refresh JWT token
     * Allows client to get a new access token using current valid token
     */
    public function refreshToken()
    {
        try {
            $refreshToken = auth('api')->refresh();
            $expiresIn = auth('api')->factory()->getTTL() * 60;

            // Load profile relationship
            $user = auth('api')->user()->load('profile');

            return $this->success(
                'Access token refreshed successfully',
                [
                    'user'       => new UserResource($user),
                    'token'      => $refreshToken,
                    'token_type' => 'bearer',
                    'expires_in' => $expiresIn,
                ]
            );
        } catch (Exception $e) {
            Log::error('Token refresh error: ' . $e->getMessage());

            // Correct order: message first, then errors
            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to refresh token',
                401
            );
        }
    }
}
