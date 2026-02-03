<?php

namespace App\Http\Controllers\Web\Backend;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            return view('backend.layouts.dashboard');
        } catch (\Exception $e) {
            // Log the error
            Log::error('Dashboard Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Return error view or redirect
            return back()->with('error', 'Dashboard loading failed: ' . $e->getMessage());
        }
    }

    /**
     * Get user count by role name safely
     */
    private function getUserCountByRole($roleName)
    {
        try {
            $roleExists = DB::table('roles')->where('name', $roleName)->exists();

            if (!$roleExists) {
                return 0;
            }

            return DB::table('users')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('roles.name', $roleName)
                ->where('model_has_roles.model_type', 'App\Models\User')
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
