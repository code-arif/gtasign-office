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
            RoleSeeder::class,
            ProfileSeeder::class,
            SettingSeeder::class,
            CategoriesSeeder::class,
            TagsSeeder::class,
            LanguageSeeder::class,
            GigSeeder::class,
            GigImageSeeder::class,
            GigTagSeeder::class,
        ]);

        // optional: show output
        $this->command->info('All data seeded successfully');
    }
}
