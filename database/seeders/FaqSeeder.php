<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    public function run()
    {
        DB::table('faqs')->insert([
            [
                'question' => 'How do I list a product for sale?',
                'answer' => 'Go to the "Add Listing" page, fill in the required fields, and submit your listing for admin approval.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'What fees does the platform charge?',
                'answer' => 'A 10% service charge applies to each successful sale.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'How do I check the status of my listing?',
                'answer' => 'Visit your dashboard to see the status of your listings, including pending approvals or active products.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'How do buyers contact me?',
                'answer' => 'Buyers can use the contact form provided on your product page to reach out directly.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'How do I request support for a specific issue?',
                'answer' => 'Use the contact options provided below to submit a ticket or reach out to our team.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
