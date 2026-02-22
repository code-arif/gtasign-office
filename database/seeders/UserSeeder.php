<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local')) {
            $this->command->warn('UserSeeder skipped — not in local environment.');
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Reset Spatie permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /* =========================================
         * ROLES
         * -----------------------------------------
         * web  → admin panel (backend controllers)
         * api  → mobile / REST API
         * ========================================= */
        DB::table('roles')->insert([
            // web guard
            ['id' => 1, 'name' => 'admin',  'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'expert', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'client', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],

            // api guard
            ['id' => 4, 'name' => 'expert', 'guard_name' => 'api', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'client', 'guard_name' => 'api', 'created_at' => now(), 'updated_at' => now()],
        ]);

        /*
         * Role ID reference:
         *   1 → admin  (web)
         *   2 → expert (web)   ← admin panel query
         *   3 → client (web)   ← admin panel query
         *   4 → expert (api)   ← API authentication
         *   5 → client (api)   ← API authentication
         */

        $users      = [];
        $modelRoles = [];
        $id         = 1;
        $password   = Hash::make('12345678');

        /* =========================================
         * ADMINS — just web guard role (role_id: 1)
         * ========================================= */
        for ($i = 1; $i <= 2; $i++) {
            $users[] = [
                'id'                => $id,
                'email'             => "admin{$i}@gmail.com",
                'phone'             => "100000000{$i}",
                'password'          => $password,
                'status'            => 'active',
                'email_verified_at' => now(),
                'remember_token'    => Str::random(10),
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            // Admin will just web guard containe
            $modelRoles[] = [
                'role_id'    => 1, // admin (web)
                'model_id'   => $id,
                'model_type' => 'App\Models\User',
            ];

            $id++;
        }

        /* =========================================
         * EXPERTS — web + api both role
         * ========================================= */
        for ($i = 1; $i <= 5; $i++) {
            $users[] = [
                'id'                => $id,
                'email'             => "expert{$i}@example.com",
                'phone'             => "200000000{$i}",
                'password'          => $password,
                'status'            => 'active',
                'email_verified_at' => now(),
                'remember_token'    => Str::random(10),
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            // web guard — admin panel will get User::role('expert', 'web')
            $modelRoles[] = [
                'role_id'    => 2, // expert (web)
                'model_id'   => $id,
                'model_type' => 'App\Models\User',
            ];

            // api guard — API auth middleware তে hasRole('expert') কাজ করবে
            $modelRoles[] = [
                'role_id'    => 4, // expert (api)
                'model_id'   => $id,
                'model_type' => 'App\Models\User',
            ];

            $id++;
        }

        /* =========================================
         * CLIENTS — web + api two role
         * ========================================= */
        for ($i = 1; $i <= 5; $i++) {
            $users[] = [
                'id'                => $id,
                'email'             => "client{$i}@example.com",
                'phone'             => "300000000{$i}",
                'password'          => $password,
                'status'            => 'active',
                'email_verified_at' => now(),
                'remember_token'    => Str::random(10),
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            // web guard
            $modelRoles[] = [
                'role_id'    => 3, // client (web)
                'model_id'   => $id,
                'model_type' => 'App\Models\User',
            ];

            // api guard
            $modelRoles[] = [
                'role_id'    => 5, // client (api)
                'model_id'   => $id,
                'model_type' => 'App\Models\User',
            ];

            $id++;
        }

        /* =========================================
         * INSERT
         * ========================================= */
        DB::table('users')->insert($users);
        DB::table('model_has_roles')->insert($modelRoles);

        $this->command->info('Users seeded: 2 admins, 5 experts, 5 clients.');
        $this->command->info('Each expert & client has both web + api guard roles.');
    }
}
