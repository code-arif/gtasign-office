<?php

namespace App\Http\Controllers\Web\Backend\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ExpertManageController extends Controller
{
    /**
     * Display a listing of experts
     */
    public function index(Request $request)
    {
        $query = User::role('expert')
            ->with(['profile', 'gigs', 'sellerOrders']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($pq) use ($search) {
                        $pq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $experts = $query->latest()->paginate(20);

        return view('admin.experts.index', compact('experts'));
    }

    /**
     * Show expert details
     */
    public function show($id)
    {
        $expert = User::role('expert')
            ->with([
                'profile',
                'education',
                'certifications',
                'experiences',
                'gigs' => function ($query) {
                    $query->latest();
                },
                'sellerOrders' => function ($query) {
                    $query->latest();
                },
                'reviews'
            ])
            ->findOrFail($id);

        $stats = [
            'total_gigs' => $expert->gigs()->count(),
            'active_gigs' => $expert->gigs()->active()->count(),
            'total_orders' => $expert->sellerOrders()->count(),
            'active_orders' => $expert->sellerOrders()->active()->count(),
            'completed_orders' => $expert->sellerOrders()->completed()->count(),
            'total_earnings' => $expert->sellerOrders()->completed()->sum('seller_earnings'),
            'average_rating' => $expert->getAverageRating(),
            'total_reviews' => $expert->getTotalReviews(),
        ];

        return view('admin.experts.show', compact('expert', 'stats'));
    }

    /**
     * Update expert status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,suspended',
            'reason' => 'required_if:status,suspended|nullable|string|max:500',
        ]);

        $expert = User::role('expert')->findOrFail($id);

        $expert->update([
            'status' => $request->status,
        ]);

        // If suspended, you might want to log the reason
        if ($request->status === 'suspended' && $request->filled('reason')) {
            // Log suspension reason (you can create a separate table for this)
            activity()
                ->performedOn($expert)
                ->causedBy(auth()->user())
                ->withProperties(['reason' => $request->reason])
                ->log('Expert suspended');
        }

        return redirect()
            ->back()
            ->with('success', 'Expert status updated successfully');
    }

    /**
     * Delete expert
     */
    public function destroy($id)
    {
        $expert = User::role('expert')->findOrFail($id);

        // Check if expert has active orders
        if ($expert->sellerOrders()->active()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete expert with active orders');
        }

        // Soft delete
        $expert->delete();

        return redirect()
            ->route('admin.experts.index')
            ->with('success', 'Expert deleted successfully');
    }

    /**
     * Restore deleted expert
     */
    public function restore($id)
    {
        $expert = User::role('expert')->onlyTrashed()->findOrFail($id);
        $expert->restore();

        return redirect()
            ->back()
            ->with('success', 'Expert restored successfully');
    }

    /**
     * Permanently delete expert
     */
    public function forceDelete($id)
    {
        $expert = User::role('expert')->onlyTrashed()->findOrFail($id);

        // This will cascade delete all related data
        $expert->forceDelete();

        return redirect()
            ->route('admin.experts.index')
            ->with('success', 'Expert permanently deleted');
    }

    /**
     * Export experts
     */
    public function export(Request $request)
    {
        $query = User::role('expert')->with('profile');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $experts = $query->get();

        $filename = 'experts_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($experts) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'ID',
                'Name',
                'Email',
                'Phone',
                'Username',
                'Status',
                'Total Gigs',
                'Total Orders',
                'Total Earnings',
                'Joined Date'
            ]);

            // Data
            foreach ($experts as $expert) {
                fputcsv($file, [
                    $expert->id,
                    $expert->profile->full_name ?? 'N/A',
                    $expert->email,
                    $expert->phone ?? 'N/A',
                    $expert->profile->username ?? 'N/A',
                    $expert->status,
                    $expert->gigs()->count(),
                    $expert->sellerOrders()->count(),
                    number_format($expert->sellerOrders()->completed()->sum('seller_earnings'), 2),
                    $expert->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show expert's gigs
     */
    public function gigs($id)
    {
        $expert = User::role('expert')->findOrFail($id);

        $gigs = $expert->gigs()
            ->with(['category', 'subCategory'])
            ->latest()
            ->paginate(20);

        return view('admin.experts.gigs', compact('expert', 'gigs'));
    }

    /**
     * Show expert's orders
     */
    public function orders($id)
    {
        $expert = User::role('expert')->findOrFail($id);

        $orders = $expert->sellerOrders()
            ->with(['gig', 'buyer'])
            ->latest()
            ->paginate(20);

        return view('admin.experts.orders', compact('expert', 'orders'));
    }

    /**
     * Show expert's earnings
     */
    public function earnings($id)
    {
        $expert = User::role('expert')->findOrFail($id);

        $earnings = $expert->earnings()
            ->with('order.gig')
            ->latest()
            ->paginate(20);

        $stats = [
            'total_earnings' => $expert->earnings()->sum('net_amount'),
            'available_balance' => $expert->available_balance,
            'pending_clearance' => $expert->pending_clearance,
            'withdrawn' => $expert->earnings()->where('status', 'withdrawn')->sum('net_amount'),
        ];

        return view('admin.experts.earnings', compact('expert', 'earnings', 'stats'));
    }
}
