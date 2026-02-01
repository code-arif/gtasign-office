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
            // Basic user stats
            $userStats = [
                'total' => DB::table('users')->count(),
                'directors' => $this->getUserCountByRole('director'),
                'evaluators' => $this->getUserCountByRole('evaluator'),
                'referees' => $this->getUserCountByRole('referee'),
                'active' => DB::table('users')->where('status', 'active')->count(),
            ];

            // Sports types stats
            $sportsStats = [
                'total' => DB::table('sports_types')->count(),
                'active' => DB::table('sports_types')->where('status', 'active')->count(),
                'inactive' => DB::table('sports_types')->where('status', 'inactive')->count(),
                'camps_by_sport' => DB::table('camps')
                    ->select('sports_type_name', DB::raw('count(*) as total'))
                    ->whereNotNull('sports_type_name')
                    ->groupBy('sports_type_name')
                    ->get()
            ];

            // return $sportsStats;exit();

            // Camp stats
            $campStats = [
                'total' => DB::table('camps')->count(),
                'active' => DB::table('camps')->where('status', 'active')->count(),
                'upcoming' => DB::table('camps')->where('start_date', '>', Carbon::now())->count(),
                'ongoing' => DB::table('camps')
                    ->where('start_date', '<=', Carbon::now())
                    ->where('end_date', '>=', Carbon::now())
                    ->count(),
                'completed' => DB::table('camps')->where('end_date', '<', Carbon::now())->count(),
                'monthly' => DB::table('camps')
                    ->select(
                        DB::raw('MONTH(start_date) as month'),
                        DB::raw('COUNT(*) as count')
                    )
                    ->whereYear('start_date', Carbon::now()->year)
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
            ];

            // Schedule stats
            $scheduleStats = [
                'total' => DB::table('schedules')->count(),
                'published' => DB::table('schedules')->where('status', 'published')->count(),
                'draft' => DB::table('schedules')->where('status', 'draft')->count(),
            ];

            // Game slot stats
            $gameSlotStats = [
                'total' => DB::table('game_slots')->count(),
                'available' => DB::table('game_slots')->where('status', 'available')->count(),
                'assigned' => DB::table('game_slots')->where('status', 'assigned')->count(),
                'completed' => DB::table('game_slots')->where('status', 'completed')->count(),
                'blocked' => DB::table('game_slots')->where('is_block', true)->count(),
                'today' => DB::table('game_slots')->whereDate('game_date', Carbon::today())->count(),
                'this_week' => DB::table('game_slots')
                    ->whereBetween('game_date', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ])
                    ->count(),
                'weekly_slots' => DB::table('game_slots')
                    ->select(
                        DB::raw('DATE(game_date) as date'),
                        DB::raw('COUNT(*) as count')
                    )
                    ->whereBetween('game_date', [
                        Carbon::now()->subDays(30),
                        Carbon::now()
                    ])
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
            ];

            // Crew stats
            $totalCrews = DB::table('crews')->count();
            $totalMembers = DB::table('crew_members')->count();
            $avgMembers = $totalCrews > 0 ? round($totalMembers / $totalCrews, 1) : 0;

            $crewStats = [
                'total' => $totalCrews,
                'active' => DB::table('crews')->where('status', 'active')->count(),
                'total_members' => $totalMembers,
                'avg_members' => $avgMembers,
                'crew_distribution' => DB::table('crews')
                    ->select('crews.*', DB::raw('COUNT(crew_members.id) as members_count'))
                    ->leftJoin('crew_members', 'crews.id', '=', 'crew_members.crew_id')
                    ->groupBy('crews.id', 'crews.camp_id', 'crews.name', 'crews.description', 'crews.status', 'crews.created_at', 'crews.updated_at')
                    ->orderByDesc('members_count')
                    ->limit(5)
                    ->get()
            ];

            // Recent activities
            $recentCamps = DB::table('camps')
                ->latest('created_at')
                ->limit(10)
                ->get();

            $recentSchedules = DB::table('schedules')
                ->latest('created_at')
                ->limit(5)
                ->get();

            // Revenue
            $revenue = [
                'total' => DB::table('camps')->sum('price') ?? 0,
                'this_month' => DB::table('camps')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->sum('price') ?? 0,
            ];

            return view('backend.layouts.dashboard', compact(
                'userStats',
                'sportsStats',
                'campStats',
                'scheduleStats',
                'gameSlotStats',
                'crewStats',
                'recentCamps',
                'recentSchedules',
                'revenue'
            ));
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
