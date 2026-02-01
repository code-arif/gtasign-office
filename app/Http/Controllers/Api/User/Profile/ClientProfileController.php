<?php

namespace App\Http\Controllers\Api\User\Profile;

use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Models\BusinessProfile;
use App\Http\Controllers\Controller;
use App\Http\Resources\LangResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class ClientProfileController extends Controller
{
    use ApiResponse;

    private User $user;

    public function __construct()
    {
        $this->user = auth('api')->user();
    }

    public function profile()
    {
        $user = $this->user;
        $profile = $user->profile;
        $data = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'username' => $profile->username,
            'image' => url($profile->image),
            'tag_line' => $profile->tag_line,
            'description' => $profile->description,
            'language' => LangResource::collection($profile->languages),
        ];

        return $this->success($data, 'Profile data fetch successfully');
    }
    public function profileUpdate(Request $request)
    {
        $user = $this->user;
        $profile = $user->profile;

        DB::transaction(function () use ($request, $profile, $user) {

            $profileData = [];

            if ($request->filled('username')) {
                $profileData['username'] = $request->username;
            }

            if ($request->filled('tag_line')) {
                $profileData['tag_line'] = $request->tag_line;
            }

            if ($request->filled('description')) {
                $profileData['description'] = $request->description;
            }

            if (!empty($profileData)) {
                $profile->update($profileData);
            }


            if ($request->hasFile('image')) {

                // Optional: delete old image
                if ($profile->image) {
                    Helper::deleteImage($profile->image);
                }
                $path = Helper::uploadImage($request->image, 'profile');

                $profile->update([
                    'image' => $path,
                ]);
            }

            if ($request->has('languages')) {
                $languages = $request->languages;

                if (is_array($languages)) {
                    $languageIds = collect($languages)->map(fn($id) => (int) $id)->toArray();
                    $profile->languages()->sync($languageIds);
                }
            }
        });

        return $this->success($request->all(), 'Profile updated successfully');
    }
}
