<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CampTableSeeder extends Seeder
{
    public function run(): void
    {
        // Get all director user IDs (Spatie role based)
        $directorIds = DB::table('model_has_roles')
            ->where('role_id', 2) // director role_id
            ->pluck('model_id')
            ->toArray();

        if (empty($directorIds)) {
            return;
        }

        $camps = [];

        for ($i = 1; $i <= 9; $i++) {

            // Fixed date range
            $startDate = Carbon::create(2026, rand(1, 2), rand(1, 28));
            $endDate   = (clone $startDate)->addDays(rand(3, 10));

            $sportsTypeId = rand(1, 5);

            $camps[] = [
                'director_id'      => $directorIds[array_rand($directorIds)],
                'sports_type_id'   => $sportsTypeId,
                'sports_type_name' => 'Sports Type ' . $sportsTypeId,
                'camp_name'        => 'Training Camp ' . $i,
                'camp_logo'        => null,
                'location'         => 'City ' . rand(1, 20),
                'latitude'         => rand(20000000, 50000000) / 1000000,
                'longitude'        => rand(70000000, 120000000) / 1000000,
                'start_date'       => $startDate->format('Y-m-d'),
                'end_date'         => $endDate->format('Y-m-d'),
                'camp_details'     => 'This is a detailed description for training camp ' . $i . '.',
                'price'            => rand(100, 500),
                'status'           => 'active',
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        DB::table('camps')->insert($camps);
    }
}
