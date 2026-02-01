<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use Carbon\Carbon;
use App\Traits\SMS;
use App\Models\User;
use App\Mail\OtpMail;
use App\Helpers\Helper;
use App\Mail\WelcomeMail;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Events\RegistrationNotificationEvent;
use App\Notifications\RegistrationNotification;

class RegisterController extends Controller
{

    use SMS, ApiResponse;

    public $select;
    public function __construct()
    {
        parent::__construct();
        $this->select = ['id', 'first_name', 'last_name', 'username', 'email', 'otp', 'avatar', 'otp_verified_at', 'last_activity_at'];
    }

    /**
     * User Registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name'       => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'email'      => 'required|string|email|max:150|unique:users',
            'phone'      => 'required|string|max:150|unique:users',
            'address'    => 'required|string',
            'password'   => 'required|string|min:6|confirmed',
            'agree'      => 'required|in:true',
            'role'       => 'required',
            'biography' => 'nullable|string|max:2500',
        ]);
        try {
            DB::beginTransaction();
            do {
                $slug = "$request->first_name" . rand(1000000000, 9999999999);
            } while (User::where('slug', $slug)->exists());
            function randomAlphaNum($length = 4)
            {
                return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
            }

            $username = '@' . strtolower($request->input('first_name')) . '_' . randomAlphaNum(4);


            $user = User::create([
                'first_name'               => $request->input('first_name'),
                'last_name'                => $request->input('last_name'),
                'address'                  => $request->input('address'),
                'username'                 => $username,
                'slug'                     => $slug,
                'email'                    => strtolower($request->input('email')),
                'password'                 => Hash::make($request->input('password')),
                'otp'                      => rand(1000, 9999),
                'otp_expires_at'           => Carbon::now()->addMinutes(60),
                'status'                   => 'active',
                'last_activity_at'         => Carbon::now(),
                'biography'               => $request->input('biography'),
            ]);

            DB::table('model_has_roles')->insert([
                'role_id' => $request->role,
                'model_type' => 'App\Models\User',
                'model_id' => $user->id
            ]);

            //notify to admin start
            $notiData = [
                'user_id' => $user->id,
                'title' => 'User register in successfully.',
                'body' => 'User register in successfully.'
            ];

            $admins = User::role('admin', 'web')->get();
            foreach ($admins as $admin) {
                $admin->notify(new RegistrationNotification($notiData));
                if (config('settings.reverb')  === 'on') {
                    broadcast(new RegistrationNotificationEvent($notiData, $admin->id))->toOthers();
                }
            }

            $data = User::select('otp')->find($user->id);

            Mail::to($user->email)->queue(new OtpMail($user->otp, $user, 'Verify Your Email Address'));

            DB::commit();

            $token = auth('api')->login($user);

            $response = [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'avatar' => $user->avatar,
                'role' => $user->role,
                'biography' => $user->biography,
                // 'otp' => auth('api')->user()->otp,
            ];

            return $this->success(
                'User registered successfully. Please verify your email using the OTP sent to your email address.',
                $response,
                201
            );
        } catch (Exception $e) {
            DB::rollBack();
            return Helper::jsonErrorResponse('User registration failed', 500, [$e->getMessage()]);
        }
    }

    /**
     * Verify Email
     */
    public function VerifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:4',
        ]);

        try {
            $user = User::where('email', $request->input('email'))->first();

            // Already verified
            if (!empty($user->otp_verified_at)) {
                return response()->json([
                    "success" => false,
                    "message" => "Email already verified.",
                    "code"    => 409
                ], 409);
            }

            // Invalid OTP
            if ((string)$user->otp !== (string)$request->input('otp')) {
                return response()->json([
                    "success" => false,
                    "message" => "Invalid OTP code",
                    "code"    => 422
                ], 422);
            }

            // OTP expired
            if (Carbon::parse($user->otp_expires_at)->isPast()) {
                return response()->json([
                    "success" => false,
                    "message" => "OTP has expired. Please request a new OTP.",
                    "code"    => 422
                ], 422);
            }

            // Update verification
            $user->otp_verified_at = now();
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            // Send Welcome Email
            try {
                Mail::to($user->email)->send(new WelcomeMail($user));
            } catch (Exception $mailException) {
                // Log the mail error but don't fail the verification
                Log::error('Welcome email failed to send: ' . $mailException->getMessage());
            }

            // Generate token
            $token = auth('api')->login($user);
            $expires_in = auth('api')->factory()->getTTL() * 60; // usually minutes * 60

            return response()->json([
                "success" => true,
                "message" => "Email verified successfully.",
                "data" => [
                    "id"         => $user->id,
                    "email"      => $user->email,
                    "username"   => $user->username,
                    "first_name" => $user->first_name,
                    "last_name"  => $user->last_name,
                    "avatar"     => $user->avatar,
                    "address"    => $user->address,
                    "status"     => $user->status,
                    "role"       => $user->role,
                    "biography"  => $user->biography,
                ],
                'token'      => $token,
                'token_type' => 'bearer',
                'expires_in' => $expires_in,
                "code" => 200
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "code"    => $e->getCode() ?: 500
            ], 500);
        }
    }


    /**
     * Resend OTP
     */
    public function ResendOtp(Request $request)
    {

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $user = User::where('email', $request->input('email'))->first();

            if (!$user) {
                return Helper::jsonErrorResponse('User not found.', 404);
            }

            if ($user->otp_verified_at) {
                return Helper::jsonErrorResponse('Email already verified.', 409);
            }

            $newOtp               = rand(1000, 9999);
            $otpExpiresAt         = Carbon::now()->addMinutes(60);
            $user->otp            = $newOtp;
            $user->otp_expires_at = $otpExpiresAt;
            $user->save();

            //* Send the new OTP to the user's email
            Mail::to($user->email)->queue(new OtpMail($newOtp, $user, 'Verify Your Email Address'));

            return response()->json([
                'status'  => true,
                'message' => 'A new OTP has been sent to your email address.',
                'code'    => 200,
                'otp'     => $newOtp // Remove this line in production
            ], 200);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 200);
        }
    }
}
