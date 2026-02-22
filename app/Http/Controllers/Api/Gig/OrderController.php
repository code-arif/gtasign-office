<?php

namespace App\Http\Controllers\Api\Gig;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inbox\OrderInboxResource;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Services\Order\OrderService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(private OrderService $orderService) {}

    /**
     * Get authenticated user's order list.
     * Role-aware: client gets their purchases, expert gets their sales.
     * GET /api/v1/orders
     *
     */
    public function index(Request $request)
    {
        try {
            $user    = auth('api')->user();
            $perPage = $request->input('per_page', 15);
            $status  = $request->input('status');

            // Determine role: expert sees seller orders, client sees buyer orders
            $isExpert = $user->hasRole('expert');

            $query = Order::with([
                'gig:id,title,price',
                'buyer.profile:id,user_id,first_name,last_name,avatar,username',
                'seller.profile:id,user_id,first_name,last_name,avatar,username',
            ]);
            // $query = Order::with([
            //     'gig:id,title,price',
            //     'buyer.profile:id,user_id,first_name,last_name,avatar,username',
            //     'seller.profile:id,user_id,first_name,last_name,avatar,username',
            //     'latestDelivery:id,order_id,delivery_number,status,submitted_at',
            // ]);

            // Scope to user's orders
            if ($isExpert) {
                $query->where('seller_id', $user->id);
            } else {
                $query->where('buyer_id', $user->id);
            }

            // Status filter
            if ($status) {
                $query->where('status', $status);
            }

            $orders = $query->latest()->paginate($perPage);

            return $this->success('Orders retrieved successfully', [
                'orders' => OrderResource::collection($orders),
                'pagination' => [
                    'total'        => $orders->total(),
                    'per_page'     => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page'    => $orders->lastPage(),
                ],
                // Counts per status for tab badges on frontend
                'counts' => $this->getStatusCounts($user->id, $isExpert),
            ]);
        } catch (Exception $e) {
            Log::error('Order list error: ' . $e->getMessage());
            return $this->error(null, 'Failed to retrieve orders', 500);
        }
    }


    public function MySellerOrders(Request $request, int $buyerId)
    {
        try {
            $sellerId = auth()->id(); // logged-in seller
            $perPage  = $request->input('per_page', 10);
            $status  = $request->input('status');

            $orders = $this->orderService->getSellerOrdersWithBuyer(
                $sellerId,
                $buyerId,
                $perPage,
                $status,
            );

            return $this->success('Orders retrieved successfully', [
                'orders' => OrderResource::collection($orders),
                'pagination' => [
                    'total'        => $orders->total(),
                    'per_page'     => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page'    => $orders->lastPage(),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Get seller orders error: ' . $e->getMessage());
            return $this->error(null, 'Failed to retrieve orders', 500);
        }
    }


    /**
     * Count orders per status for the authenticated user.
     * Useful for sidebar/tab badges.
     */
    private function getStatusCounts(int $userId, bool $isExpert): array
    {
        $column = $isExpert ? 'seller_id' : 'buyer_id';

        $counts = Order::where($column, $userId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Always return all keys so frontend doesn't need null checks
        $statuses = [
            'pending_payment',
            'active',
            'qa_pending',
            'qa_rejected',
            'delivered',
            'revision_requested',
            'completed',
            'cancelled',
            'disputed'
        ];

        $result = ['all' => array_sum($counts)];
        foreach ($statuses as $s) {
            $result[$s] = $counts[$s] ?? 0;
        }

        return $result;
    }
}
