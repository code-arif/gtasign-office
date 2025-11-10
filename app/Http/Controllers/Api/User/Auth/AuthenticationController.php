<?php
namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegisterOtpMail;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    use ApiResponse;

    public function register(Request $request)
    {
        DB::beginTransaction(); // transaction start

        try {
            $validator = Validator::make($request->all(), [
                'first_name' => ['required', 'string', 'max:255'],
                'last_name'  => ['required', 'string', 'max:255'],
                'email'      => ['required', 'string', 'email', 'unique:users', 'max:255'],
                'password'   => ['required', 'string', 'min:8'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 404);
            }

            $validatedData = $validator->validated();
            $otp           = rand(1000, 9999);
            $otpExpiresAt  = Carbon::now()->addMinutes(5);

            $user = User::create([
                'first_name'      => $validatedData['first_name'],
                'last_name'       => $validatedData['last_name'],
                'email'           => $validatedData['email'],
                'password'        => Hash::make($validatedData['password']),
                'otp'             => $otp,
                'otp_expires_at'  => $otpExpiresAt,
                'is_otp_verified' => false,
            ]);

            // Try to send mail
            Mail::to($user->email)->send(new RegisterOtpMail($otp, $user));

            // If everything is fine, commit
            DB::commit();

            return $this->success([
                'message' => 'OTP has been sent to your email. Please verify to complete registration.',
                'email'   => $user->email,
                'otp'     => $user->otp,
            ], 'OTP Sent', 201);
        } catch (Exception $e) {
            // Rollback if any error occurs
            DB::rollBack();

            Log::error('Registration Error: ' . $e->getMessage());
            return $this->error([], 'Registration failed: ' . $e->getMessage(), 500);
        }
    }

    public function RegistrationVerifyOtp(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email'],
            'otp'   => ['required', 'digits:4'],
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 400);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->error([], 'User not found', 200);
        }

        if ($user->otp !== $request->otp) {
            return $this->error([], 'Your OTP is Invalid.', 403);
        }

        if (! $user->otp_expires_at || Carbon::now()->gt($user->otp_expires_at)) {
            return $this->error([], 'OTP has expired');
        }

        $user->update([
            'email_verified_at' => Carbon::now(),
            'is_otp_verified'   => true,
            'otp'               => null,
            'otp_expires_at'    => null,
        ]);

        $token = auth('api')->login($user);

        $userData = [

            'id'         => $user['id'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'email'      => $user['email'],
            'token'      => $token,
        ];

        return $this->success($userData, 'User Registration successful.', 200);
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email'    => ['required', 'string', 'email', 'max:255', 'exists:users,email'],
                'password' => ['required', 'string', 'min:8'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $data = $validator->validated();
            $user = User::where('email', $data['email'])->first();

            if (! $user->is_otp_verified) {
                return $this->error([], 'Please verify your email with the OTP before logging in.', 403);
            }

            if (! $token = auth('api')->attempt($data)) {
                return $this->error([], 'Invalid email or password.', 401);
            }

            $userData = [
                'id'         => $user->id,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'email'      => $user->email,
                'role'       => $user->role ?? null,
                'token'      => $token,
            ];

            return $this->success($userData, 'Successfully Logged In', 200);
        } catch (Exception $e) {
            Log::error('Login Error: ' . $e->getMessage());
            return $this->error([], 'Something went wrong during login. Please try again later.', 500);
        }
    }

    public function updateRole(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (! $user) {
                return $this->error([], 'User not found .', 400);
            }

            if ($user->role !== null) {
                return $this->error([], 'You have already updated your role. Role cannot be changed again.', 400);
            }

            // update user role
            $user->update(['role' => $request->role]);

            // Prepare response data
            $userData = [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'phone_number'  => $user->phone_number,
                'date_of_birth' => $user->date_of_birth,
                'role'          => $user->role,

                'created_at'    => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at'    => $user->updated_at->format('Y-m-d H:i:s'),
            ];

            return $this->success($userData, 'User role updated successfully.', 200);
        } catch (Exception $e) {
            Log::error('Role update error: ' . $e->getMessage());
            return $this->error([], 'An error occurred while updating the role.', 500);
        }
    }

    public function logout()
    {
        try {
            auth('api')->logout();
            return $this->success([], 'Successfully logged out.', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
