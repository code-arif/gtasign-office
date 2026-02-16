<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GigImageSeeder extends Seeder
{
    public function run(): void
    {
        for ($gigId = 1; $gigId <= 10; $gigId++) {

            // Fiverr style gallery
            for ($i = 1; $i <= 3; $i++) {

                DB::table('gig_images')->insert([
                    'gig_id' => $gigId,
                    'path' => 'default/gig.jpg', // default image
                    'is_primary' => $i === 1, // first image is primary
                    'sort_order' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
