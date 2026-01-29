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
                'first_name' => 'client',
                'last_name'  => 'john',
                'email'      => 'client@gmail.com',
                'group'      => 'user',
                'role'       => 'client',
                'password'   => Hash::make('12345678'),
            ],
            [
                'first_name' => 'admin',
                'last_name'  => 'user',
                'email'      => 'admin@gmail.com',
                'group'      => 'admin',   // ✅ admin belongs here
                'role'       => 'admin',
                'password'   => Hash::make('12345678'),
            ],
            [
                'first_name' => 'expert',
                'last_name'  => 'user',
                'email'      => 'expert@gmail.com',
                'group'      => 'user',
                'role'       => 'expert',
                'password'   => Hash::make('12345678'),
            ],
        ]);
    }
}
