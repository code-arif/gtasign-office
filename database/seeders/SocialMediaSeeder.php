<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class SocialMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $socialMedias = [
            ['social_media' => 'WhatsApp', 'profile_link' => 'https://www.whatsapp.com', 'social_media_icon' => null, 'created_at' => now(), 'updated_at' => now()],
            ['social_media' => 'LinkedIn', 'profile_link' => 'https://www.linkedin.com/company','social_media_icon' => null, 'created_at' => now(), 'updated_at' => now()],
            ['social_media' => 'Facebook', 'profile_link' => 'https://www.facebook.com','social_media_icon' => null, 'created_at' => now(), 'updated_at' => now()],
            ['social_media' => 'Twitter', 'profile_link' => 'https://www.twitter.com','social_media_icon' => null, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('social_media')->insert($socialMedias);
    }
}
