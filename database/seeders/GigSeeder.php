<?php

namespace Database\Seeders;

use App\Models\Gig;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class GigSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $statuses = ['draft', 'pending_approval', 'active', 'rejected'];

        for ($i = 1; $i <= 10; $i++) {

            $status = $faker->randomElement($statuses);

            Gig::create([
                'user_id' => $faker->numberBetween(1, 5),
                'category_id' => $faker->numberBetween(1, 3),
                'sub_category_id' => $faker->optional()->numberBetween(4, 6),

                'title' => $faker->sentence(6),
                'scope' => $faker->paragraph(5),

                'price' => $faker->randomFloat(2, 50, 500),
                'delivery_days' => $faker->numberBetween(1, 10),

                // Now pass array (Eloquent will convert to JSON)
                'system_questions' => [
                    [
                        'question' => 'Do you have brand guidelines?',
                        'type' => 'text'
                    ],
                    [
                        'question' => 'Preferred color?',
                        'type' => 'text'
                    ]
                ],

                'custom_questions' => [
                    [
                        'question' => $faker->sentence(4),
                        'required' => true
                    ]
                ],

                'status' => $status,
                'rejection_reason' => $status === 'rejected'
                    ? 'Quality does not meet marketplace standards.'
                    : null,

                'impressions' => $faker->numberBetween(10, 1000),
                'clicks' => $faker->numberBetween(5, 300),
                'orders' => $faker->numberBetween(0, 50),
                'cancellations' => $faker->numberBetween(0, 5),

                'published_at' => $status === 'active'
                    ? now()->subDays(rand(1, 30))
                    : null,

                'is_agreed' => true,
            ]);
        }
    }
}
