<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local')) {
            return;
        }

        DB::table('profiles')->truncate();

        $profiles = [];

        foreach (User::all() as $user) {

            // Extract role name (admin / expert / client)
            $role = $user->roles->first()?->name ?? 'user';

            $username = $role . $user->id;
            $slug = Str::slug($username);

            $profiles[] = [
                'user_id' => $user->id,
                'first_name' => ucfirst($role),
                'last_name' => 'User',
                'username' => $username,
                'slug' => $slug,
                'tagline' => ucfirst($role) . ' at Marketplace',
                'biography' => "Hello, I am a {$role} on this platform.",
                'address' => 'USA',
                'avatar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('profiles')->insert($profiles);
    }
}
