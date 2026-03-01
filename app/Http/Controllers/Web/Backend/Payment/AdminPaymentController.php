<?php

namespace App\Http\Controllers\Web\Backend\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SellerEarnings;
use App\Models\User;
use App\Models\WithdrawalRequests;
use App\Models\OrderActivity;
use App\Models\Chat;
use App\Services\Payment\EscrowService;
use App\Services\Payment\StripePaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class AdminPaymentController extends Controller
{
    public function __construct(
        protected EscrowService $escrowService,
        protected StripePaymentService $stripeService,
    ) {}

    // ─────────────────────────────────────────────────────────────────
    // VIEWS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Main payment dashboard
     * GET /admin/payments
     */
    public function index()
    {
        $stats = $this->getDashboardStats();
        return view('backend.layouts.payments.index', compact('stats'));
    }

    /**
     * Orders list with payment control
     * GET /admin/payments/orders
     */
    public function orders(Request $request)
    {
        if ($request->ajax()) {
            $orders = Order::with(['buyer.profile', 'seller.profile', 'gig', 'earning'])
                ->select('orders.*')
                ->latest();

            return DataTables::of($orders)
                ->addIndexColumn()
                ->addColumn('buyer_name', fn($o) => $o->buyer?->profile?->first_name . ' ' . $o->buyer?->profile?->last_name)
                ->addColumn('seller_name', fn($o) => $o->seller?->profile?->first_name . ' ' . $o->seller?->profile?->last_name)
                ->addColumn('earning_status', fn($o) => $o->earning?->status ?? 'no_record')
                ->addColumn('stripe_connected', function ($o) {
                    $accountId = $o->seller?->stripe_account_id ?? $o->seller?->profile?->stripe_account_id;
                    return $accountId ? true : false;
                })
                ->addColumn('action', function ($o) {
                    return view('backend.layouts.payments.partials.order-actions', compact('o'))->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return redirect()->route('admin.payments.index');
    }

    /**
     * Connected Stripe accounts list
     * GET /admin/payments/stripe-accounts
     */
    public function stripeAccounts(Request $request)
    {
        if ($request->ajax()) {
            $users = User::with('profile')
                ->whereRelation('profile', 'stripe_account_id', '!=', null)
                ->select('users.*');

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('full_name', fn($u) => $u->profile?->first_name . ' ' . $u->profile?->last_name)
                ->addColumn('username', fn($u) => $u->profile?->username ?? '—')
                ->addColumn('stripe_account', fn($u) => $u->profile?->stripe_account_id)
                ->addColumn('onboarded_at', fn($u) => $u->profile?->stripe_onboarded_at?->format('d M Y') ?? '—')
                ->addColumn('available_balance', fn($u) => '$' . number_format($u->available_balance, 2))
                ->addColumn('pending_clearance', fn($u) => '$' . number_format($u->pending_clearance, 2))
                ->addColumn('stripe_status', function ($u) {
                    $accountId = $u->profile?->stripe_account_id;
                    try {
                        $ready = $this->stripeService->isConnectAccountReady($accountId);
                        return $ready
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-warning text-dark">Incomplete</span>';
                    } catch (Exception $e) {
                        return '<span class="badge bg-danger">Error</span>';
                    }
                })
                ->addColumn('action', function ($u) {
                    return view('backend.layouts.payments.partials.stripe-actions', compact('u'))->render();
                })
                ->rawColumns(['stripe_status', 'action'])
                ->make(true);
        }

        return redirect()->route('admin.payments.index');
    }

    /**
     * Withdrawals list
     * GET /admin/payments/withdrawals
     */
    public function withdrawals(Request $request)
    {
        if ($request->ajax()) {
            $withdrawals = WithdrawalRequests::with('seller.profile')->select('withdrawal_requests.*');

            return DataTables::of($withdrawals)
                ->addIndexColumn()
                ->addColumn('seller_name', fn($w) => $w->seller?->profile?->first_name . ' ' . $w->seller?->profile?->last_name)
                ->addColumn('action', function ($w) {
                    return view('backend.layouts.payments.partials.withdrawal-actions', compact('w'))->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return redirect()->route('admin.payments.index');
    }

    // ─────────────────────────────────────────────────────────────────
    // ACTIONS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Manually release escrow to expert (before 14 days)
     * POST /admin/payments/orders/{orderId}/release-escrow
     */
    public function releaseEscrow(Request $request, int $orderId)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::with(['seller', 'room', 'earning'])->findOrFail($orderId);

            $earning = $order->earning;

            if (!$earning) {
                return back()->with('error', 'No earning record found for this order.');
            }

            if (!in_array($earning->status, ['pending', 'clearing'])) {
                return back()->with('error', "Cannot release — earning is already '{$earning->status}'.");
            }

            // If still pending, move to clearing first
            if ($earning->status === 'pending') {
                $earning->update(['status' => 'clearing']);
                $order->seller->increment('pending_clearance', $earning->net_amount);
            }

            // Immediately make available (skip 14-day wait)
            $earning->update([
                'status'       => 'available',
                'available_at' => now(),
            ]);

            // Move from pending_clearance → available_balance
            $order->seller->decrement('pending_clearance', $earning->net_amount);
            $order->seller->increment('available_balance', $earning->net_amount);

            // Log activity
            OrderActivity::create([
                'order_id'    => $orderId,
                'user_id'     => auth()->id(),
                'type'        => 'escrow_released',
                'description' => "Admin manually released escrow. Reason: {$request->reason}",
                'metadata'    => ['admin_id' => auth()->id(), 'reason' => $request->reason],
            ]);

            // System chat message
            Chat::create([
                'sender_id'   => $order->buyer_id,
                'receiver_id' => $order->seller_id,
                'room_id'     => $order->room_id,
                'type'        => 'system',
                'order_id'    => $orderId,
                'text'        => "Admin released your earnings early for Order #{$order->order_number}. Funds are now available in your wallet.",
            ]);

            DB::commit();

            return back()->with('success', "Escrow released successfully for Order #{$order->order_number}. Expert's available balance updated.");
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Admin release escrow error: ' . $e->getMessage());
            return back()->with('error', 'Failed to release escrow: ' . $e->getMessage());
        }
    }

    /**
     * Manually cancel order and handle partial/full refund logic
     * POST /admin/payments/orders/{orderId}/cancel
     */
    public function cancelOrder(Request $request, int $orderId)
    {
        $request->validate([
            'reason'              => 'required|string|max:500',
            'refund_type'         => 'required|in:full,partial,none',
            'partial_amount'      => 'required_if:refund_type,partial|nullable|numeric|min:1',
            'expert_compensation' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::with(['seller', 'buyer', 'room', 'earning'])->findOrFail($orderId);

            $earning = $order->earning;
            $refundType = $request->refund_type;

            // Handle earning record
            if ($earning && in_array($earning->status, ['pending', 'clearing'])) {
                if ($refundType === 'full') {
                    // Full refund — expert gets nothing
                    $earning->update(['status' => 'refunded']);

                    // Remove from pending_clearance if was there
                    if ($earning->status === 'clearing') {
                        $order->seller->decrement('pending_clearance', $earning->net_amount);
                    }
                } elseif ($refundType === 'partial' && $request->expert_compensation > 0) {
                    // Partial — give expert their negotiated cut
                    $compensationAmount = min((float)$request->expert_compensation, $earning->net_amount);

                    $earning->update([
                        'status'       => 'available',
                        'net_amount'   => $compensationAmount,
                        'available_at' => now(),
                    ]);

                    $order->seller->increment('available_balance', $compensationAmount);

                    if ($earning->getOriginal('status') === 'clearing') {
                        $order->seller->decrement('pending_clearance', $earning->getOriginal('net_amount'));
                    }
                } else {
                    $earning->update(['status' => 'refunded']);
                }
            }

            // Cancel the order
            $order->update([
                'status'              => 'cancelled',
                'cancelled_at'        => now(),
                'cancelled_by'        => 'admin',
                'cancellation_reason' => $request->reason,
                'auto_complete_at'    => null,
            ]);

            // System messages
            Chat::create([
                'sender_id'   => $order->buyer_id,
                'receiver_id' => $order->seller_id,
                'room_id'     => $order->room_id,
                'type'        => 'order_cancelled',
                'order_id'    => $orderId,
                'text'        => "Order #{$order->order_number} was cancelled by Admin.\nReason: {$request->reason}",
                'metadata'    => ['refund_type' => $refundType, 'admin_id' => auth()->id()],
            ]);

            $order->room->update(['last_message_at' => now(), 'has_active_order' => false]);

            OrderActivity::create([
                'order_id'    => $orderId,
                'user_id'     => auth()->id(),
                'type'        => 'order_cancelled',
                'description' => "Admin cancelled order. Refund type: {$refundType}. Reason: {$request->reason}",
            ]);

            DB::commit();

            return back()->with('success', "Order #{$order->order_number} cancelled. Refund type: {$refundType}.");
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Admin cancel order error: ' . $e->getMessage());
            return back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    /**
     * Force transfer to expert via Stripe (bypass wallet withdrawal)
     * POST /admin/payments/orders/{orderId}/force-transfer
     */
    public function forceTransfer(Request $request, int $orderId)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::with(['seller.profile', 'earning'])->findOrFail($orderId);

            $earning = $order->earning;

            if (!$earning) {
                return back()->with('error', 'No earning record found.');
            }

            $stripeAccountId = $order->seller?->stripe_account_id
                ?? $order->seller?->profile?->stripe_account_id;

            if (!$stripeAccountId) {
                return back()->with('error', 'Expert has not connected a Stripe account. Cannot force transfer.');
            }

            if (!$this->stripeService->isConnectAccountReady($stripeAccountId)) {
                return back()->with('error', 'Expert Stripe account is not fully onboarded.');
            }

            $amountCents = (int)($earning->net_amount * 100);

            // Stripe Transfer
            $transfer = $this->stripeService->transferToExpert(
                $stripeAccountId,
                $amountCents,
                $orderId
            );

            // Mark earning as withdrawn
            $earning->update([
                'status'        => 'withdrawn',
                'withdrawn_at'  => now(),
            ]);

            // Deduct from available_balance if it was there
            if ($order->seller->available_balance >= $earning->net_amount) {
                $order->seller->decrement('available_balance', $earning->net_amount);
            }

            OrderActivity::create([
                'order_id'    => $orderId,
                'user_id'     => auth()->id(),
                'type'        => 'escrow_released',
                'description' => "Admin force-transferred \${$earning->net_amount} to expert via Stripe. Transfer ID: {$transfer->id}. Reason: {$request->reason}",
                'metadata'    => ['stripe_transfer_id' => $transfer->id, 'admin_id' => auth()->id()],
            ]);

            DB::commit();

            return back()->with('success', "\${$earning->net_amount} transferred to expert's Stripe account. Transfer ID: {$transfer->id}");
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Admin force transfer error: ' . $e->getMessage());
            return back()->with('error', 'Stripe transfer failed: ' . $e->getMessage());
        }
    }

    /**
     * Get order details (AJAX)
     * GET /admin/payments/orders/{orderId}/details
     */
    public function orderDetails(int $orderId)
    {
        try {
            $order = Order::with([
                'buyer.profile',
                'seller.profile',
                'gig',
                'earning',
                'deliveries',
                'activities',
            ])->findOrFail($orderId);

            $stripeAccountId = $order->seller?->stripe_account_id
                ?? $order->seller?->profile?->stripe_account_id;

            $stripeReady = false;
            if ($stripeAccountId) {
                try {
                    $stripeReady = $this->stripeService->isConnectAccountReady($stripeAccountId);
                } catch (Exception $e) {
                    $stripeReady = false;
                }
            }

            return response()->json([
                'success' => true,
                'order'   => $order,
                'stripe'  => [
                    'account_id' => $stripeAccountId,
                    'is_ready'   => $stripeReady,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    /**
     * Get Stripe dashboard link for expert
     * GET /admin/payments/stripe/{userId}/dashboard
     */
    public function expertStripeDashboard(int $userId)
    {
        try {
            $user = User::with('profile')->findOrFail($userId);
            $stripeAccountId = $user->stripe_account_id ?? $user->profile?->stripe_account_id;

            if (!$stripeAccountId) {
                return back()->with('error', 'This expert has no Stripe account connected.');
            }

            $dashboardUrl = $this->stripeService->getConnectDashboardLink($stripeAccountId);

            return redirect($dashboardUrl);
        } catch (Exception $e) {
            return back()->with('error', 'Failed to generate Stripe dashboard link: ' . $e->getMessage());
        }
    }

    /**
     * Get wallet summary for a specific expert (AJAX)
     * GET /admin/payments/expert/{userId}/wallet
     */
    public function expertWallet(int $userId)
    {
        try {
            $summary = $this->escrowService->getWalletSummary($userId);
            $user = User::with('profile')->findOrFail($userId);

            return response()->json([
                'success' => true,
                'user'    => [
                    'name'    => $user->profile?->first_name . ' ' . $user->profile?->last_name,
                    'email'   => $user->email,
                    'balance' => $summary,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────

    private function getDashboardStats(): array
    {
        return [
            'total_orders'         => Order::count(),
            'active_orders'        => Order::whereIn('status', ['active', 'qa_pending', 'qa_approved', 'delivered'])->count(),
            'completed_orders'     => Order::where('status', 'completed')->count(),
            'cancelled_orders'     => Order::where('status', 'cancelled')->count(),
            'total_revenue'        => Order::where('status', 'completed')->sum('price'),
            'platform_fees'        => Order::where('status', 'completed')->sum('platform_fee'),
            'in_escrow'            => SellerEarnings::whereIn('status', ['pending', 'clearing'])->sum('net_amount'),
            'available_to_experts' => SellerEarnings::where('status', 'available')->sum('net_amount'),
            'withdrawn'            => SellerEarnings::where('status', 'withdrawn')->sum('net_amount'),
            'pending_withdrawals'  => WithdrawalRequests::where('status', 'pending')->count(),
            // 'connected_experts'    => User::whereNotNull('stripe_account_id')
            //     ->orWhereHas('profile', fn($q) => $q->whereNotNull('stripe_account_id'))
            //     ->count(),
        ];
    }
}
