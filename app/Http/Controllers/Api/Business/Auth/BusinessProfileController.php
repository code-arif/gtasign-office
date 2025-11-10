<?php

namespace App\Http\Controllers\Api\Business\Auth;

use Exception;
use App\Models\User;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BusinessProfileController extends Controller
{
    use ApiResponse;
    public function getProfile()
    {

        try {
            $data = User::select('id', 'email')->with('business_profile')->where('id', Auth::id())->first();

            if (!$data) {
                return $this->success([], 'Business Profile not found', 200);
            }

            $response = [
                'id' => $data->id,
                'email' => $data->email,
                'venue_name' => $data->business_profile->venue_name,
                'cover'   => $data->business_profile->cover,
                'address' => $data->address,
                'phone'   => $data->phone,
                'open_hour' => $data->business_profile->open_hour,
                'close_hour' => $data->business_profile->close_close
            ];

            return $this->success($response, 'Business Profile fetched successfully', 200);
        } catch (\Exception $e) {

            Log::info($e->getMessage());
            return $this->error($e->getMessage(), 'Error while fetching business profile', 500);
        }
    }


    public function updatePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => ['required', 'string'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $user = auth('api')->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return $this->error([], 'Current password is incorrect.', 403);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            return $this->success(true, 'Password updated successfully.', 200);
        } catch (Exception $e) {
            Log::error('Password update failed: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }


    public function deleteBusinessProfile()
    {
        $user = User::with('business_profile')->find(auth('api')->id());

        if (!$user) {
            return $this->success([], 'User Profile not found', 200);
        }

        if ($user->business_profile && $user->business_profile->cover) {
            Helper::deleteImage($user->business_profile->cover);
        }

        if ($user->business_profile && $user->business_profile->menu) {
            Helper::deleteImage($user->business_profile->menu);
        }

        $user->business_profile()->delete();
        $user->delete();

        return $this->success(true, 'Profile deleted successfully', 200);
    }


    public function logout()
    {
        try {
            if (Auth::check('api')) {
                Auth::logout('api');
                return $this->success('Successfully loged out.', 200);
            } else {
                return $this->error([false], 'User not Authenticated.', 401);
            }
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
