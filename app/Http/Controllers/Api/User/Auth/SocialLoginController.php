<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    use ApiResponse;
    public function socialSignin(Request $request, $provider)
    {
        try {
            if (!in_array($provider, ['google', 'facebook', 'twitter', 'apple'])) {
                return $this->success([], 'Social provider not supported', 200);
            }

            $token = $request->input('token');
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Token is required'], 422);
            }

            $socialUser = Socialite::driver($provider)->stateless()->userFromToken($token);

        
            $userData = $this->extractUserData($socialUser, $provider);

           
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'f_name' => $userData['f_name'],
                    'l_name' => $userData['l_name'],
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'password' => bcrypt($socialUser->getId()),
                ]
            );

            $user->update([
                'is_social_logged' => true
            ]);

            Auth::login($user);
            $token = auth('api')->login($user);

           
            $response = [
                'id' => $user->id,
                'f_name' => $user->f_name,
                'l_name' => $user->l_name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
                'zip_code' => $user->zip_code ?? null,
                'dob' => $user->dob ?? null,
                'avatar' => $user->avatar,
                'provider' => $user->provider,
                'token' => $token,
            ];

            return $this->success($response, 'Successfully Logged In', 200);
        } catch (\Exception $e) {
            Log::error("Social Login Error ({$provider}): " . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }


    private function extractUserData($socialUser, $provider)
    {
        $email = $socialUser->getEmail();
        $fullName = $socialUser->getName();
        $firstName = null;
        $lastName = null;

        if ($provider === 'google') {
            $firstName = $socialUser->user['given_name'] ?? $fullName;
            $lastName = $socialUser->user['family_name'] ?? '';
        } elseif ($provider === 'facebook') {
            $firstName = $socialUser->user['first_name'] ?? $fullName;
            $lastName = $socialUser->user['last_name'] ?? '';
        } elseif ($provider === 'apple') {

            if (!isset($socialUser->user['name'])) {
                $nameParts = explode('@', $email);
                $firstName = $nameParts[0] ?? 'AppleUser';
                $lastName = '';
            } else {
                $firstName = $socialUser->user['name']['firstName'] ?? $fullName;
                $lastName = $socialUser->user['name']['lastName'] ?? '';
            }
        } elseif ($provider === 'twitter') {
            $nameParts = explode(' ', $fullName, 2);
            $firstName = $nameParts[0] ?? $fullName;
            $lastName = $nameParts[1] ?? '';
        }

        return [
            'email' => $email,
            'f_name' => $firstName,
            'l_name' => $lastName,
        ];
    }
}
