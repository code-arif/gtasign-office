<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $roles = [
            'admin',
            'expert',
            'client',
        ];

        foreach ($roles as $role) {
            $guard = match ($role) {
                'admin' => 'web',
                'expert', 'client' => 'api',
                default => 'api'
            };

            Role::firstOrCreate(['name' => $role, 'guard_name' => $guard]);
        }

        $this->command->info('Roles created successfully!');

        // Optional: Create some basic permissions
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->command->info('Permissions created successfully!');

        // Assign permissions to roles
        $admin = Role::findByName('admin');
        $admin->givePermissionTo(Permission::all());


        $this->command->info('Permissions assigned to roles successfully!');
    }
}
