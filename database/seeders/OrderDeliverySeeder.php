<?php

namespace Database\Seeders;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderDeliverySeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local')) {
            $this->command->warn('OrderDeliverySeeder skipped — not in local.');
            return;
        }

        // Only orders that can actually have deliveries
        $orders = Order::whereIn('status', [
            'active',
            'qa_pending',
            'delivered',
            'completed',
            'revision_requested'
        ])->get();

        if ($orders->isEmpty()) {
            $this->command->error('No eligible orders found.');
            return;
        }

        $statuses = [
            'pending_qa',
            'qa_approved',
            'qa_rejected',
            'revision_requested',
            'delivered_to_client',
            'accepted'
        ];

        $deliveries = [];

        foreach ($orders->take(35) as $order) {

            $submittedAt = $order->started_at
                ? Carbon::parse($order->started_at)->addDays(rand(1, $order->delivery_days))
                : now()->subDays(rand(2, 15));

            $status = collect($statuses)->random();

            $deliveries[] = [
                'order_id' => $order->id,
                'delivery_number' => 1,
                'message' => 'Work has been completed as per requirements.',
                'files' => json_encode([
                    'deliverables/'.Str::random(10).'.zip'
                ]),
                'status' => $status,

                'revision_reason' => $status === 'revision_requested'
                    ? 'Please adjust color palette and spacing.'
                    : null,

                'qa_feedback' => $status === 'qa_rejected'
                    ? 'Quality standards not met.'
                    : null,

                'submitted_at' => $submittedAt,
                'qa_reviewed_at' => in_array($status, ['qa_approved','qa_rejected'])
                    ? $submittedAt->copy()->addHours(rand(6,18))
                    : null,

                'delivered_to_client_at' => $status === 'delivered_to_client'
                    ? $submittedAt->copy()->addDay()
                    : null,

                'client_reviewed_at' => $status === 'accepted'
                    ? $submittedAt->copy()->addDays(2)
                    : null,

                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('order_deliveries')->insert($deliveries);

        $this->command->info('Order Deliveries Seeded Successfully.');
    }
}
