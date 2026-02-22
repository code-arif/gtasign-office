<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local')) {
            $this->command->warn('OrderSeeder skipped — not in local environment.');
            return;
        }

        // Get all experts (sellers)
        $experts = User::role('expert', 'web')->pluck('id');

        // Get all clients (buyers)
        $clients = User::role('client', 'web')->pluck('id');

        if ($experts->isEmpty() || $clients->isEmpty()) {
            $this->command->error('Experts or Clients not found. Run UserSeeder first.');
            return;
        }

        $statuses = [
            'pending_payment',
            'active',
            'qa_pending',
            'delivered',
            'revision_requested',
            'completed',
        ];

        $orders = [];

        for ($i = 1; $i <= 50; $i++) {

            $buyerId  = $clients->random();
            $sellerId = $experts->random();

            $price = rand(50, 500);

            $platformFee = round($price * 0.10, 2);
            $sellerEarnings = $price - $platformFee;

            $deliveryDays = rand(2, 10);

            $createdAt = now()->subDays(rand(0, 30));

            $status = collect($statuses)->random();

            $orders[] = [
                'order_number' => strtoupper(Str::random(10)),

                'gig_id' => null,
                'custom_offer_id' => null,
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
                'room_id' => 1, // adjust if you generate rooms dynamically

                'price' => $price,
                'platform_fee' => $platformFee,
                'seller_earnings' => $sellerEarnings,

                'delivery_days' => $deliveryDays,
                'expected_delivery_at' => $createdAt->copy()->addDays($deliveryDays),

                'max_revisions' => rand(1, 3),
                'revision_count' => rand(0, 1),

                'requirements' => 'Please follow brand guidelines and deliver high quality work.',

                'status' => $status,

                'payment_method' => 'stripe',
                'payment_intent_id' => Str::random(20),
                'stripe_checkout_session_id' => Str::random(20),
                'paid_at' => $createdAt,

                'funds_in_escrow' => true,
                'escrow_released_at' => $status === 'completed'
                    ? $createdAt->copy()->addDays($deliveryDays + 2)
                    : null,

                'auto_complete_enabled' => true,
                'auto_complete_at' => $createdAt->copy()->addDays($deliveryDays + 3),

                'started_at' => $createdAt,
                'delivered_at' => in_array($status, ['delivered','completed']) ? now() : null,
                'completed_at' => $status === 'completed' ? now() : null,

                'created_at' => $createdAt,
                'updated_at' => now(),
            ];
        }

        DB::table('orders')->insert($orders);

        $this->command->info('50 Dummy Orders Created Successfully.');
    }
}
