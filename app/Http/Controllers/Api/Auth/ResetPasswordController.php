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
use App\Models\UserSecurityToken;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    use ApiResponse;
    public $select;

    /**
     * Send OTP to user email for password reset.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            // Check existing active OTP
            $existingOtp = UserSecurityToken::where('user_id', $user->id)
                ->where('type', 'password_reset')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if ($existingOtp) {
                return $this->error(
                    'An OTP has already been sent. Please check your email or try again later.',
                    null,
                    429
                );
            }

            $otp = rand(1000, 9999);

            UserSecurityToken::create([
                'user_id'    => $user->id,
                'identifier' => $user->email,
                'token_hash' => Hash::make($otp),
                'type'       => 'password_reset',
                'expires_at' => now()->addMinutes(60),
            ]);

            // Mail::to($user->email)
            //     ->queue(new OtpMail($otp, $user, 'Reset Your Password - SecAAX'));

            return $this->success(
                'OTP sent successfully.',
                [
                    'otp' => $otp,
                    'email' => $user->email,
                    'expires_at' => now()->addMinutes(60)->format('Y-m-d H:i:s')
                ]
            );
        } catch (Exception $e) {
            Log::error('Password reset OTP failed: ' . $e->getMessage());
            return $this->error('Failed to send OTP', null, 500);
        }
    }

    /**
     * Verify otp
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:4',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            $token = UserSecurityToken::where('user_id', $user->id)
                ->where('type', 'password_reset')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if (!$token || !Hash::check($request->otp, $token->token_hash)) {
                return $this->error('Invalid or expired OTP', null, 422);
            }

            // Mark OTP used
            $token->used_at = now();
            $token->save();

            // Create reset token
            $resetToken = Str::random(64);

            UserSecurityToken::create([
                'user_id'    => $user->id,
                'identifier' => $user->email,
                'token_hash' => Hash::make($resetToken),
                'type'       => 'password_reset',
                'expires_at' => now()->addHour(),
            ]);

            return $this->success(
                'OTP verified successfully.',
                [
                    'reset_token' => $resetToken,
                    'expires_at'  => now()->addHour()->format('Y-m-d H:i:s')
                ]
            );
        } catch (Exception $e) {
            return $this->error('OTP verification failed', null, 500);
        }
    }

    /**
     * Set new password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'token'    => 'required|string',
            'password' => 'required|min:6|confirmed',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            $token = UserSecurityToken::where('user_id', $user->id)
                ->where('type', 'password_reset')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if (!$token || !Hash::check($request->token, $token->token_hash)) {
                return $this->error('Invalid or expired reset token', null, 419);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            $token->used_at = now();
            $token->save();

            return $this->success('Password reset successfully.');
        } catch (Exception $e) {
            return $this->error('Password reset failed', null, 500);
        }
    }
}
