<?php

namespace App\Http\Controllers\Api\Business\Auth;

use Exception;
use App\Models\User;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\BusinessProfile;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BusinessAuthController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email'         => 'required|email|unique:users,email',
                'password'      => 'required|string|confirmed|min:8',
                'establishment_id' => 'required|integer|exists:establishments,id',
                'venue_name'    => 'required|string|max:255',
                'address'       => 'nullable|string|max:255',
                'phone'         => 'nullable|string|max:15',
                'cover'         =>  'nullable|image|mimes:jpeg,jpg,png,gif|max:5120',
                'open_hour'     => 'nullable|date_format:H:i',
                'close_hour'    => 'nullable|date_format:H:i',
                'menu'          => 'nullable|file|mimes:pdf,jpg,png,doc,docx,txt,sql|max:5120',
                'address'       => 'nullable|string',
                'latitude'      => 'nullable|numeric|between:-90,90',
                'longitude'     => 'nullable|numeric|between:-180,180',
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 200);
            }

            $validatedData = $validator->validated();

            $user = User::create([
                'email' =>  $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'phone' => $validatedData['phone'],
                'role' => 'business',
            ]);

            $businessProfile = BusinessProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'establishment_id' => $validatedData['establishment_id'],
                    'venue_name'       => $validatedData['venue_name'],
                    'open_hour'        => $validatedData['open_hour'],
                    'close_hour'       => $validatedData['close_hour'],
                    'address'          => $validatedData['address'],
                    'latitude'         => $validatedData['latitude'] ?? null,
                    'longitude'        => $validatedData['longitude'] ?? null,
                ]
            );

            if ($request->hasFile('cover')) {
                $coverPath = Helper::uploadImage($request->file('cover'), 'business_profiles');
                $businessProfile->cover = $coverPath;
                $businessProfile->save();
            }

            if ($request->hasFile('menu')) {
                $coverPath = Helper::uploadImage($request->file('menu'), 'business_menu');
                $businessProfile->menu = $coverPath;
                $businessProfile->save();
            }

            $token = auth('api')->login($user);

            $response = [
                'id'              => $user->id,
                'email'           => $user->email,
                'venue_name'      => $validatedData['venue_name'],
                'cover'           => $businessProfile->cover,
                'address'         => $validatedData['address'],
                'open_hour'       => $validatedData['open_hour'],
                'close_hour'      => $validatedData['close_hour'],
                'address'         => $validatedData['address'],
                'latitude'        => $validatedData['latitude'],
                'longitude'       => $validatedData['longitude'],
                'token'           => "This Accout need to approve by admin."
            ];

            return $this->success($response, 'Business profile registered successfully', 200);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->error($e->getMessage(), 'An Unknown Error occurred.', 500);
        }
    }

    public function login(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 200);
            }

            $data = $validator->validated();

            if (! $token = auth('api')->attempt($data)) {
                return $this->error([], 'Invalid email or password.', 401);
            }

            $user = auth('api')->user();


            if($user->business_profile->status == 'pending' || $user->business_profile->status == 'cancelled') {
                return $this->success([], 'Your account is not approved yet.', 200);
            }


            $data = [
                'id' => $user->id,
                'venue_name' => $user->business_profile->venue_name,
                'email' => $user->email,
                'token' => $token,
            ];

            return $this->success($data, 'Successfully Logged In', 200);
        } catch (Exception $e) {

            Log::error($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email'   => 'nullable|email|unique:users,email,' . auth()->id(),
                'establishment_id' => 'nullable|integer|exists:establishments,id',
                'venue_name' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:15',
                'cover' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120',
                'open_hour' => 'nullable|date_format:H:i',
                'close_hour' => 'nullable|date_format:H:i',
                'menu' => 'nullable|file|mimes:pdf,jpg,png,doc,docx,txt,sql|max:5120',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 200);
            }

            $validatedData = $validator->validated();
            $user = Auth::user();


            $user->update([
                'email' => $validatedData['email'] ?? $user->email,
                'address' => $validatedData['address'] ?? $user->address,
                'phone' => $validatedData['phone'] ?? $user->phone,
            ]);

            $businessProfile = BusinessProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'establishment_id' => $validatedData['establishment_id'],
                    'venue_name' => $validatedData['venue_name'],
                    'open_hour' => $validatedData['open_hour'],
                    'close_hour' => $validatedData['close_hour'],
                    'latitude' => $validatedData['latitude'],
                    'longitude' => $validatedData['longitude']
                ]
            );


            if ($request->hasFile('cover')) {
                $coverPath = Helper::uploadImage($request->file('cover'), 'business_profiles');
                $businessProfile->cover = $coverPath;
                $businessProfile->save();
            }

            if ($request->hasFile('menu')) {
                $menuPath = Helper::uploadImage($request->file('menu'), 'business_menu');
                $businessProfile->menu = $menuPath;
                $businessProfile->save();
            }


            $response = [
                'id' => $user->id,
                'email' => $user->email,
                'venue_name' => $validatedData['venue_name'],
                'cover' => $businessProfile->cover,
                'address' => $validatedData['address'],
                'phone' => $validatedData['phone'],
                'open_hour' => $validatedData['open_hour'],
                'close_hour' => $validatedData['close_hour'],
                'latitude' => $businessProfile->latitude,
                'longitude' => $businessProfile->longitude,
            ];

            return $this->success($response, 'Business profile updated successfully', 200);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->error($e->getMessage(), 'An error occurred.', 500);
        }
    }


    public function editMenu(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'menu' => 'required|file|mimes:pdf,jpg,png,doc,docx,txt,sql,json|max:5120',
            ]);

            if ($validator->fails()) {
                return $this->success([], $validator->errors()->first(), 200);
            }

            $businessProfile = BusinessProfile::where('user_id', Auth::id())->first();

            if (!$businessProfile) {
                return $this->success([], 'Business menu not found', 200);
            }

            if($businessProfile->menu) {
                Helper::deleteImage($businessProfile->menu);
            }

            if ($request->hasFile('menu')) {
                $menuPath = Helper::uploadImage($request->file('menu'), 'business_menu');
                $businessProfile->menu = $menuPath;
                $businessProfile->save();
            }

            $menu = url($businessProfile->menu);

            return $this->success(['menu' => $menu], 'Business menu updated successfully', 200);
        } catch (Exception $e) {

            Log::info($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
