<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Gig;
use App\Models\Order;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Core Stats ──────────────────────────────────────────────
        $stats = [
            'total_experts'    => User::role('expert', 'web')->count(),
            'active_experts'   => User::role('expert', 'web')->where('status', 'active')->count(),
            'new_experts_week' => User::role('expert', 'web')->where('created_at', '>=', now()->subWeek())->count(),

            'total_clients'    => User::role('client', 'web')->count(),
            'active_clients'   => User::role('client', 'web')->where('status', 'active')->count(),
            'new_clients_week' => User::role('client', 'web')->where('created_at', '>=', now()->subWeek())->count(),

            'total_gigs'       => Gig::count(),
            'active_gigs'      => Gig::where('status', 'active')->count(),
            'pending_gigs'     => Gig::where('status', 'pending_approval')->count(),

            'total_orders'     => Order::count(),
            'active_orders'    => Order::whereIn('status', ['active', 'delivered', 'revision_requested'])->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'new_orders_today' => Order::whereDate('created_at', today())->count(),

            'total_revenue'    => Order::where('status', 'completed')->sum('platform_fee'),
            'revenue_month'    => Order::where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('platform_fee'),
            'revenue_week'     => Order::where('status', 'completed')
                ->where('created_at', '>=', now()->subWeek())
                ->sum('platform_fee'),
        ];

        // ── Orders Chart — last 30 days (daily) ─────────────────────
        $ordersDaily = Order::where('created_at', '>=', now()->subDays(29))
            ->selectRaw("DATE(created_at) as day, COUNT(*) as total, SUM(platform_fee) as revenue")
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        // Fill all 30 days (even zeros)
        $chartDays     = [];
        $chartOrders   = [];
        $chartRevenue  = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartDays[]    = now()->subDays($i)->format('M d');
            $chartOrders[]  = $ordersDaily[$date]->total ?? 0;
            $chartRevenue[] = round($ordersDaily[$date]->revenue ?? 0, 2);
        }

        // ── Revenue Chart — last 6 months (monthly) ─────────────────
        $revenueMonthly = Order::where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(platform_fee) as revenue, COUNT(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $chartMonths       = [];
        $chartMonthRevenue = [];
        $chartMonthOrders  = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $chartMonths[]       = now()->subMonths($i)->format('M Y');
            $chartMonthRevenue[] = round($revenueMonthly[$key]->revenue ?? 0, 2);
            $chartMonthOrders[]  = $revenueMonthly[$key]->total ?? 0;
        }

        // ── Order Status Breakdown ───────────────────────────────────
        $orderStatusBreakdown = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // ── Recent Data ──────────────────────────────────────────────
        $recentOrders = Order::with([
            'gig:id,title',
            'buyer:id',
            'seller:id',
            'buyer.profile:id,user_id,first_name,last_name,avatar',
            'seller.profile:id,user_id,first_name,last_name',
        ])
            ->latest()
            ->take(8)
            ->get();

        $pendingGigs = Gig::with(['user.profile:id,user_id,first_name,last_name', 'category:id,name'])
            ->where('status', 'pending_approval')
            ->latest()
            ->take(6)
            ->get();

        $recentExperts = User::role('expert', 'web')
            ->with('profile:id,user_id,first_name,last_name,avatar,username')
            ->latest()
            ->take(5)
            ->get();

        return view('backend.layouts.dashboard', compact(
            'stats',
            'chartDays',
            'chartOrders',
            'chartRevenue',
            'chartMonths',
            'chartMonthRevenue',
            'chartMonthOrders',
            'orderStatusBreakdown',
            'recentOrders',
            'pendingGigs',
            'recentExperts'
        ));
    }
}
