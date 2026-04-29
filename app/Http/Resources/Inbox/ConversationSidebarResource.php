<?php

namespace App\Http\Resources\Inbox;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationSidebarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $room = $this->resource['room'];
        $otherUser = $this->resource['other_user'];

        // 1. About
        $about = [
            'name' => trim($otherUser->profile?->first_name . ' ' . $otherUser->profile?->last_name) ?: $otherUser->profile?->username,
            'from' => $otherUser->profile?->address,
            'joined' => $otherUser->created_at ? $otherUser->created_at->format('M, Y') : null,
        ];

        // 2. Activities
        $orderCompleted = Order::where(function($q) use ($otherUser) {
            $q->where('seller_id', $otherUser->id)
              ->orWhere('buyer_id', $otherUser->id);
        })->where('status', 'completed')->count();

        $orderWithYou = Order::where('room_id', $room->id)->count();

        $activities = [
            'order_completed' => $orderCompleted,
            'order_with_you' => $orderWithYou,
        ];

        // 3. Project Delivery (Active Order)
        $activeOrder = Order::where('room_id', $room->id)
            ->whereIn('status', ['active', 'qa_rejected', 'qa_pending', 'delivered'])
            ->latest('id')
            ->first();

        $projectDelivery = null;
        if ($activeOrder) {
            $timeLeft = null;
            if ($activeOrder->expected_delivery_at) {
                if ($activeOrder->isLate()) {
                    $timeLeft = 'Late';
                } else {
                    $diff = now()->diff($activeOrder->expected_delivery_at);
                    $timeLeft = $diff->format('%dd, %hh, %im');
                }
            }

            $projectDelivery = [
                'order_id' => $activeOrder->id,
                'status' => $activeOrder->status,
                'time_left' => $timeLeft,
                'can_extend_time' => $activeOrder->canRequestExtension(),
                'can_send_to_qa' => $activeOrder->canSubmitToQa(),
            ];
        }

        return [
            'about' => $about,
            'activities' => $activities,
            'project_delivery' => $projectDelivery,
        ];
    }
}
