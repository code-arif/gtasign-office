<?php

namespace App\Console\Commands;

use App\Models\Chat;
use App\Models\Order;
use App\Models\OrderActivity;
use App\Services\Payment\EscrowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Auto-completes orders that have been delivered but not responded to by client
 * within the configured days (default: 3 days after delivery).
 *
 * Schedule: daily
 */
class AutoCompleteOrders extends Command
{
    protected $signature   = 'orders:auto-complete';
    protected $description = 'Auto-complete orders that passed their auto-completion deadline';

    public function __construct(protected EscrowService $escrowService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Running auto-complete orders...');

        $orders = Order::with(['room', 'latestDelivery', 'earning', 'seller'])
            ->where('status', 'delivered')
            ->where('auto_complete_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            DB::beginTransaction();
            try {
                // Complete the delivery
                if ($order->latestDelivery) {
                    $order->latestDelivery->update([
                        'status'            => 'accepted',
                        'client_reviewed_at' => now(),
                    ]);
                }

                // Complete the order
                $order->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);

                // Start 14-day clearing period
                $this->escrowService->startClearingPeriod($order->id);

                // System message
                Chat::create([
                    'sender_id'   => $order->buyer_id,
                    'receiver_id' => $order->seller_id,
                    'room_id'     => $order->room_id,
                    'type'        => 'order_completed',
                    'order_id'    => $order->id,
                    'text'        => "⏱️ Order #{$order->order_number} was auto-completed. Funds released to seller.",
                ]);

                $order->room->update(['last_message_at' => now(), 'has_active_order' => false]);

                OrderActivity::create([
                    'order_id'    => $order->id,
                    'user_id'     => null,
                    'type'        => 'auto_completed',
                    'description' => 'Order auto-completed after delivery deadline',
                ]);

                DB::commit();
                $count++;

                $this->info("Auto-completed order #{$order->order_number}");
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Auto-complete failed for order #{$order->id}: " . $e->getMessage());
                $this->error("Failed: order #{$order->id}: " . $e->getMessage());
            }
        }

        $this->info("Auto-completed {$count} order(s).");
        return self::SUCCESS;
    }
}
