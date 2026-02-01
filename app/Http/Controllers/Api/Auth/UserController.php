<?php

namespace App\Http\Controllers\Api\Auth;

use Stripe\Stripe;
use Stripe\Account;
use App\Models\User;
use App\Models\Artist;
use App\Helpers\Helper;
use App\Models\Festival;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\MyalbumResource;
use App\Http\Resources\AllAlbumResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\AlbumForUserResource;

class UserController extends Controller
{
    use ApiResponse;

    public $select;

    public function __construct()
    {
        parent::__construct();
        $this->select = ['id', 'first_name', 'last_name', 'username', 'address', 'slug',  'email', 'avatar'];
    }

    /**
     * Get User Details
     */
    public function me()
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->error('User not found', 404);
        }

        // Base response
        $response = [
            'id'         => $user->id,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'username'   => $user->username,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'address'    => $user->address,
            'biography'  => $user->biography,
            'avatar'     => $user->avatar
                ? asset($user->avatar)
                : asset('default/profile.jpg'),
            'slug'       => $user->slug,
            'role'       => $user->role,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];

        // Extra data only for referee
        if ($user->role === 'referee') {

            $avgScore10 = $user->evaluations()
                ->where('status', 'submitted')
                ->whereNotNull('average_score')
                ->avg('average_score'); // 1–10 scale

            $rating5 = $avgScore10
                ? round($avgScore10 / 2, 1) // convert to 5 scale
                : 0;

            $response['referee'] = [
                'checkin_camp' => $user->refereeCheckins()->count(),
                'total_game'   => $user->evaluations()
                    ->where('status', 'submitted')
                    ->count(),
                'rating'       => $rating5,
            ];
        }

        return $this->success(
            'User details fetched successfully',
            $response,
            200
        );
    }


    /**
     * Update User Profile
     */
    public function updateProfile(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'biography'  => 'nullable|string|max:2500',
            'phone'      => 'required|string|max:150|unique:users,phone,' . auth('api')->id(),
            'address'    => 'required|string',
        ]);

        $user = auth('api')->user();

        /**
         * Username generator:
         * username will be generated only if username is empty
         */
        if (!$user->username) {
            $generated = strtolower(($validatedData['first_name'] ?? 'user')) . '_' . $this->randomAlphaNum(4);
            $validatedData['username'] = $generated;
        }

        $user->update($validatedData);

        $response = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'biography' => $user->biography,
            'avatar' => $user->avatar
                ? asset($user->avatar)
                : asset('default/profile.jpg'),
            'slug' => $user->slug,
            'role' => $user->role,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];

        return Helper::jsonResponse(true, 'Profile updated successfully', 200, $response);
    }

    /**
     * Generate random alphanumeric string
     */
    private function randomAlphaNum($length = 4)
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
    }

    /**
     * Update User Avatar
     */
    public function updateAvatar(Request $request)
    {
        $validatedData = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);
        $user = auth('api')->user();
        if (!empty($user->avatar)) {
            Helper::fileDelete(public_path($user->getRawOriginal('avatar')));
        }
        $validatedData['avatar'] = Helper::fileUpload($request->file('avatar'), 'user/avatar', getFileName($request->file('avatar')));

        $user->update($validatedData);

        $response = [
            'id' => $user->id,
            'avatar' => $user->avatar
                ? asset($user->avatar)
                : asset('default/profile.jpg'),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];

        return Helper::jsonResponse(true, 'Avatar updated successfully', 200, $response);
    }

    /**
     * Delete User Profile
     */
    public function destroy()
    {
        $user = User::findOrFail(auth('api')->id());
        if (!empty($user->avatar) && file_exists(public_path($user->avatar))) {
            Helper::fileDelete(public_path($user->avatar));
        }
        Auth::logout('api');
        $user->forceDelete();
        return $this->success('User profile deleted successfully', [], 200);
    }


    /**
     * Change User Password
     */
    public function changePassword(Request $request)
    {
        $user = auth()->guard('api')->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'old_password'      => 'required',
            'new_password'      => 'required|min:6',
            'confirm_password'  => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        // Check if old password is correct
        if (!Hash::check($request->old_password, $user->password)) {
            return $this->error([], 'Old password does not match', 400);
        }

        // Update with new password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->success('Password changed successfully', [], 200);
    }
}
