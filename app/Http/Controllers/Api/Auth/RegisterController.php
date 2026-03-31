<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use App\Models\User;
use App\Mail\OtpMail;
use App\Helpers\Helper;
use App\Models\Profile;
use App\Mail\WelcomeMail;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\UserSecurityToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Notifications\RegistrationNotification;

class RegisterController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user
     * Flow:
     * 1. Validate input
     * 2. Create user + profile
     * 3. Assign role
     * 4. Generate OTP token
     * 5. Notify admins
     * 6. Send OTP email
     */
    public function register(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'required|string|email|max:150|unique:users',
            'password'   => 'required|string|min:6|confirmed',
            'agree'      => 'required',
            'role'       => 'required|in:2,3', // 2=expert, 3=client
        ]);

        if ($validator->fails()) {
            return $this->validationError(
                $validator->errors()->toArray(),
                'Validation failed',
                422
            );
        }

        DB::beginTransaction();

        try {
            // Create User
            $user = User::create([
                'email' => strtolower($request->email),
                'password' => Hash::make($request->password),
                'is_agreed' => true,
                'status' => 'active',
            ]);

            // Generate unique username and slug
            $slug = Helper::generateSlug($request->first_name);
            $username = Helper::generateUsername($request->first_name);

            // Create Profile
            Profile::create([
                'user_id'    => $user->id,
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'username'   => $username,
                'slug'       => $slug,
            ]);

            // Assign role
            DB::table('model_has_roles')->insert([
                'role_id'    => $request->role,
                'model_type' => User::class,
                'model_id'   => $user->id,
            ]);

            // Create OTP token for email verification
            $otp = rand(100000, 999999); // 6-digit OTP
            UserSecurityToken::create([
                'user_id' => $user->id,
                'identifier' => $user->email,
                'token_hash' => Hash::make($otp),
                'type' => 'email_verification',
                'expires_at' => now()->addMinutes(60),
            ]);

            // Notify admins (optional)
            // $admins = User::role('admin', 'web')->get();
            // foreach ($admins as $admin) {
            //     $admin->notify(new RegistrationNotification([
            //         'user_id' => $user->id,
            //         'title'   => 'New user registered',
            //         'body'    => 'A new user has registered successfully.'
            //     ]));
            // }

            // // Send OTP email
            Mail::to($user->email)->send(new OtpMail($otp, $user, 'Verify Your Email Address'));

            DB::commit();

            return $this->success(
                'User registered successfully. Please verify your email using the OTP sent.',
                [
                    'email' => $user->email,
                    'username' => $username,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    // 'otp' => $otp,
                    'is_agreed' => $user->is_agreed
                ],
                201
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error('User registration failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify email OTP
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:6',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            // Already verified
            if ($user->email_verified_at) {
                return $this->error('Email already verified', null, 409);
            }

            // Fetch OTP token
            $token = UserSecurityToken::where('user_id', $user->id)
                ->where('type', 'email_verification')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if (!$token || !Hash::check($request->otp, $token->token_hash)) {
                return $this->error('Invalid or expired OTP', null, 422);
            }

            // Mark token used
            $token->used_at = now();
            $token->save();

            // Update user verification
            $user->email_verified_at = now();
            $user->save();

            // Send Welcome Email
            try {
                Mail::to($user->email)->send(new WelcomeMail($user));
            } catch (Exception $mailEx) {
                // Log mail error silently
                Log::error('Welcome email failed: ' . $mailEx->getMessage());
            }

            // Generate JWT token
            $tokenJwt = auth('api')->login($user);
            $expires_in = auth('api')->factory()->getTTL() * 60;

            return $this->success(
                'Email verified successfully.',
                [
                    'user' => $user->only(['id', 'email', 'username', 'first_name', 'last_name']),
                    'token' => $tokenJwt,
                    'token_type' => 'bearer',
                    'expires_in' => $expires_in
                ]
            );
        } catch (Exception $e) {
            return $this->error('Verification failed', ['exception' => $e->getMessage()], 500);
        }
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if ($user->email_verified_at) {
                return $this->error('Email already verified', null, 409);
            }

            $otp = rand(100000, 999999); // 6-digit OTP

            UserSecurityToken::create([
                'user_id' => $user->id,
                'identifier' => $user->email,
                'token_hash' => Hash::make($otp),
                'type' => 'email_verification',
                'expires_at' => now()->addSeconds(150),
            ]);

            Mail::to($user->email)->send(new OtpMail($otp, $user, 'Verify Your Email Address'));

            return $this->success(
                'A new OTP has been sent to your email.',
                [
                    // 'otp' => $otp
                ],
                201
            );
        } catch (Exception $e) {
            return $this->error('OTP resend failed', ['exception' => $e->getMessage()], 500);
        }
    }
}
