<?php

namespace Database\Seeders;

<<<<<<< HEAD
=======
use App\Models\User;
use Doctrine\Inflector\Language;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
>>>>>>> 7ad60917571d7a5f37e452e0a0d43bef9b511182
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
            SettingSeeder::class,
            SportsTypeSeeder::class,
            CampTableSeeder::class,
            CampPaymentAndCheckinSeeder::class,
            CampEvaluatorRegistrationSeeder::class
        ]);

<<<<<<< HEAD
        // optional: show output
        $this->command->info('All data seeded successfully');
=======
        $this->call(UserSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(FaqSeeder::class);
        $this->call(SocialMediaSeeder::class);
        $this->call(DynamicPageSeeder::class);
        $this->call(LanguageSeeder::class);

        // $this->call(PlanSeeder::class);

>>>>>>> 7ad60917571d7a5f37e452e0a0d43bef9b511182
    }
}
