<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Carbon;
use App\Models\BusinessProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('languages')->insert([
            ['name' => 'en',  'display_name' => 'English'],
            ['name' => 'bn',  'display_name' => 'বাংলা (Bangla)'],
            ['name' => 'hi',  'display_name' => 'Hindi'],
            ['name' => 'ur',  'display_name' => 'Urdu'],
            ['name' => 'ar',  'display_name' => 'Arabic'],
            ['name' => 'fr',  'display_name' => 'French'],
            ['name' => 'de',  'display_name' => 'German'],
            ['name' => 'es',  'display_name' => 'Spanish'],
            ['name' => 'pt',  'display_name' => 'Portuguese'],
            ['name' => 'ru',  'display_name' => 'Russian'],
            ['name' => 'zh',  'display_name' => 'Chinese'],
            ['name' => 'ja',  'display_name' => 'Japanese'],
            ['name' => 'ko',  'display_name' => 'Korean'],
            ['name' => 'it',  'display_name' => 'Italian'],
            ['name' => 'tr',  'display_name' => 'Turkish'],
            ['name' => 'fa',  'display_name' => 'Persian'],
            ['name' => 'th',  'display_name' => 'Thai'],
            ['name' => 'vi',  'display_name' => 'Vietnamese'],
            ['name' => 'id',  'display_name' => 'Indonesian'],
            ['name' => 'ms',  'display_name' => 'Malay'],
        ]);
    }
}