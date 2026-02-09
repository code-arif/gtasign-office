<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            // RoleSeeder::class,
            SettingSeeder::class,
            CategoriesSeeder::class,
            TagsSeeder::class,
        ]);

        // optional: show output
        $this->command->info('All data seeded successfully');
    }
}
