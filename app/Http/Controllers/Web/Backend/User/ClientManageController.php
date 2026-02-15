<?php

namespace App\Http\Controllers\Web\Backend\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClientManageController extends Controller
{
    /**
     * Display a listing of clients
     */
    public function index(Request $request)
    {
        $query = User::role('client')
            ->with(['profile', 'buyerOrders']);

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

        $clients = $query->latest()->paginate(20);

        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Show client details
     */
    public function show($id)
    {
        $client = User::role('client')
            ->with([
                'profile',
                'buyerOrders' => function ($query) {
                    $query->latest();
                }
            ])
            ->findOrFail($id);

        $stats = [
            'total_orders' => $client->buyerOrders()->count(),
            'active_orders' => $client->buyerOrders()->active()->count(),
            'completed_orders' => $client->buyerOrders()->completed()->count(),
            'cancelled_orders' => $client->buyerOrders()->where('status', 'cancelled')->count(),
            'total_spent' => $client->buyerOrders()->completed()->sum('total_amount'),
        ];

        return view('admin.clients.show', compact('client', 'stats'));
    }

    /**
     * Update client status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,suspended',
            'reason' => 'required_if:status,suspended|nullable|string|max:500',
        ]);

        $client = User::role('client')->findOrFail($id);

        $client->update([
            'status' => $request->status,
        ]);

        if ($request->status === 'suspended' && $request->filled('reason')) {
            activity()
                ->performedOn($client)
                ->causedBy(auth()->user())
                ->withProperties(['reason' => $request->reason])
                ->log('Client suspended');
        }

        return redirect()
            ->back()
            ->with('success', 'Client status updated successfully');
    }

    /**
     * Delete client
     */
    public function destroy($id)
    {
        $client = User::role('client')->findOrFail($id);

        // Check if client has active orders
        if ($client->buyerOrders()->active()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete client with active orders');
        }

        $client->delete();

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'Client deleted successfully');
    }

    /**
     * Restore deleted client
     */
    public function restore($id)
    {
        $client = User::role('client')->onlyTrashed()->findOrFail($id);
        $client->restore();

        return redirect()
            ->back()
            ->with('success', 'Client restored successfully');
    }

    /**
     * Show client's orders
     */
    public function orders($id)
    {
        $client = User::role('client')->findOrFail($id);

        $orders = $client->buyerOrders()
            ->with(['gig', 'seller'])
            ->latest()
            ->paginate(20);

        return view('admin.clients.orders', compact('client', 'orders'));
    }

    /**
     * Export clients
     */
    public function export(Request $request)
    {
        $query = User::role('client')->with('profile');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $clients = $query->get();

        $filename = 'clients_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($clients) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Name',
                'Email',
                'Phone',
                'Username',
                'Status',
                'Total Orders',
                'Total Spent',
                'Joined Date'
            ]);

            foreach ($clients as $client) {
                fputcsv($file, [
                    $client->id,
                    $client->profile->full_name ?? 'N/A',
                    $client->email,
                    $client->phone ?? 'N/A',
                    $client->profile->username ?? 'N/A',
                    $client->status,
                    $client->buyerOrders()->count(),
                    number_format($client->buyerOrders()->completed()->sum('total_amount'), 2),
                    $client->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
