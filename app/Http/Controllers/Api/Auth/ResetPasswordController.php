<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Helpers\Helper;
use App\Mail\ForgotPassOTP;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    use ApiResponse;
    public $select;
    public function __construct()
    {
        parent::__construct();
        $this->select = ['id', 'name', 'email', 'avatar'];
    }

    /**
     * Send OTP to user email for password reset.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $email = $request->input('email');
            $otp   = rand(1000, 9999);
            $user  = User::where('email', $email)->first();

            if ($user) {
                // Send OTP Email
                Mail::to($email)->queue(new ForgotPassOTP($otp, $user, 'Reset Your Password - Whistle Works'));

                // Update user with new OTP
                $user->otp            = $otp;
                $user->otp_expires_at = Carbon::now()->addMinutes(60);
                $user->save();

                // Log the OTP request for security
                Log::info('Password reset OTP sent to ' . $email . ' at ' . now());

                $response = [
                    'email'  => $email,
                    'expires_at' => $user->otp_expires_at->format('Y-m-d H:i:s'),
                    'message' => 'OTP sent successfully. Please check your email.'
                ];

                return $this->success('OTP sent successfully', $response, 200);
            } else {
                return $this->error('User not found with this email address', 404);
            }
        } catch (Exception $e) {
            Log::error('Forgot password error: ' . $e->getMessage());
            return $this->error('Failed to send OTP. Please try again later.', 500);
        }
    }


    /**
     * Resend OTP to user email for password reset.
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'purpose' => 'nullable|string|in:registration,password_reset,verification,login',
        ]);

        try {
            $email = $request->input('email');
            $purpose = $request->input('purpose', 'verification');
            $user = User::where('email', $email)->first();

            if ($user) {
                // Generate new OTP
                $otp = rand(1000, 9999);
                $user->otp = $otp;
                $user->otp_expires_at = Carbon::now()->addMinutes(60);
                $user->save();

                // Determine email subject based on purpose
                $subject = $this->getOtpSubject($purpose);

                // Send OTP email using your existing OtpMail class
                Mail::to($email)->queue(new ForgotPassOTP($otp, $user, 'Reset Your Password - Whistle Works'));

                // Log the resend activity
                Log::info('OTP resent to ' . $email . ' for ' . $purpose . ' at ' . now());

                $response = [
                    'email' => $email,
                    'expires_at' => $user->otp_expires_at->format('Y-m-d H:i:s'),
                    'purpose' => $purpose,
                    'message' => 'OTP has been resent to your email address.'
                ];

                return $this->success('OTP resent successfully', $response, 200);
            } else {
                return $this->error('User not found with this email address', 404);
            }
        } catch (Exception $e) {
            Log::error('Resend OTP error: ' . $e->getMessage());
            return $this->error('Failed to resend OTP. Please try again later.', 500);
        }
    }

    /**
     * Get email subject based on OTP purpose
     */
    private function getOtpSubject($purpose)
    {
        $subjects = [
            'registration' => 'Verify Your Email Address - Whistle Works',
            'password_reset' => 'Reset Your Password - Whistle Works',
            'verification' => 'Verify Your Account - Whistle Works',
            'login' => 'Login Verification - Whistle Works',
        ];

        return $subjects[$purpose] ?? 'Your Verification Code - Whistle Works';
    }


    /**
     * Verify otp
     */
    public function MakeOtpToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:4',
        ]);

        try {
            $email = $request->input('email');
            $otp   = $request->input('otp');
            $user = User::where('email', $email)->first();

            if (!$user) {
                return Helper::jsonErrorResponse('User not found', 404);
            }

            if (Carbon::parse($user->otp_expires_at)->isPast()) {
                return Helper::jsonErrorResponse('OTP has expired.', 400);
            }

            if ($user->otp !== $otp) {
                return Helper::jsonErrorResponse('Invalid OTP', 400);
            }
            $token = Str::random(60);

            $user->otp = null;
            $user->otp_expires_at = null;
            $user->reset_password_token = $token;
            $user->reset_password_token_expire_at = Carbon::now()->addHour();

            $user->save();

            return response()->json([
                'status'     => true,
                'message'    => 'OTP verified successfully.',
                'code'       => 200,
                'token'      => $token,
            ]);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }


    /**
     * Set new password
     */
    public function ResetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'token'    => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);
        try {
            $email       = $request->input('email');
            $newPassword = $request->input('password');

            $user = User::where('email', $email)->first();
            if (!$user) {
                return Helper::jsonErrorResponse('User not found', 404);
            }

            if (!empty($user->reset_password_token) && $user->reset_password_token === $request->token && $user->reset_password_token_expire_at >= Carbon::now()) {

                $user->password = Hash::make($newPassword);
                $user->reset_password_token = null;
                $user->reset_password_token_expire_at = null;

                $user->save();

                return Helper::jsonResponse(true, 'Password reset successfully.', 200);
            } else {
                return Helper::jsonErrorResponse('Invalid Token', 419);
            }
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
