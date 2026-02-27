<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\User;
use App\Models\Gig;
use Carbon\Carbon;

class OrderReviewSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::inRandomOrder()->take(30)->get();

        foreach ($orders as $order) {

            // Reviewer (Buyer)
            $reviewerId = $order->buyer_id ?? User::inRandomOrder()->first()->id;

            // Reviewed User (Seller)
            $reviewedUserId = $order->seller_id ?? User::where('id', '!=', $reviewerId)->inRandomOrder()->first()->id;

            $rating = rand(3, 5);

            DB::table('order_reviews')->insert([
                'order_id'            => $order->id,
                'reviewer_id'         => $reviewerId,
                'reviewed_user_id'    => $reviewedUserId,
                'gig_id'              => $order->gig_id ?? Gig::inRandomOrder()->first()->id,

                'rating'              => $rating,
                'communication_rating' => rand(3, 5),
                'service_rating'      => rand(3, 5),
                'delivery_rating'     => rand(3, 5),

                'review' => fake()->randomElement([
                    'Outstanding experience! Highly recommended.',
                    'Very professional and delivered on time.',
                    'Communication was smooth and efficient.',
                    'Great service, will order again.',
                    'Impressive work quality and fast delivery.',
                    'Seller exceeded my expectations.',
                    'Very responsive and cooperative.',
                    'Amazing job! Everything was perfect.',
                    'Good work overall, satisfied with the result.',
                    'Fast delivery and excellent communication.'
                ]),

                'seller_reply' => rand(0, 1)
                    ? fake()->randomElement([
                        'Thank you so much for your kind words!',
                        'It was a pleasure working with you.',
                        'Looking forward to working again.',
                        'Appreciate your feedback!',
                        'Thanks for trusting my service.'
                    ])
                    : null,

                'is_public'  => rand(0, 1),
                'replied_at' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 10)) : null,

                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ]);
        }
    }
}
