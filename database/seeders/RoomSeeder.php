<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local')) {
            $this->command->warn('RoomSeeder skipped — not in local environment.');
            return;
        }

        $experts = User::role('expert', 'web')->pluck('id');
        $clients = User::role('client', 'web')->pluck('id');

        if ($experts->isEmpty() || $clients->isEmpty()) {
            $this->command->error('No experts or clients found.');
            return;
        }

        $rooms = [];

        // create unique expert↔client pairs
        foreach ($clients as $clientId) {
            $selectedExperts = $experts->shuffle()->take(3); // each client has 3 rooms with different experts
            foreach ($selectedExperts as $expertId) {
                $rooms[] = [
                    'first_user_id' => $clientId,
                    'second_user_id' => $expertId,
                    'has_active_order' => false,
                    'last_message_at' => now()->subDays(rand(1, 30)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('rooms')->insert($rooms);

        $this->command->info(count($rooms) . ' Rooms created successfully.');
    }
}
