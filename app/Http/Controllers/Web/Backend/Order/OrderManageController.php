<?php

namespace App\Http\Controllers\Web\Backend\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class OrderManageController extends Controller
{
    /**
     * Order list
     */
    public function index()
    {
        $stats = [
            'total_orders'     => Order::count(),
            'pending_payment'  => Order::where('status', 'pending_payment')->count(),
            'active_orders'    => Order::whereIn('status', ['active', 'delivered', 'revision_requested'])->count(),
            'qa_pending'       => Order::where('status', 'qa_pending')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'disputed_orders'  => Order::where('status', 'disputed')->count(),
            'total_revenue'    => Order::where('status', 'completed')->sum('platform_fee'),
        ];

        return view('backend.layouts.orders.index', compact('stats'));
    }

    /**
     * DataTables server-side
     */
    public function getData(Request $request)
    {
        if ($request->ajax() && $request->wantsJson()) {

            $query = Order::query()
                ->with([
                    'gig:id,title',
                    'buyer:id',
                    'buyer.profile:id,user_id,first_name,last_name,avatar',
                    'seller:id',
                    'seller.profile:id,user_id,first_name,last_name',
                ])->latest('id');

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Date range
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('gig', fn($gq) => $gq->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('buyer.profile', fn($bp) =>
                        $bp->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"))
                        ->orWhereHas('seller.profile', fn($sp) =>
                        $sp->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"));
                });
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->filterColumn('order_number', fn($q, $k) => $q->where('order_number', 'like', "%{$k}%"))
                ->addColumn('order_info', function ($order) {
                    return '
                        <div>
                            <div class="fw-semibold">#' . e($order->order_number) . '</div>
                            <small class="text-muted text-truncate d-block" style="max-width:200px;" title="' . e($order->gig?->title) . '">
                                ' . e(Str::limit($order->gig?->title ?? 'N/A', 30)) . '
                            </small>
                        </div>';
                })
                ->addColumn('buyer', function ($order) {
                    $bp = $order->buyer?->profile;
                    $name = $bp ? trim($bp->first_name . ' ' . ($bp->last_name ?? '')) : ($order->buyer?->email ?? 'N/A');
                    $avatar = $bp?->avatar
                        ? asset('storage/' . $bp->avatar)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=32&background=0ea5e9&color=fff';
                    return '
                        <div class="d-flex align-items-center gap-2">
                            <img src="' . $avatar . '" class="rounded-circle flex-shrink-0"
                                 width="28" height="28" style="object-fit:cover;" alt="">
                            <small class="text-truncate" style="max-width:100px;">' . e($name) . '</small>
                        </div>';
                })
                ->addColumn('seller', function ($order) {
                    $sp = $order->seller?->profile;
                    $name = $sp ? trim($sp->first_name . ' ' . ($sp->last_name ?? '')) : ($order->seller?->email ?? 'N/A');
                    return '<small class="text-truncate d-block" style="max-width:100px;">' . e($name) . '</small>';
                })
                ->addColumn('pricing', function ($order) {
                    return '
                        <div class="text-center">
                            <div class="fw-bold text-success">${' . number_format($order->price, 2) . '}</div>
                            <small class="text-muted">Fee: $' . number_format($order->platform_fee, 2) . '</small>
                        </div>';
                })
                ->addColumn('status', function ($order) {
                    $map = [
                        'pending_payment'    => ['Pending Payment',    'warning'],
                        'active'             => ['Active',             'primary'],
                        'qa_pending'         => ['QA Pending',         'info'],
                        'qa_rejected'        => ['QA Rejected',        'danger'],
                        'delivered'          => ['Delivered',          'info'],
                        'revision_requested' => ['Revision Requested', 'warning'],
                        'completed'          => ['Completed',          'success'],
                        'cancelled'          => ['Cancelled',          'secondary'],
                        'disputed'           => ['Disputed',           'danger'],
                    ];
                    $s = $map[$order->status] ?? [ucfirst($order->status), 'secondary'];
                    return '<span class="badge bg-' . $s[1] . ' p-2">' . e($s[0]) . '</span>';
                })
                ->addColumn('created_at', function ($order) {
                    return '<small>' . $order->created_at->format('M d, Y') . '<br>'
                        . $order->created_at->format('h:i A') . '</small>';
                })
                ->addColumn('action', function ($order) {
                    return '<a href="' . route('admin.orders.show', $order->id) . '"
                        class="btn btn-primary btn-sm" title="View Details">
                        <i class="fe fe-eye"></i>
                    </a>';
                })
                ->rawColumns(['order_info', 'buyer', 'seller', 'pricing', 'status', 'created_at', 'action'])
                ->make(true);
        }
    }

    /**
     * Order details — comprehensive dashboard
     */
    public function show($id)
    {
        $order = Order::with([
            'gig',
            'customOffer',
            'buyer.profile',
            'seller.profile',
            'deliveries' => fn($q) => $q->orderBy('delivery_number'),
            // 'qaReviews.reviewedBy',
            'extensionRequests.requestedBy',
            'activities.user',
            'review',
        ])
            ->findOrFail($id);

        // Activity chart data — last 10 activities with timestamps
        $activityChart = $order->activities()
            ->orderBy('created_at')
            ->get()
            ->map(fn($a) => [
                'label' => ucfirst(str_replace('_', ' ', $a->type)),
                'date'  => $a->created_at->format('M d, h:i A'),
            ]);

        return view('backend.layouts.orders.show', compact('order', 'activityChart'));
    }

    /**
     * Export orders CSV
     */
    public function export(Request $request)
    {
        try {
            $query = Order::with(['gig', 'buyer.profile', 'seller.profile']);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $orders   = $query->get();
            $filename = 'orders_' . date('Y-m-d_His') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($orders) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Order #', 'Gig', 'Buyer', 'Seller', 'Price', 'Platform Fee', 'Seller Earnings', 'Status', 'Payment Method', 'Created', 'Completed']);
                foreach ($orders as $o) {
                    $bp = $o->buyer?->profile;
                    $sp = $o->seller?->profile;
                    fputcsv($file, [
                        $o->order_number,
                        $o->gig?->title ?? 'N/A',
                        $bp ? trim($bp->first_name . ' ' . ($bp->last_name ?? '')) : ($o->buyer?->email ?? 'N/A'),
                        $sp ? trim($sp->first_name . ' ' . ($sp->last_name ?? '')) : ($o->seller?->email ?? 'N/A'),
                        number_format($o->price, 2),
                        number_format($o->platform_fee, 2),
                        number_format($o->seller_earnings, 2),
                        ucfirst(str_replace('_', ' ', $o->status)),
                        $o->payment_method ?? 'N/A',
                        $o->created_at->format('Y-m-d H:i:s'),
                        $o->completed_at ? $o->completed_at->format('Y-m-d H:i:s') : 'N/A',
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            Log::error('Order export error: ' . $e->getMessage());
            return back()->with('error', 'Failed to export orders');
        }
    }
}
