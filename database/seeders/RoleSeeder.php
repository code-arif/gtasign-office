<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles for BOTH guards
        $roles = [
            // Web guard — admin panel use করে
            ['name' => 'admin',  'guard_name' => 'web'],
            ['name' => 'expert', 'guard_name' => 'web'],
            ['name' => 'client', 'guard_name' => 'web'],

            // API guard — mobile/API use করে
            ['name' => 'expert', 'guard_name' => 'api'],
            ['name' => 'client', 'guard_name' => 'api'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name'], 'guard_name' => $role['guard_name']]
            );
        }

        $this->command->info('✅ Roles created for web & api guards.');

        // Permissions (web guard only — admin panel এ লাগবে)
        $permissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view gigs',
            'edit gigs',
            'delete gigs',
            'view orders',
            'manage orders',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->command->info('✅ Permissions created.');

        // Admin কে সব permission দাও
        $admin = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        $admin->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->command->info('✅ All permissions assigned to admin role.');
    }
}
