<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Gig;
use App\Models\User;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('backend.layouts.dashboard');
    }

    // public function index()
    // {
    //     $stats = [
    //         'total_experts' => User::role('expert')->count(),
    //         'active_experts' => User::role('expert')->active()->count(),
    //         'total_clients' => User::role('client')->count(),
    //         'active_clients' => User::role('client')->active()->count(),
    //         'total_gigs' => Gig::count(),
    //         'active_gigs' => Gig::active()->count(),
    //         'pending_gigs' => Gig::where('status', 'pending_approval')->count(),
    //         // 'total_orders' => Order::count(),
    //         // 'active_orders' => Order::active()->count(),
    //         // 'completed_orders' => Order::completed()->count(),
    //         // 'total_revenue' => Order::completed()->sum('platform_fee'),
    //     ];

    //     $recentExperts = User::role('expert')
    //         ->with('profile')
    //         ->latest()
    //         ->take(5)
    //         ->get();

    //     // $recentOrders = Order::with(['gig', 'buyer', 'seller'])
    //     //     ->latest()
    //     //     ->take(10)
    //     //     ->get();

    //     $pendingGigs = Gig::with(['user', 'category'])
    //         ->where('status', 'pending_approval')
    //         ->latest()
    //         ->take(5)
    //         ->get();

    //     return view('backend.layouts.dashboard', compact(
    //         'stats',
    //         'recentExperts',
    //         'recentOrders',
    //         'pendingGigs'
    //     ));
    // }
}
