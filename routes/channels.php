<?php

use App\Models\Order;
use App\Models\Room;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| PRIVATE CHANNEL: inbox.room.{roomId}
| — For all inbox messages (text, offer, order events)
|--------------------------------------------------------------------------
*/

Broadcast::channel('inbox.room.{roomId}', function ($user, $roomId) {
    $room = Room::find($roomId);
    if (!$room) return false;
    return $user->id === $room->first_user_id || $user->id === $room->second_user_id;
});

/*
|--------------------------------------------------------------------------
| PRIVATE CHANNEL: order.{orderId}
| — order status, delivery, extension, revision events
|--------------------------------------------------------------------------
*/
Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);
    if (!$order) return false;
    return $user->id === $order->buyer_id || $user->id === $order->seller_id;
});

/*
|--------------------------------------------------------------------------
| PRIVATE CHANNEL: user.{userId}
| — personal notifications (payment, escrow, admin actions)
|--------------------------------------------------------------------------
*/
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

/*
|--------------------------------------------------------------------------
| PRIVATE CHANNEL: inbox.list.{userId}
| — inbox sidebar: unread count, last message, room sorting
|--------------------------------------------------------------------------
*/
Broadcast::channel('inbox.list.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
