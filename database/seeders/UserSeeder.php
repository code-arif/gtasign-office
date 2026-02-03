<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local')) {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('model_has_roles')->truncate();
        DB::table('roles')->truncate();
        DB::table('users')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /* =========================
         * Roles
         * ========================= */
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'admin',  'guard_name' => 'web'],
            ['id' => 2, 'name' => 'expert', 'guard_name' => 'api'],
            ['id' => 3, 'name' => 'client', 'guard_name' => 'api'],
        ]);

        $users = [];
        $modelRoles = [];
        $id = 1;

        $password = Hash::make('12345678');

        /* =========================
         * Admins (2)
         * ========================= */
        for ($i = 1; $i <= 2; $i++) {
            $users[] = [
                'id' => $id,
                'email' => "admin{$i}@gmail.com",
                'phone' => "100000000{$i}",
                'password' => $password,
                'status' => 'active',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $modelRoles[] = [
                'role_id' => 1,
                'model_id' => $id,
                'model_type' => 'App\Models\User',
            ];

            $id++;
        }

        /* =========================
         * Experts (10)
         * ========================= */
        for ($i = 1; $i <= 10; $i++) {
            $users[] = [
                'id' => $id,
                'email' => "expert{$i}@example.com",
                'phone' => "200000000{$i}",
                'password' => $password,
                'status' => 'active',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $modelRoles[] = [
                'role_id' => 2,
                'model_id' => $id,
                'model_type' => 'App\Models\User',
            ];

            $id++;
        }

        /* =========================
         * Clients (10)
         * ========================= */
        for ($i = 1; $i <= 10; $i++) {
            $users[] = [
                'id' => $id,
                'email' => "client{$i}@example.com",
                'phone' => "300000000{$i}",
                'password' => $password,
                'status' => 'active',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $modelRoles[] = [
                'role_id' => 3,
                'model_id' => $id,
                'model_type' => 'App\Models\User',
            ];

            $id++;
        }

        /* =========================
         * Insert
         * ========================= */
        DB::table('users')->insert($users);
        DB::table('model_has_roles')->insert($modelRoles);
    }
}
