<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Carbon;
use App\Models\BusinessProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'first_name' => 'John',
                'last_name' => 'John',

                'email' => 'user@gmail.com',

                'role' => 'seller',
                'password' => Hash::make('12345678'),

            ],
            [
                'first_name' => 'John1',
                'last_name' => 'John2',

                'email' => 'admin@gmail.com',

                'role' => 'admin',
                'password' => Hash::make('12345678'),

            ],
            [
                'first_name' => 'John2',
                'last_name' => 'John2',
                'email' => 'business@gmail.com',

                'role' => 'user',
                'password' => Hash::make('12345678'),

            ],

        ]);


    }

}
