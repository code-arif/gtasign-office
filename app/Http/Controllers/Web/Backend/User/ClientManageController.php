<?php

namespace App\Http\Controllers\Web\Backend\User;

use Exception;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ClientManageController extends Controller
{
    /**
     * Client list page
     */
    public function index()
    {
        $totalClients    = User::role('client', 'web')->count();
        $activeClients   = User::role('client', 'web')->where('status', 'active')->count();
        $inactiveClients = User::role('client', 'web')->where('status', 'inactive')->count();
        $suspendedClients = User::role('client', 'web')->where('status', 'suspended')->count();
        $deletedClients  = User::role('client', 'web')->onlyTrashed()->count();

        return view('backend.layouts.users.clients.index', compact(
            'totalClients',
            'activeClients',
            'inactiveClients',
            'suspendedClients',
            'deletedClients'
        ));
    }

    /**
     * Server-side DataTables
     */
    public function getData(Request $request)
    {
        if ($request->ajax() && $request->wantsJson()) {

            $query = User::role('client', 'web')
                ->select([
                    'users.id',
                    'users.email',
                    'users.phone',
                    'users.status',
                    'users.created_at',
                    'users.deleted_at',
                    'users.email_verified_at',
                ])
                ->with([
                    'profile:id,user_id,first_name,last_name,avatar,username',
                ])
                ->withCount([
                    'buyerOrders',
                    'buyerOrders as completed_orders_count' => fn($q) => $q->where('status', 'completed'),
                    'buyerOrders as active_orders_count'    => fn($q) => $q->whereIn('status', ['active', 'delivered', 'revision_requested']),
                ]);

            // Show deleted
            if ($request->filled('show_deleted') && $request->show_deleted === 'true') {
                $query->withTrashed();
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('users.status', $request->status);
            }

            // Date range
            if ($request->filled('date_from')) {
                $query->whereDate('users.created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('users.created_at', '<=', $request->date_to);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('users.email', 'like', "%{$search}%")
                        ->orWhere('users.phone', 'like', "%{$search}%")
                        ->orWhereHas('profile', function ($pq) use ($search) {
                            $pq->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name',  'like', "%{$search}%")
                                ->orWhere('username',   'like', "%{$search}%");
                        });
                });
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->filterColumn('email', fn($q, $k) => $q->where('users.email', 'like', "%{$k}%"))
                ->addColumn('client_info', function ($user) {
                    $profile  = $user->profile;
                    $fullName = $profile
                        ? trim($profile->first_name . ' ' . ($profile->last_name ?? ''))
                        : 'N/A';
                    $username = $profile->username ?? '';
                    $avatar   = $profile && $profile->avatar
                        ? asset($profile->avatar)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=0ea5e9&color=fff&size=64';

                    $deletedBadge = $user->deleted_at
                        ? '<span class="badge bg-danger ms-2">Deleted</span>' : '';
                    $verifiedIcon = $user->email_verified_at
                        ? '<i class="fe fe-check-circle text-success ms-1" title="Verified"></i>' : '';

                    return '
                        <div class="d-flex align-items-center">
                            <img src="' . $avatar . '" alt="avatar" class="rounded-circle me-3 flex-shrink-0"
                                 width="44" height="44" style="object-fit:cover; border:2px solid #e9ecef;">
                            <div>
                                <div class="fw-semibold">' . e($fullName) . $deletedBadge . '</div>
                                <small class="text-muted">@' . e($username) . $verifiedIcon . '</small>
                            </div>
                        </div>';
                })
                ->addColumn('contact', function ($user) {
                    return '
                        <div>
                            <div class="small"><i class="fe fe-mail me-1 text-muted"></i>' . e($user->email) . '</div>
                            <div class="small text-muted"><i class="fe fe-phone me-1"></i>' . e($user->phone ?? '—') . '</div>
                        </div>';
                })
                ->addColumn('stats', function ($user) {
                    $totalSpent = Order::where('buyer_id', $user->id)
                        ->where('status', 'completed')
                        ->sum('price');

                    return '
                        <div class="d-flex gap-3 justify-content-center">
                            <div class="text-center">
                                <div class="fw-bold text-primary">' . ($user->buyer_orders_count ?? 0) . '</div>
                                <small class="text-muted">Orders</small>
                            </div>
                            <div class="text-center">
                                <div class="fw-bold text-success">' . ($user->completed_orders_count ?? 0) . '</div>
                                <small class="text-muted">Completed</small>
                            </div>
                            <div class="text-center">
                                <div class="fw-bold text-info">$' . number_format($totalSpent, 0) . '</div>
                                <small class="text-muted">Spent</small>
                            </div>
                        </div>';
                })
                ->addColumn('status', function ($user) {
                    $map = ['active' => 'success', 'inactive' => 'secondary', 'suspended' => 'danger'];
                    $color = $map[$user->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . ' p-2">' . ucfirst($user->status) . '</span>';
                })
                ->addColumn('joined_at', function ($user) {
                    return '<small>' . $user->created_at->format('M d, Y') . '<br>'
                        . $user->created_at->format('h:i A') . '</small>';
                })
                ->addColumn('action', function ($user) {
                    $viewBtn = '<a href="' . route('admin.clients.show', $user->id) . '"
                        class="btn btn-primary btn-sm me-1" title="View Profile">
                        <i class="fe fe-eye"></i>
                    </a>';

                    $statusBtn = '';
                    if (!$user->deleted_at) {
                        $statusBtn = '
                        <div class="btn-group">
                            <button type="button" class="btn btn-info btn-sm dropdown-toggle"
                                    data-bs-toggle="dropdown" title="Change Status">
                                <i class="fe fe-settings"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"
                                    onclick="changeClientStatus(' . $user->id . ', \'active\')">
                                    <i class="fe fe-check-circle text-success me-2"></i>Set Active</a></li>
                                <li><a class="dropdown-item" href="#"
                                    onclick="changeClientStatus(' . $user->id . ', \'inactive\')">
                                    <i class="fe fe-pause-circle text-secondary me-2"></i>Set Inactive</a></li>
                                <li><a class="dropdown-item" href="#"
                                    onclick="changeClientStatus(' . $user->id . ', \'suspended\')">
                                    <i class="fe fe-slash text-danger me-2"></i>Suspend</a></li>
                            </ul>
                        </div>';
                    }

                    return '<div class="d-flex">' . $viewBtn . $statusBtn . '</div>';
                })
                ->rawColumns(['client_info', 'contact', 'stats', 'status', 'joined_at', 'action'])
                ->make(true);
        }
    }

    /**
     * Client full dashboard
     */
    public function show($id)
    {
        $client = User::role('client', 'web')
            ->withTrashed()
            ->with(['profile'])
            ->findOrFail($id);

        // Core stats
        $stats = [
            'total_orders'     => Order::where('buyer_id', $id)->count(),
            'active_orders'    => Order::where('buyer_id', $id)->whereIn('status', ['active', 'delivered', 'revision_requested'])->count(),
            'completed_orders' => Order::where('buyer_id', $id)->where('status', 'completed')->count(),
            'cancelled_orders' => Order::where('buyer_id', $id)->where('status', 'cancelled')->count(),
            'pending_orders'   => Order::where('buyer_id', $id)->where('status', 'pending_payment')->count(),
            'disputed_orders'  => Order::where('buyer_id', $id)->where('status', 'disputed')->count(),
            'total_spent'      => Order::where('buyer_id', $id)->where('status', 'completed')->sum('price'),
            'pending_payment'  => Order::where('buyer_id', $id)->where('status', 'pending_payment')->sum('price'),
            'total_reviews'    => DB::table('order_reviews')->where('reviewer_id', $id)->count(),
            'avg_rating_given' => DB::table('order_reviews')->where('reviewer_id', $id)->avg('rating') ?? 0,
            'custom_offers'    => DB::table('custom_offers')->where('client_id', $id)->count(),
        ];

        // Recent orders (last 8)
        $recentOrders = Order::where('buyer_id', $id)
            ->with(['gig:id,title', 'seller:id'])
            ->latest()
            ->take(8)
            ->get();

        // Recent reviews given by client
        $recentReviews = DB::table('order_reviews')
            ->join('gigs', 'order_reviews.gig_id', '=', 'gigs.id')
            ->join('users as sellers', 'order_reviews.reviewed_user_id', '=', 'sellers.id')
            ->where('order_reviews.reviewer_id', $id)
            ->select(
                'order_reviews.*',
                'gigs.title as gig_title',
                'sellers.email as seller_email'
            )
            ->latest('order_reviews.created_at')
            ->take(5)
            ->get();

        // Custom offers
        $customOffers = DB::table('custom_offers')
            ->join('users as experts', 'custom_offers.expert_id', '=', 'experts.id')
            ->where('custom_offers.client_id', $id)
            ->select('custom_offers.*', 'experts.email as expert_email')
            ->latest('custom_offers.created_at')
            ->take(5)
            ->get();

        // Orders per month (last 6 months) — chart
        $ordersChart = Order::where('buyer_id', $id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total, SUM(price) as spent")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Order status breakdown — doughnut
        $statusBreakdown = Order::where('buyer_id', $id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('backend.layouts.users.clients.show', compact(
            'client',
            'stats',
            'recentOrders',
            'recentReviews',
            'customOffers',
            'ordersChart',
            'statusBreakdown'
        ));
    }

    /**
     * Update client status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:active,inactive,suspended',
                'reason' => 'required_if:status,suspended|nullable|string|max:500',
            ]);

            $client = User::role('client', 'web')->findOrFail($id);
            $client->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Client status updated successfully',
                'status'  => $request->status,
            ]);
        } catch (Exception $e) {
            Log::error('Client status update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Soft delete
     */
    public function destroy($id)
    {
        $client = User::role('client', 'web')->findOrFail($id);

        if (Order::where('buyer_id', $id)->whereIn('status', ['active', 'delivered', 'revision_requested'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete client with active orders',
            ], 422);
        }

        $client->delete();
        return response()->json(['success' => true, 'message' => 'Client deleted successfully']);
    }

    /**
     * Restore
     */
    public function restore($id)
    {
        $client = User::role('client', 'web')->onlyTrashed()->findOrFail($id);
        $client->restore();
        return response()->json(['success' => true, 'message' => 'Client restored successfully']);
    }

    /**
     * Force delete
     */
    public function forceDelete($id)
    {
        $client = User::role('client', 'web')->onlyTrashed()->findOrFail($id);
        $client->forceDelete();
        return response()->json(['success' => true, 'message' => 'Client permanently deleted']);
    }

    /**
     * Export CSV
     */
    public function export(Request $request)
    {
        try {
            $query = User::role('client', 'web')->with('profile');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('show_deleted') && $request->show_deleted === 'true') {
                $query->withTrashed();
            }

            $clients  = $query->get();
            $filename = 'clients_' . date('Y-m-d_His') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($clients) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['ID', 'Name', 'Email', 'Phone', 'Username', 'Status', 'Total Orders', 'Completed Orders', 'Total Spent', 'Joined Date', 'Deleted']);
                foreach ($clients as $client) {
                    $profile = $client->profile;
                    fputcsv($file, [
                        $client->id,
                        $profile ? trim($profile->first_name . ' ' . ($profile->last_name ?? '')) : 'N/A',
                        $client->email,
                        $client->phone ?? 'N/A',
                        $profile->username ?? 'N/A',
                        $client->status,
                        Order::where('buyer_id', $client->id)->count(),
                        Order::where('buyer_id', $client->id)->where('status', 'completed')->count(),
                        number_format(Order::where('buyer_id', $client->id)->where('status', 'completed')->sum('price'), 2),
                        $client->created_at->format('Y-m-d'),
                        $client->deleted_at ? 'Yes' : 'No',
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            Log::error('Client export error: ' . $e->getMessage());
            return back()->with('error', 'Failed to export clients');
        }
    }
}
