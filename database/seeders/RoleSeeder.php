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
            ['name' => 'admin',  'guard' => 'web'],
            ['name' => 'expert', 'guard' => 'web'],   // ← add this for admin panel
            ['name' => 'client', 'guard' => 'web'],   // ← add this
            ['name' => 'expert', 'guard' => 'api'],   // keep original for API
            ['name' => 'client', 'guard' => 'api'],   // keep original
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(
                ['name' => $r['name'], 'guard_name' => $r['guard']],
                ['name' => $r['name'], 'guard_name' => $r['guard']]
            );
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
