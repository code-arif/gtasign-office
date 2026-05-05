<?php

namespace App\Http\Controllers\Web\Backend\Order;

use App\Http\Controllers\Controller;
use App\Models\ExtensionRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ExtensionRequestController extends Controller
{
    /**
     * Extension request list
     */
    public function index()
    {
        $stats = [
            'total_requests'   => ExtensionRequest::count(),
            'pending_requests' => ExtensionRequest::where('status', 'pending')->count(),
            'approved_requests' => ExtensionRequest::where('status', 'approved')->count(),
            'rejected_requests' => ExtensionRequest::where('status', 'rejected')->count(),
        ];

        return view('backend.layouts.extension-requests.index', compact('stats'));
    }

    /**
     * DataTables
     */
    public function getData(Request $request)
    {
        if ($request->ajax() && $request->wantsJson()) {

            $query = ExtensionRequest::query()
                ->with([
                    'order:id,order_number',
                    'requester:id',
                    'requester.profile:id,user_id,first_name,last_name,avatar',
                ]);

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Date range
            if ($request->filled('date_from')) {
                $query->whereDate('requested_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('requested_at', '<=', $request->date_to);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('order', fn($oq) => $oq->where('order_number', 'like', "%{$search}%"))
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhereHas('requester.profile', fn($pq) =>
                        $pq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"));
                });
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('order_info', function ($ext) {
                    return '<div class="fw-semibold">#' . e($ext->order?->order_number ?? 'N/A') . '</div>';
                })
                ->addColumn('requested_by', function ($ext) {
                    $p = $ext->requester?->profile;
                    $name = $p ? trim($p->first_name . ' ' . ($p->last_name ?? '')) : ($ext->requester?->email ?? 'N/A');
                    $avatar = $p?->avatar
                        ? asset($p->avatar)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=32&background=6366f1&color=fff';
                    return '
                        <div class="d-flex align-items-center gap-2">
                            <img src="' . $avatar . '" class="rounded-circle flex-shrink-0"
                                 width="28" height="28" style="object-fit:cover;" alt="">
                            <small class="text-truncate" style="max-width:100px;">' . e($name) . '</small>
                        </div>';
                })
                ->addColumn('extension_days', function ($ext) {
                    return '<div class="text-center fw-bold text-warning">+' . $ext->additional_days . ' days</div>';
                })
                ->addColumn('reason', function ($ext) {
                    return '<small class="text-muted text-truncate d-block" style="max-width:200px;" title="' . e($ext->reason) . '">'
                        . e(Str::limit($ext->reason, 40)) . '</small>';
                })
                ->addColumn('status', function ($ext) {
                    $map = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
                    return '<span class="badge bg-' . ($map[$ext->status] ?? 'secondary') . ' p-2">'
                        . ucfirst($ext->status) . '</span>';
                })
                ->addColumn('requested_at', function ($ext) {
                    return '<small>' . $ext->requested_at->format('M d, Y') . '<br>'
                        . $ext->requested_at->format('h:i A') . '</small>';
                })
                ->addColumn('action', function ($ext) {
                    $viewBtn = '<a href="' . route('admin.extension-requests.show', $ext->id) . '"
                        class="btn btn-primary btn-sm me-1" title="View Details">
                        <i class="fe fe-eye"></i>
                    </a>';

                    $actionBtn = '';
                    // if ($ext->status === 'pending') {
                    //     $actionBtn = '
                    //     <div class="btn-group">
                    //         <button type="button" class="btn btn-success btn-sm"
                    //                 onclick="approveExtension(' . $ext->id . ')" title="Approve">
                    //             <i class="fe fe-check"></i>
                    //         </button>
                    //         <button type="button" class="btn btn-danger btn-sm"
                    //                 onclick="rejectExtension(' . $ext->id . ')" title="Reject">
                    //             <i class="fe fe-x"></i>
                    //         </button>
                    //     </div>';
                    // }

                    return '<div class="d-flex">' . $viewBtn . $actionBtn . '</div>';
                })
                ->rawColumns(['order_info', 'requested_by', 'extension_days', 'reason', 'status', 'requested_at', 'action'])
                ->make(true);
        }
    }

    /**
     * Show extension request
     */
    public function show($id)
    {
        $extension = ExtensionRequest::with([
            'order.gig',
            'order.buyer.profile',
            'order.seller.profile',
            'requester.profile',
        ])
            ->findOrFail($id);

        return view('backend.layouts.extension-requests.show', compact('extension'));
    }

    /**
     * Approve extension
     */
    public function approve(Request $request, $id)
    {
        try {
            $extension = ExtensionRequest::findOrFail($id);

            if ($extension->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Extension request already processed',
                ], 422);
            }

            DB::beginTransaction();

            $extension->update([
                'status'       => 'approved',
                'responded_at' => now(),
            ]);

            // Update order expected delivery
            if ($extension->order->expected_delivery_at) {
                $extension->order->update([
                    'expected_delivery_at' => $extension->order->expected_delivery_at->addDays($extension->additional_days),
                ]);
            }

            // Log activity
            $extension->order->activities()->create([
                'user_id'     => auth()->id(),
                'type'        => 'extension_approved',
                'description' => "Extension request approved: +{$extension->additional_days} days added",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Extension request approved successfully',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Extension approve error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve extension: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject extension
     */
    public function reject(Request $request, $id)
    {
        try {
            $extension = ExtensionRequest::findOrFail($id);

            if ($extension->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Extension request already processed',
                ], 422);
            }

            DB::beginTransaction();

            $extension->update([
                'status'       => 'rejected',
                'responded_at' => now(),
            ]);

            // Log activity
            $extension->order->activities()->create([
                'user_id'     => auth()->id(),
                'type'        => 'extension_rejected',
                'description' => "Extension request rejected",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Extension request rejected',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Extension reject error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject extension: ' . $e->getMessage(),
            ], 500);
        }
    }
}
