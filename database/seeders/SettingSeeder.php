<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB as FacadesDB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->insert([
            'title'         => 'Whistle Work',
            'phone'         => '123456789',
            'email'         => 'admin@admin.com',
            'name'          => 'Admin',
            'copyright'     => 'Copyright © 2026 Whistle Work. All rights reserved.',
            'description'   => "Whistle Work is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience",
            'address'       => 'New York, USA',
            'keywords'      => 'Laravel, Framework, PHP',
            'author'        => 'Drew Bontrager',
            'created_at'    => FacadesDB::raw('CURRENT_TIMESTAMP'),
            'updated_at'    => FacadesDB::raw('CURRENT_TIMESTAMP'),
        ]);
    }
}
