<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->insert([
            'title'         => 'BMW',
            'phone'         => '123456789',
            'email'         => 'bmw@example.com',
            'name'          => 'BWM - PARTS AND ACCESSORIES',
            'copyright'     => 'Copyright © 2025 BMW. All rights reserved.',
            'description'   => "BMW parts and accessories are designed to enhance the performance, style, and comfort of your vehicle.
                                Genuine BMW parts ensure perfect compatibility, maintaining the car's reliability and safety.",
            'address'       => 'Cairo, Egypt',
            'keywords'      => 'BMW, PARTS, ACCESSORIES',
            'author'        => 'BMW',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }
}
