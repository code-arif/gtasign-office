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
use Illuminate\Support\Facades\Mail;
use App\Mail\ExpertStatusUpdateMail;

class ExpertManageController extends Controller
{
    /**
     * Display a listing of experts
     */
    public function index(Request $request)
    {
        $totalExperts    = User::role('expert')->count();
        $activeExperts   = User::role('expert')->where('status', 'active')->count();
        $inactiveExperts = User::role('expert')->where('status', 'inactive')->count();
        $suspendedExperts = User::role('expert')->where('status', 'suspended')->count();
        $deletedExperts  = User::role('expert')->onlyTrashed()->count();

        return view('backend.layouts.users.experts.index', compact(
            'totalExperts',
            'activeExperts',
            'inactiveExperts',
            'suspendedExperts',
            'deletedExperts'
        ));
    }

    /**
     * Server-side DataTables data
     */
    public function getData(Request $request)
    {
        if ($request->ajax() && $request->wantsJson()) {

            $query = User::role('expert', 'web')
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
                    'profile:id,user_id,first_name,last_name,avatar,username,level,level_name',
                ])
                ->withCount([
                    'gigs',                                          // $user->gigs_count
                    'sellerOrders',                                  // $user->seller_orders_count
                    'sellerOrders as completed_orders_count' => fn($q) // $user->completed_orders_count
                    => $q->where('status', 'completed'),
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
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        });
                });
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->filterColumn('email', function ($q, $keyword) {
                    $q->where('users.email', 'like', "%{$keyword}%");
                })
                ->addColumn('expert_info', function ($user) {
                    $profile = $user->profile;

                    $fullName = $profile
                        ? trim($profile->first_name . ' ' . ($profile->last_name ?? ''))
                        : 'N/A';

                    $username = $profile?->username ?? '';

                    // Avatar
                    $avatar = $profile && $profile->avatar
                        ? asset($profile->avatar)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=6366f1&color=fff&size=64';

                    // Badges & icons
                    $deletedBadge = $user->deleted_at
                        ? '<span class="badge bg-danger ms-2">Deleted</span>'
                        : '';

                    $levelBadge = '';
                    if ($profile && $profile->level) {
                        $levelText = strtoupper($profile->level) ?: ucfirst($profile->level_name ?? '');
                        $levelBadge = '<span class="badge bg-info ms-1 px-2">' . e($levelText) . '</span>';
                    }

                    $verifiedIcon = $user->email_verified_at
                        ? '<i class="fe fe-check-circle text-success ms-1" title="Email Verified"></i>'
                        : '';

                    // Final HTML
                    return '
                        <div class="d-flex align-items-center">
                            <img src="' . $avatar . '" alt="' . e($fullName) . '"
                                class="rounded-circle me-3 flex-shrink-0"
                                width="44" height="44"
                                style="object-fit:cover; border:2px solid #e9ecef;">
                            <div>
                                <div class="fw-semibold d-flex align-items-center flex-wrap gap-2">
                                    ' . e($fullName) . '
                                    ' . $levelBadge . '
                                    ' . $deletedBadge . '
                                </div>
                                <small class="text-muted d-block">
                                    @' . e($username) . $verifiedIcon . '
                                </small>
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
                    $earnings = Order::where('seller_id', $user->id)
                        ->where('status', 'completed')
                        ->sum('seller_earnings');

                    return '
                    <div class="d-flex gap-3 justify-content-center">
                        <div class="text-center">
                            <div class="fw-bold text-primary">' . ($user->gigs_count ?? 0) . '</div>
                            <small class="text-muted">Gigs</small>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold text-info">' . ($user->seller_orders_count ?? 0) . '</div>
                            <small class="text-muted">Orders</small>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold text-success">$' . number_format($earnings, 0) . '</div>
                            <small class="text-muted">Earned</small>
                        </div>
                    </div>';
                })
                ->addColumn('status', function ($user) {
                    $map = [
                        'active'    => 'success',
                        'inactive'  => 'secondary',
                        'suspended' => 'danger',
                    ];
                    $color = $map[$user->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . ' p-2">' . ucfirst($user->status) . '</span>';
                })
                ->addColumn('joined_at', function ($user) {
                    return '<small>' . $user->created_at->format('M d, Y') . '<br>'
                        . $user->created_at->format('h:i A') . '</small>';
                })
                ->addColumn('action', function ($user) {
                    $viewBtn = '<a href="' . route('admin.experts.show', $user->id) . '"
                        class="btn btn-primary btn-sm me-1" title="View Profile">
                        <i class="fe fe-eye"></i>
                    </a>';

                    $editBtn = '<a href="' . route('admin.experts.edit', $user->id) . '"
                        class="btn btn-warning btn-sm me-1" title="Edit Expert">
                        <i class="fe fe-edit-2"></i>
                    </a>';

                    $levelBtn = '<button type="button" class="btn btn-success btn-sm me-1"
                        title="Change Level"
                        onclick="openLevelModal(' . $user->id . ')">
                        <i class="fa-solid fa-turn-up"></i>
                    </button>';

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
                                    onclick="changeExpertStatus(' . $user->id . ', \'active\')">
                                    <i class="fe fe-check-circle text-success me-2"></i>Set Active</a></li>
                                <li><a class="dropdown-item" href="#"
                                    onclick="changeExpertStatus(' . $user->id . ', \'inactive\')">
                                    <i class="fe fe-pause-circle text-secondary me-2"></i>Set Inactive</a></li>
                                <li><a class="dropdown-item" href="#"
                                    onclick="changeExpertStatus(' . $user->id . ', \'suspended\')">
                                    <i class="fe fe-slash text-danger me-2"></i>Suspend</a></li>
                            </ul>
                        </div>';
                    }

                    return '<div class="d-flex">' . $viewBtn . $editBtn . $levelBtn . $statusBtn . '</div>';
                })
                ->rawColumns(['expert_info', 'contact', 'stats', 'status', 'joined_at', 'action'])
                ->make(true);
        }
    }

    /**
     * Show expert details — full dashboard
     */
    public function show($id)
    {
        $expert = User::role('expert')
            ->withTrashed()
            ->with([
                'profile',
                'educations',
                'certifications',
                'experiences',
            ])
            ->findOrFail($id);

        // Core stats
        $stats = [
            'total_gigs'       => $expert->gigs()->count(),
            'active_gigs'      => $expert->gigs()->where('status', 'active')->count(),
            'pending_gigs'     => $expert->gigs()->where('status', 'pending_approval')->count(),
            'total_orders'     => Order::where('seller_id', $id)->count(),
            'active_orders'    => Order::where('seller_id', $id)->whereIn('status', ['active', 'delivered', 'revision_requested'])->count(),
            'completed_orders' => Order::where('seller_id', $id)->where('status', 'completed')->count(),
            'cancelled_orders' => Order::where('seller_id', $id)->where('status', 'cancelled')->count(),
            'total_earnings'   => Order::where('seller_id', $id)->where('status', 'completed')->sum('seller_earnings'),
            'pending_earnings' => Order::where('seller_id', $id)->whereIn('status', ['active', 'delivered'])->sum('seller_earnings'),
            'avg_rating'       => DB::table('order_reviews')->where('reviewed_user_id', $id)->avg('rating') ?? 0,
            'total_reviews'    => DB::table('order_reviews')->where('reviewed_user_id', $id)->count(),
        ];

        // Recent gigs (last 5)
        $recentGigs = $expert->gigs()
            ->with(['category', 'subCategory'])
            ->latest()
            ->take(5)
            ->get();

        // Recent orders (last 8)
        $recentOrders = Order::where('seller_id', $id)
            ->with(['gig:id,title', 'buyer:id'])
            ->latest()
            ->take(8)
            ->get();

        // Recent reviews (last 5)
        $recentReviews = DB::table('order_reviews')
            ->join('users as reviewers', 'order_reviews.reviewer_id', '=', 'reviewers.id')
            ->join('gigs', 'order_reviews.gig_id', '=', 'gigs.id')
            ->where('order_reviews.reviewed_user_id', $id)
            ->select(
                'order_reviews.*',
                'reviewers.email as reviewer_email',
                'gigs.title as gig_title'
            )
            ->latest('order_reviews.created_at')
            ->take(5)
            ->get();

        // Orders per month (last 6 months) — for chart
        $ordersChart = Order::where('seller_id', $id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total, SUM(seller_earnings) as earnings")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Order status breakdown — for doughnut chart
        $statusBreakdown = Order::where('seller_id', $id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('backend.layouts.users.experts.show', compact(
            'expert',
            'stats',
            'recentGigs',
            'recentOrders',
            'recentReviews',
            'ordersChart',
            'statusBreakdown'
        ));
    }

    /**
     * Update expert status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:active,inactive,suspended',
                'reason' => 'required_if:status,suspended|nullable|string|max:500',
            ]);

            $expert = User::role('expert')->findOrFail($id);
            $expert->update(['status' => $request->status]);

            // Send email notification to expert
            try {
                Mail::to($expert->email)->send(new ExpertStatusUpdateMail($expert, $request->status, $request->reason));
            } catch (Exception $mailEx) {
                Log::error('Expert status update mail failed for user ' . $id . ': ' . $mailEx->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Expert status updated successfully',
                'status'  => $request->status,
            ]);
        } catch (Exception $e) {
            Log::error('Expert status update error: ' . $e->getMessage());
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
        $expert = User::role('expert')->findOrFail($id);

        if (Order::where('seller_id', $id)->whereIn('status', ['active', 'delivered', 'revision_requested'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete expert with active orders',
            ], 422);
        }

        $expert->delete();
        return response()->json(['success' => true, 'message' => 'Expert deleted successfully']);
    }

    /**
     * Restore soft-deleted expert
     */
    public function restore($id)
    {
        $expert = User::role('expert')->onlyTrashed()->findOrFail($id);
        $expert->restore();
        return response()->json(['success' => true, 'message' => 'Expert restored successfully']);
    }

    /**
     * Force delete
     */
    public function forceDelete($id)
    {
        $expert = User::role('expert')->onlyTrashed()->findOrFail($id);
        $expert->forceDelete();
        return response()->json(['success' => true, 'message' => 'Expert permanently deleted']);
    }

    /**
     * Export experts CSV
     */
    public function export(Request $request)
    {
        try {
            $query = User::role('expert')->with('profile');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('show_deleted') && $request->show_deleted === 'true') {
                $query->withTrashed();
            }

            $experts  = $query->get();
            $filename = 'experts_' . date('Y-m-d_His') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($experts) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['ID', 'Name', 'Email', 'Phone', 'Username', 'Status', 'Total Gigs', 'Total Orders', 'Total Earnings', 'Joined Date', 'Deleted']);
                foreach ($experts as $expert) {
                    $profile = $expert->profile;
                    fputcsv($file, [
                        $expert->id,
                        $profile ? trim($profile->first_name . ' ' . ($profile->last_name ?? '')) : 'N/A',
                        $expert->email,
                        $expert->phone ?? 'N/A',
                        $profile->username ?? 'N/A',
                        $expert->status,
                        $expert->gigs()->count(),
                        Order::where('seller_id', $expert->id)->count(),
                        number_format(Order::where('seller_id', $expert->id)->where('status', 'completed')->sum('seller_earnings'), 2),
                        $expert->created_at->format('Y-m-d'),
                        $expert->deleted_at ? 'Yes' : 'No',
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            Log::error('Expert export error: ' . $e->getMessage());
            return back()->with('error', 'Failed to export experts');
        }
    }

    /**
     * Show level change modal content (for AJAX or direct)
     */
    public function getLevelForm($id)
    {
        $expert = User::role('expert')
            ->with('profile')
            ->findOrFail($id);

        $currentLevel      = $expert->profile?->level ?? null;
        $currentLevelName  = $expert->profile?->level_name ?? null;

        return response()->json([
            'success'         => true,
            'expert_id'       => $expert->id,
            'current_level'   => $currentLevel,
            'current_level_name' => $currentLevelName,
            'full_name'       => $expert->profile
                ? trim($expert->profile->first_name . ' ' . ($expert->profile->last_name ?? ''))
                : 'N/A',
            'username'        => $expert->profile?->username ?? '—',
        ]);
    }

    /**
     * Update expert level
     */
    public function updateLevel(Request $request, $id)
    {
        $request->validate([
            'level'      => 'required|string|max:50',
            'level_name' => 'nullable|string|max:100',
        ]);

        try {
            $expert = User::role('expert')->findOrFail($id);

            $expert->profile()->update([
                'level'      => $request->level,
                'level_name' => $request->level_name ?: null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expert level updated successfully',
                'level'   => $request->level,
                'level_name' => $request->level_name,
            ]);
        } catch (Exception $e) {
            Log::error('Level update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update level: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Show the edit form for an expert
     */
    public function edit($id)
    {
        $expert = User::role('expert')
            ->withTrashed()
            ->with([
                'profile',
                'educations',
                'certifications',
                'experiences',
            ])
            ->findOrFail($id);

        return view('backend.layouts.users.experts.edit', compact('expert'));
    }

    /**
     * Update expert info
     */
    public function update(Request $request, $id)
    {
        $expert = User::role('expert')->withTrashed()->findOrFail($id);

        $request->validate([
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'nullable|string|max:100',
            'email'       => 'required|email|max:191|unique:users,email,' . $expert->id,
            'phone'       => 'nullable|string|max:20',
            'username'    => 'nullable|string|max:80|unique:profiles,username,' . $expert->profile?->id,
            'status'      => 'required|in:active,inactive,suspended',
            'level'       => 'nullable|string|max:50',
            'level_name'  => 'nullable|string|max:100',
            'bio'         => 'nullable|string|max:1000',
            'avatar'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // 1. Update user table
            $expert->update([
                'email'  => $request->email,
                'phone'  => $request->phone,
                'status' => $request->status,
            ]);

            // 2. Handle avatar upload
            $avatarPath = $expert->profile?->avatar;

            if ($request->hasFile('avatar')) {
                // Delete old avatar if it exists and is a local file
                if ($avatarPath && file_exists(public_path($avatarPath))) {
                    @unlink(public_path($avatarPath));
                }

                $file      = $request->file('avatar');
                $filename  = 'expert_' . $expert->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $directory = 'uploads/experts/avatars';

                $file->move(public_path($directory), $filename);
                $avatarPath = $directory . '/' . $filename;
            }

            // 3. Update profile table
            $expert->profile()->updateOrCreate(
                ['user_id' => $expert->id],
                [
                    'first_name' => $request->first_name,
                    'last_name'  => $request->last_name,
                    'username'   => $request->username ?: $expert->profile?->username,
                    'bio'        => $request->bio,
                    'level'      => $request->level,
                    'level_name' => $request->level_name,
                    'avatar'     => $avatarPath,
                ]
            );

            DB::commit();

            return redirect()->route('admin.experts.show', $expert->id)->with('t-success', 'Expert profile updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Expert update failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to update expert: ' . $e->getMessage());
        }
    }

    // Sub-page methods (gigs, orders, earnings) remain in their respective views via DataTables
    public function gigs($id)
    { /* handled in show dashboard */
    }
    public function orders($id)
    { /* handled in show dashboard */
    }
    public function earnings($id)
    { /* handled in show dashboard */
    }
}
