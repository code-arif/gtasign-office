<?php

namespace App\Http\Controllers\Web\Backend\QA;


use Exception;
use App\Models\Order;
use App\Models\OrderQaReview;
use App\Models\OrderDelivery;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Gig\QaService;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class QaManageController extends Controller
{
    public function __construct(protected QaService $qaService) {}

    // ─────────────────────────────────────────────────────────────────
    // INDEX — Pending QA Reviews List
    // ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $totalPending  = OrderQaReview::where('status', 'pending')->count();
        $totalApproved = OrderQaReview::where('status', 'approved')->count();
        $totalRejected = OrderQaReview::where('status', 'rejected')->count();
        $totalAll      = OrderQaReview::count();

        return view('backend.layouts.qa.index', compact(
            'totalPending',
            'totalApproved',
            'totalRejected',
            'totalAll'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // DATATABLE DATA
    // ─────────────────────────────────────────────────────────────────

    public function getData(Request $request)
    {
        if (!$request->ajax() || !$request->wantsJson()) {
            abort(403);
        }

        $query = OrderQaReview::with([
            'order.gig:id,title,price',
            'order.buyer.profile:id,user_id,first_name,last_name,username,avatar',
            'order.seller.profile:id,user_id,first_name,last_name,username,avatar',
            'delivery:id,order_id,delivery_number,files,submitted_at',
            'reviewer.profile:id,user_id,first_name,last_name',
        ])->orderBy('id', 'desc');

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('submitted_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('submitted_at', '<=', $request->date_to);
        }

        // Search by order number or seller name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', fn($oq) => $oq->where('order_number', 'like', "%{$search}%"))
                    ->orWhereHas('order.seller.profile', fn($pq) =>
                        $pq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                    );
            });
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('order_info', function ($review) {
                $order  = $review->order;
                $num    = $order?->order_number ?? '—';
                $title  = \Str::limit($order?->gig?->title ?? 'Custom Order', 30);
                $price  = '$' . number_format($order?->price ?? 0, 2);

                return '
                    <div>
                        <div class="fw-semibold">#' . e($num) . '</div>
                        <small class="text-muted d-block" title="' . e($order?->gig?->title) . '">' . e($title) . '</small>
                        <small class="text-success fw-600">' . $price . '</small>
                    </div>';
            })
            ->addColumn('seller_info', function ($review) {
                $profile  = $review->order?->seller?->profile;
                $name     = $profile ? trim($profile->first_name . ' ' . ($profile->last_name ?? '')) : '—';
                $username = $profile?->username ?? '';
                $avatar   = $profile?->avatar
                    ? asset('storage/' . $profile->avatar)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=6366f1&color=fff&size=40';

                return '
                    <div class="d-flex align-items-center gap-2">
                        <img src="' . $avatar . '" class="rounded-circle" width="36" height="36"
                             style="object-fit:cover;border:2px solid #e9ecef;">
                        <div>
                            <div class="fw-semibold small">' . e($name) . '</div>
                            <small class="text-muted">@' . e($username) . '</small>
                        </div>
                    </div>';
            })
            ->addColumn('delivery_info', function ($review) {
                $delivery = $review->delivery;
                if (!$delivery) return '<span class="text-muted">—</span>';

                $fileCount  = count($delivery->files ?? []);
                $deliveryNo = $delivery->delivery_number ?? 1;

                return '
                    <div>
                        <span class="badge bg-secondary">Delivery #' . $deliveryNo . '</span>
                        <div class="mt-1">
                            <small class="text-muted">
                                <i class="fe fe-paperclip me-1"></i>' . $fileCount . ' file(s)
                            </small>
                        </div>
                        <small class="text-muted">
                            ' . ($delivery->submitted_at ? \Carbon\Carbon::parse($delivery->submitted_at)->format('M d, Y') : '—') . '
                        </small>
                    </div>';
            })
            ->addColumn('status', function ($review) {
                $map = [
                    'pending'  => ['warning', 'clock', 'Pending'],
                    'approved' => ['success', 'check-circle', 'Approved'],
                    'rejected' => ['danger',  'x-circle', 'Rejected'],
                ];
                [$color, $icon, $label] = $map[$review->status] ?? ['secondary', 'help-circle', ucfirst($review->status)];

                $reviewedBy = $review->reviewer?->profile
                    ? trim($review->reviewer->profile->first_name . ' ' . ($review->reviewer->profile->last_name ?? ''))
                    : null;

                return '
                    <div>
                        <span class="badge bg-' . $color . ' p-1 d-inline-flex align-items-center">
                            <i class="fe fe-' . $icon . ' me-1"></i>' . $label . '
                        </span>
                        ' . ($reviewedBy ? '<div class="mt-1"><small class="text-muted">by ' . e($reviewedBy) . '</small></div>' : '') . '
                    </div>';
            })
            ->addColumn('submitted_at', function ($review) {
                $submitted = $review->submitted_at
                    ? \Carbon\Carbon::parse($review->submitted_at)
                    : $review->created_at;

                return '
                    <small>' . $submitted->format('M d, Y') . '<br>
                    <span class="text-muted">' . $submitted->format('h:i A') . '</span><br>
                    <span class="text-muted">' . $submitted->diffForHumans() . '</span>
                    </small>';
            })
            ->addColumn('action', function ($review) {
                $viewBtn = '
                    <a href="' . route('admin.qa.show', $review->id) . '"
                       class="btn btn-primary btn-sm me-1" title="Review">
                        <i class="fe fe-eye"></i>
                    </a>';

                $quickActions = '';
                if ($review->status === 'pending') {
                    $quickActions = '
                        <button onclick="quickApprove(' . $review->id . ')"
                                class="btn btn-success btn-sm me-1" title="Quick Approve">
                            <i class="fe fe-check"></i>
                        </button>
                        <button onclick="openRejectModal(' . $review->id . ')"
                                class="btn btn-danger btn-sm" title="Reject">
                            <i class="fe fe-x"></i>
                        </button>';
                }

                return '<div class="d-flex">' . $viewBtn . $quickActions . '</div>';
            })
            ->rawColumns(['order_info', 'seller_info', 'delivery_info', 'status', 'submitted_at', 'action'])
            ->make(true);
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW — Full Review Detail Page
    // ─────────────────────────────────────────────────────────────────

    public function show(int $reviewId)
    {
        $review = OrderQaReview::with([
            'order.gig',
            'order.buyer.profile',
            'order.seller.profile',
            'order.activities',
            'order.extensionRequests.requester.profile',
            'delivery',
            'reviewer.profile',
        ])->findOrFail($reviewId);

        $order    = $review->order;
        $delivery = $review->delivery;

        // All deliveries for this order (history)
        $allDeliveries = OrderDelivery::where('order_id', $order->id)
            ->orderBy('delivery_number')
            ->get();

        // Previous QA reviews for context
        $previousReviews = OrderQaReview::where('order_id', $order->id)
            ->where('id', '!=', $reviewId)
            ->with('reviewer.profile')
            ->latest()
            ->get();

        return view('backend.layouts.qa.show', compact(
            'review',
            'order',
            'delivery',
            'allDeliveries',
            'previousReviews'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // APPROVE
    // ─────────────────────────────────────────────────────────────────

    public function approve(Request $request, int $reviewId)
    {
        try {
            $request->validate([
                'feedback' => 'nullable|string|max:1000',
            ]);

            $admin  = auth()->user();
            $review = $this->qaService->approveDelivery($reviewId, $admin->id, $request->feedback);

            return response()->json([
                'success' => true,
                'message' => 'QA delivery approved and sent to client successfully.',
                'review'  => [
                    'id'     => $review->id,
                    'status' => $review->status,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('QA approve error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getMessage() === 'QA review not found' ? 404 : 400);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // REJECT
    // ─────────────────────────────────────────────────────────────────

    public function reject(Request $request, int $reviewId)
    {
        try {
            $request->validate([
                'feedback' => 'required|string|max:2000',
                'issues'   => 'nullable|array',
                'issues.*' => 'string|max:200',
            ]);

            $admin  = auth()->user();
            $review = $this->qaService->rejectDelivery(
                $reviewId,
                $admin->id,
                $request->feedback,
                $request->input('issues', [])
            );

            return response()->json([
                'success' => true,
                'message' => 'Delivery rejected. Expert has been notified.',
                'review'  => [
                    'id'     => $review->id,
                    'status' => $review->status,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('QA reject error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
