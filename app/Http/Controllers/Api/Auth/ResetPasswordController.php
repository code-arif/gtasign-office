<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPassOTP;
use App\Models\User;
use App\Models\UserSecurityToken;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    use ApiResponse;
    public $select; // select all field

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

            $existingOtp = UserSecurityToken::where('user_id', $user->id)
                ->where('type', 'password_reset')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if ($existingOtp) {

                $resendAvailableAt = $existingOtp->created_at->addSeconds(150); // 2.5 min

                if (now()->lessThan($resendAvailableAt)) {
                    return $this->error(
                        'OTP already sent. Please wait before requesting again.',
                        null,
                        429
                    );
                }

                // Cooldown passed → invalidate old OTP
                $existingOtp->update([
                    'used_at' => now(),
                ]);
            }

            $otp = rand(100000, 999999); // 6-digit OTP

            UserSecurityToken::create([
                'user_id'    => $user->id,
                'identifier' => $user->email,
                'token_hash' => Hash::make($otp),
                'type'       => 'password_reset',
                'expires_at' => now()->addMinutes(60),
            ]);

            Log::info('Sending Password Reset OTP to: ' . $user->email);
            
            Mail::to($user->email)->send(new ForgotPassOTP($otp, $user, 'Reset Your Password - SecAAX'));

            Log::info('Password Reset OTP sent successfully to: ' . $user->email);

            return $this->success(
                'OTP sent successfully.',
                [
                    // 'otp' => $otp,
                    'email' => $user->email,
                    // 'expires_at' => now()->addMinutes(60)->format('Y-m-d H:i:s')
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
            'otp'   => 'required|digits:6',
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
