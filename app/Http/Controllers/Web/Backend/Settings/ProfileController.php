<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = User::find($request->id);
        return view('backend.layouts.settings.profile_settings', compact('user'));
    }

    /**
     * Update user email and name
     */
    public function UpdateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|max:100|min:2',
            'last_name'  => 'nullable|max:100|min:2',
            'email'      => 'nullable|email|unique:users,email,' . auth()->id(),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $user = User::with('profile')->findOrFail(auth()->id());

            // Update users table
            if ($request->filled('email')) {
                $user->email = $request->email;
                $user->save();
            }

            // Update profile table
            if ($user->profile) {
                $user->profile->update([
                    'first_name' => $request->first_name,
                    'last_name'  => $request->last_name,
                ]);
            }

            session()->put('t-success', 'Profile updated successfully');
        } catch (Exception $e) {
            session()->put('t-error', 'Something went wrong');
        }

        return redirect()->back();
    }


    /**
     * Update admin password
     */
    public function UpdatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password'     => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            $user = Auth::user();
            if (Hash::check($request->old_password, $user->password)) {
                $user->password = Hash::make($request->password);
                $user->save();

                return redirect()->back()->with('t-success', 'Password updated successfully');
            } else {
                return redirect()->back()->with('t-error', 'Current password is incorrect');
            }
        } catch (Exception) {
            return redirect()->back()->with('t-error', 'Something went wrong');
        }
    }

    /**
     * Update admin profile image
     */
    public function UpdateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        try {
            $user  = Auth::user();
            $image = $request->file('profile_picture');

            $imageName = time() . '.' . $image->getClientOriginalExtension();

            // Delete old image
            if ($user->profile && $user->profile->avatar && file_exists(public_path($user->profile->avatar))) {
                Helper::fileDelete(public_path($user->profile->avatar));
            }

            $imagePath = Helper::fileUpload($image, 'profile', $imageName);

            if (!$imagePath) {
                throw new Exception('Failed to upload image.');
            }

            // Save into profiles table
            $user->profile->update([
                'avatar' => $imagePath
            ]);

            return response()->json([
                'success'   => true,
                'message'   => 'Profile picture updated successfully.',
                'image_url' => asset($imagePath),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
