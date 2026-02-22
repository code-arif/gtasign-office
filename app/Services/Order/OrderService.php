<?php

namespace App\Services\Order;

use App\Models\Order;

class OrderService
{
    public function getSellerOrdersWithBuyer(int $sellerId, int $buyerId, int $perPage = 10)
    {
        return Order::with([
            'buyer.profile',
            'seller.profile',
            'gig',
            'room',
        ])
            ->where('seller_id', $sellerId)
            ->where('buyer_id', $buyerId)
            ->latest()
            ->paginate($perPage);
    }
}
