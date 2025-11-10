<?php
namespace App\Http\Controllers\Api\User\Auth;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    use ApiResponse;

    public function updateProfile(Request $request)
    {
        // dd($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'name'                 => ['nullable', 'string', 'max:255'],
                'avatar'               => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],

                'date_of_birth'        => ['nullable', 'date'],
                'location'             => ['nullable', 'string'],
                'company_name'         => ['nullable', 'string'],
                'company_bio'          => ['nullable', 'string'],
                'company_display_name' => ['nullable', 'string'],
                'website_url'          => ['nullable', 'string'],
                'size'                 => ['nullable', 'string'],
                'past_project'         => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                return $this->error($validator->errors(), 'Validation Failed', 422);
            }

            $user = auth('api')->user();
            if (! $user) {
                return $this->error([], 'User not authenticated.', 401);
            }

            // Handle user avatar
            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    Helper::deleteImage($user->avatar);
                }
                $avatar = Helper::uploadImage($request->file('avatar'), 'profile');

            }

            // Update user fields
            $user->name          = $request->input('name', $user->name);
            $user->date_of_birth = $request->input('date_of_birth', $user->date_of_birth);
            $user->avatar        = $avatar ?? $user->avatar;
            $user->save();

            // Update or create company
            $company = Company::where('user_id', $user->id)->first();

            $company->location     = $request->location ?? $company->location;
            $company->name         = $request->company_name ?? $company->name;

            $company->bio          = $request->company_bio ??  $company->bio;
            $company->display_name = $request->company_display_name ?? $company->display_name;
            $company->website_url  = $request->website_url ?? $company->website_url;
            $company->size         = $request->size ?? $company->size;
            $company->past_project = $request->past_project ?? $company->past_project;
            $company->image_url    = $avatar ?? $company->image_url;

            $company->save();

            // Response data
            $userData = [
                'id'                   => $user->id,
                'name'                 => $user->name,
                'email'                => $user->email,
                'avatar'               => $user->company ? $user->company->image_url : $user->avatar,
                'date_of_birth'        => $user->date_of_birth,
                'location'             => $company->location,
                'company_name'         => $company->name,
                'company_bio'          => $company->bio,
                'company_display_name' => $company->display_name,
                'website_url'          => $company->website_url,
                'size'                 => $company->size,
                'past_project'         => $company->past_project,

            ];

            return $this->success($userData, 'Profile updated successfully.', 200);

        } catch (\Exception $e) {
            Log::error('Profile update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error([], 'An unexpected error occurred. Please try again.', 500);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => ['required', 'string'],
                'password'         => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $user = auth('api')->user();

            if (! Hash::check($request->current_password, $user->password)) {
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

    public function updateAvatar(Request $request)
    {
        try {
            $request->validate([
                'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            ]);

            $user = Auth::user();

            if ($user->avatar) {
                Helper::deleteImage($user->avatar);
            }

            if ($request->hasFile('avatar')) {
                $image        = $request->file('avatar');
                $imagePath    = Helper::uploadImage($image, 'profile');
                $user->avatar = $imagePath;
            }

            $user->save();

            $updatedUser = User::select('id', 'avatar')->find(auth('api')->id());

            return $this->success($updatedUser, 'Avatar updated successfully.', 200);
        } catch (Exception $e) {

            Log::error('Avatar update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error([], 'An unexpected error occurred. Please try again.', 500);
        }
    }

    public function me()
    {
        try {
            $user = auth('api')->user();

            if (! $user) {
                return $this->error([], 'User not found.', 404);
            }
            // company

            $company = Company::where('user_id', $user->id)->first();

            // if (! $company) {
            //     return $this->error([], 'Company not found.', 404);
            // }

            $userData = [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'avatar'        => $user->company ? $user->company->image_url : $user->avatar,
                'date_of_birth' => $user->date_of_birth ?? null,
                'location'      => $company->location ?? null,
            ];

            return $this->success($userData, 'User Profile Retrived successfull', 200);
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function deleteProfile()
    {
        $user = User::find(auth('api')->id());

        if (! $user) {
            return $this->success([], 'User Profile not found', 200);
        }

        if ($user->avatar) {
            Helper::deleteImage($user->avatar);
        }

        $user->delete();
        return $this->success(true, 'Profile deleted successfully', 200);
    }

    public function employee_profile()
    {
        try {
            $user = auth('api')->user();

            if (! $user) {
                return $this->error([], 'User not found.', 404);
            }
            // company

            $employee = $user->employee;

            $company = $user->company;

            $userData = [

                'id'                      => $user->id,
                'name'                    => $user->name,
                'email'                   => $user->email,
                'avatar'                  => $user->employee ? $user->employee->image_url : $user->avatar,
                'date_of_birth'           => $user->date_of_birth ?? null,
                'location'                => $employee && $employee->location ? $employee->location : null,
                'bio'                     => $employee && $employee->bio ? $employee->bio : null,

                // Employee presence flags
                'employee_profile'        => $employee ? true : false,
                'employee_location'       => $employee && $employee->location ? true : false,
                'employee_specialize'     => $employee && ! $employee->specializations->isEmpty() ? true : false,
                'employee_job_categories' => $employee && ! $employee->employee_job_categories->isEmpty() ? true : false,

                // Company presence flags
                'company_image'           => $company && $company->image_url ? true : false,
                'company_specialize'      => $company && $company->company_specializes ? true : false,

            ];

            return $this->success($userData, 'Employee Profile Retrived successfull', 200);

        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
//
    // employee profile update
    public function employee_profile_update(Request $request)
    {
        // try {
        $validator = Validator::make($request->all(), [
            'name'          => ['nullable', 'string', 'max:255'],
            'avatar'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            'date_of_birth' => ['nullable', 'date'],
            'location'      => ['nullable', 'string'],
            'bio'           => ['nullable', 'string'],
            'phone_number'  => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation Failed', 422);
        }

        $user = auth('api')->user();
        if (! $user) {
            return $this->error([], 'User not authenticated.', 401);
        }

        $employee = Employee::where('user_id', $user->id)->first();

        // Handle avatar upload and deletion
        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');

            if ($user->avatar) {
                Helper::deleteImage($user->avatar);
            }

            if ($employee && $employee->image_url) {
                Helper::deleteImage($employee->image_url);
            }

            $uploadedPath = Helper::uploadImage($image, 'profile');

            // Save avatar for user and employee
            $user->avatar = $uploadedPath;
            if ($employee) {
                $employee->image_url = $uploadedPath;
            }
        }

        // Update user fields
        $user->name          = $request->input('name', $user->name);
        $user->phone_number  = $request->input('phone_number', $user->phone_number);
        $user->date_of_birth = $request->input('date_of_birth', $user->date_of_birth);

        // Calculate age if DOB was updated
        if ($request->filled('date_of_birth')) {
            $user->age = Helper::calculateAge($request->date_of_birth);
        }

        $user->save();

        // Update employee fields
        if ($employee) {
            $employee->location = $request->location ? $request->location : $employee->location;
            $employee->bio      = $request->bio ? $request->bio : $employee->bio;
            $employee->save();
        }

        // Prepare response
        $userData = [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'age'           => $user->age,
            'avatar'        => $user->employee ? $user->employee->image_url : $user->avatar,
            'date_of_birth' => $user->date_of_birth,
            'location'      => $employee->location ?? null,
            'bio'           => $employee->bio ?? null,
            'phone_number'  => $user->phone_number,
        ];

        return $this->success($userData, 'Employee Profile updated successfully.', 200);

        // } catch (\Exception $e) {
        //     Log::error('Profile update failed: ' . $e->getMessage(), [
        //         'trace' => $e->getTraceAsString(),
        //     ]);

        //     return $this->error([], 'An unexpected error occurred. Please try again.', 500);
        // }
    }

}
