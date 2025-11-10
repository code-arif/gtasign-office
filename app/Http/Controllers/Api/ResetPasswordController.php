<?php

namespace App\Http\Controllers\Api;


use Exception;
use App\Models\User;
use App\Mail\OtpMail;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class ResetPasswordController extends Controller
{
    use ApiResponse;

    public function forgotPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);


        if ($validator->fails()) {
            return $this->error(['validation failed'] , $validator->errors()->first(), 422);
        }

        try {
            $email = $request->input('email');
            $otp   = rand(1000, 9999);
            $user  = User::where('email', $email)->first();

            if ($user) {
                try {

                    Mail::to($email)->send(new OtpMail($otp, $user, 'Your OTP for Reset Password'));


                    $user->update([
                        'otp'            => $otp,
                        'otp_expires_at' => Carbon::now()->addMinutes(5),
                    ]);

                    $data = [
                        'email' => $user->email,
                        'otp' => $otp,

                    ];

                    return $this->success($data, 'OTP Code Sent Successfully. Please check your email.', 200);
                } catch (\Exception $e) {
                    return $this->error([], 'Failed to send OTP email. Please try again later.', 500);
                }
            }

            return $this->error([], 'Invalid email address.', 404);
        } catch (\Exception $e) {

            Log::error($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function VerifyOTP(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required',
        ]);


        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        try {

            $email = $request->input('email');
            $otp   = $request->input('otp');
            $user = User::where('email', $email)->first();

            if (!$user) {
                return $this->error(false, 'User not found', 404);
            }

            if (Carbon::parse($user->otp_expires_at)->isPast()) {

                return $this->error([], 'OTP has expaired', 400);
            }

            if ($user->otp !== $otp) {

                return $this->error([], 'Invalid OTP', 400);
            }

            $token = Str::random(60);

            $user->update([
                'otp'             => null,
                'otp_expires_at'  => null,
                'reset_password_token' => $token,
                'reset_password_token_expire_at' => Carbon::now()->addHour(),
            ]);

            return response()->json([
                'status'     => true,
                'message'    => 'OTP verified successfully.',
                'code'       => 200,
                'token'      => $token,
            ]);
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }


    public function ResetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'token'    => 'required|string',
            'password' => 'required|string|min:6',
        ]);


        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|exists:users,email',
            'token'    => 'required|string',
            'password' => 'required|string|min:6',
        ]);


        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }


        try {

            $email       = $request->input('email');
            $newPassword = $request->input('password');

            $user = User::where('email', $email)->first();
            if (!$user) {
                return $this->error(false, 'User not found', 404);
            }

            if (!empty($user->reset_password_token) && $user->reset_password_token === $request->token && $user->reset_password_token_expire_at >= Carbon::now()) {
                $user->update([
                    'password'        => Hash::make($newPassword),
                    'reset_password_token' => null,
                    'reset_password_token_expire_at' => null,
                ]);

                return $this->success(true, 'Password reset Successfully.', 200);
            } else {
                return $this->error(false, 'Invalid token or token expired', 401);
            }
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
