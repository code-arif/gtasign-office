<?php

namespace Database\Seeders;

use App\Models\SportsType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SportsTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SportsType::insert([
            [
                'sports_name' => 'Football',
                'icon'        => 'icons/football.png',
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'sports_name' => 'Basketball',
                'icon'        => 'icons/basketball.png',
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'sports_name' => 'Cricket',
                'icon'        => 'icons/cricket.png',
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'sports_name' => 'Tennis',
                'icon'        => 'icons/tennis.png',
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'sports_name' => 'Volleyball',
                'icon'        => 'icons/volleyball.png',
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
