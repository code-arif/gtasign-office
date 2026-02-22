<?php

namespace App\Services\Order;

use App\Models\Order;

class OrderService
{
    public function getSellerOrdersWithBuyer(
        int $sellerId,
        int $buyerId,
        int $perPage = 10,
        ?string $status = null
    ) {
        $query = Order::with([
            'buyer.profile',
            'seller.profile',
            'gig.primaryImage',
            'room',
        ])
            ->where('seller_id', $sellerId)
            ->where('buyer_id', $buyerId);

        /**
         * Apply Status Filter Only When Provided
         */
        if (!empty($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        return $query->latest()->paginate($perPage);
    }
}
