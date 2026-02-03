<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;
use App\Models\FirebaseTokens;
use Exception;

class LogoutController extends Controller
{
    use ApiResponse;

    /**
     * Logout user
     * Invalidates the current JWT token
     */
    public function logout()
    {
        try {
            auth('api')->logout();
            return $this->success('Logged out successfully', null, 200);
        } catch (Exception $e) {
            return $this->error(['exception' => $e->getMessage()], 'Logout failed', 500);
        }
    }
}
