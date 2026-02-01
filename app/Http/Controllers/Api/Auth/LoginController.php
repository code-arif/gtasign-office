<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\Helper;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    use ApiResponse;

    public $select;
    public function __construct()
    {
        parent::__construct();
        $this->select = ['id', 'name', 'username', 'email', 'avatar', 'otp_verified_at', 'last_activity_at'];
    }

    /**
     * User Login
     */
    public function login(Request $request)
    {
        try {
            // Validate Request
            $validator = Validator::make($request->all(), [
                'email'    => 'required|email|exists:users,email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return $this->error($validator->errors(), 'Validation failed', 422);
            }

            // Find User
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            // Check Active Status
            if ($user->status !== 'active') {
                return $this->error(null, 'User is not active', 403);
            }

            // Check Password
            if (!Hash::check($request->password, $user->password)) {
                return $this->error(null, 'Invalid credentials', 422);
            }

            // Check Email Verification
            if (!$user->otp_verified_at) {
                return $this->error(
                    ['is_otp_verified' => false],
                    'Email not verified. Please verify your email before logging in.',
                    403
                );
            }

            // Clear OTP / Reset fields after verification
            $user->update([
                'otp'                              => null,
                'otp_expires_at'                   => null,
                'reset_password_token'             => null,
                'reset_password_token_expire_at'   => null,
                'last_activity_at'                 => now(),
            ]);

            // Generate Token
            $token = auth('api')->login($user);

            // Success Response
            return $this->success('Login successful', [
                'user'       => [
                    'id'          => $user->id,
                    'email'       => $user->email,
                    'username'    => $user->username,
                    'name'        => $user->name,
                    'first_name'  => $user->first_name,
                    'last_name'   => $user->last_name,
                    'avatar'      => $user->avatar,
                    'address'     => $user->address,
                    'status'      => $user->status,
                    'role'        => $user->role ?? null,
                    'biography'   => $user->biography,
                ],
                'token'      => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ]);
        } catch (Exception $e) {
            return $this->error(['error' => $e->getMessage()], 'An error occurred during login', 500);
        }
    }


    public function refreshToken()
    {
        $refreshToken = auth('api')->refresh();

        if (empty($refreshToken)) {
            return Helper::jsonErrorResponse('Failed to refresh the token.', 401);
        }

        return response()->json([
            'status'     => true,
            'message'    => 'Access token refreshed successfully.',
            'code'       => 200,
            'token_type' => 'bearer',
            'token'      => $refreshToken,
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'data' => auth('api')->user()
        ]);
    }
}
