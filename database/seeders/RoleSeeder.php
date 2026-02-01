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
            'director',
            'evaluator',
            'referee'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $this->command->info('Roles created successfully!');

        // Optional: Create some basic permissions
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Camp Management
            'view camps',
            'create camps',
            'edit camps',
            'delete camps',

            // Schedule Management
            'view schedules',
            'create schedules',
            'edit schedules',
            'delete schedules',

            // Game Slot Management
            'view game-slots',
            'create game-slots',
            'edit game-slots',
            'delete game-slots',

            // Crew Management
            'view crews',
            'create crews',
            'edit crews',
            'delete crews',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->command->info('Permissions created successfully!');

        // Assign permissions to roles
        $admin = Role::findByName('admin');
        $admin->givePermissionTo(Permission::all());

        $director = Role::findByName('director');
        $director->givePermissionTo([
            'view camps', 'create camps', 'edit camps',
            'view schedules', 'create schedules', 'edit schedules',
            'view game-slots', 'create game-slots', 'edit game-slots',
            'view crews', 'create crews', 'edit crews',
        ]);

        $evaluator = Role::findByName('evaluator');
        $evaluator->givePermissionTo([
            'view camps', 'view schedules', 'view game-slots',
        ]);

        $referee = Role::findByName('referee');
        $referee->givePermissionTo([
            'view schedules', 'view game-slots',
        ]);

        $this->command->info('Permissions assigned to roles successfully!');
    }
}
